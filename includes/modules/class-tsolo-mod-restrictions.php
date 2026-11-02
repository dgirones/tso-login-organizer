<?php
/**
 * Content restrictions module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Restrictions
 */
class TSOLO_Mod_Restrictions {

	/**
	 * Post meta key for allowed roles on a singular post/page.
	 *
	 * @var string
	 */
	const META_ROLES = '_tsolo_restrict_roles';

	/**
	 * Instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_private_site' ), 1 );
		add_action( 'template_redirect', array( $this, 'gate_singular' ), 2 );
		add_filter( 'rest_authentication_errors', array( $this, 'private_site_rest' ), 20 );
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_filter( 'get_the_excerpt', array( $this, 'filter_excerpt' ), 20, 2 );
		add_shortcode( 'tsolo-logged-in', array( $this, 'shortcode_logged_in' ) );
		add_shortcode( 'tsolo-logged-out', array( $this, 'shortcode_logged_out' ) );
		add_shortcode( 'tsolo-role', array( $this, 'shortcode_role' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'private_site' => 0,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$s                 = tsolo_get_option_array( TSOLO_OPT_RESTRICTIONS, self::defaults() );
		$s['private_site'] = empty( $s['private_site'] ) ? 0 : 1;
		return $s;
	}

	/**
	 * Sanitize settings blob.
	 *
	 * @param array $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array(
			'private_site' => empty( $raw['private_site'] ) ? 0 : 1,
		);
	}

	/**
	 * Whether the current front request must stay public under private-site mode.
	 *
	 * @return bool
	 */
	public static function is_public_request() {
		if ( is_user_logged_in() ) {
			return true;
		}
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return true;
		}
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return true;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return true;
		}
		if ( function_exists( 'tsolo_is_action' ) && tsolo_is_action() ) {
			return true;
		}
		return self::request_path_is_action_slug();
	}

	/**
	 * Whether the request path is exactly one of the TSO action slugs.
	 *
	 * Fallback when rewrite rules are missing (404): without it a guest sent to
	 * /login/ would be redirected to /login/ again, forever.
	 *
	 * @return bool
	 */
	public static function request_path_is_action_slug() {
		if ( ! function_exists( 'tsolo_get_actions' ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$uri  = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Path compared only.
		$path = (string) wp_parse_url( (string) $uri, PHP_URL_PATH );
		$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = strtolower( trim( $path, '/' ) );
		$base = strtolower( trim( $base, '/' ) );
		if ( '' !== $base && 0 === strpos( $path, $base ) ) {
			$path = trim( substr( $path, strlen( $base ) ), '/' );
		}
		if ( '' === $path ) {
			return false;
		}
		foreach ( tsolo_get_actions() as $action ) {
			if ( $path === $action->get_slug() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build redirect target for the current front URL (for post-login return).
	 *
	 * @return string
	 */
	public static function current_front_url() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via esc_url_raw below.
		if ( '' === $host ) {
			return home_url( '/' );
		}
		$scheme = is_ssl() ? 'https://' : 'http://';
		$url    = esc_url_raw( $scheme . $host . $uri );
		return $url ? $url : home_url( '/' );
	}

	/**
	 * Sanitize a list of role slugs.
	 *
	 * @param mixed $roles Roles.
	 * @return string[]
	 */
	public static function sanitize_roles( $roles ) {
		if ( is_string( $roles ) ) {
			$roles = explode( ',', $roles );
		}
		if ( ! is_array( $roles ) ) {
			return array();
		}
		$out = array();
		foreach ( $roles as $role ) {
			$role = sanitize_key( (string) $role );
			if ( '' !== $role ) {
				$out[] = $role;
			}
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Whether a user may view content restricted to the given roles.
	 *
	 * Admins (manage_options) always may. Guests never may when roles is non-empty.
	 *
	 * @param string[]     $roles Allowed roles.
	 * @param WP_User|null $user  User or null for guest.
	 * @return bool
	 */
	public static function user_can_view_roles( $roles, $user = null ) {
		$roles = self::sanitize_roles( $roles );
		if ( empty( $roles ) ) {
			return true;
		}
		if ( ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
			return false;
		}
		if ( user_can( $user, 'manage_options' ) ) {
			return true;
		}
		foreach ( (array) $user->roles as $role ) {
			if ( in_array( sanitize_key( (string) $role ), $roles, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Force login for whole site.
	 */
	public function maybe_private_site() {
		$s = self::get_settings();
		if ( empty( $s['private_site'] ) || self::is_public_request() ) {
			return;
		}
		wp_safe_redirect( wp_login_url( self::current_front_url() ) );
		exit;
	}

	/**
	 * Private site: do not leak content through the REST API to guests.
	 *
	 * @param WP_Error|null|true $result Existing authentication result.
	 * @return WP_Error|null|true
	 */
	public function private_site_rest( $result ) {
		if ( ! empty( $result ) || is_user_logged_in() ) {
			return $result;
		}
		$s = self::get_settings();
		if ( empty( $s['private_site'] ) ) {
			return $result;
		}
		if ( ! apply_filters( 'tsolo_private_site_block_rest', true ) ) {
			return $result;
		}
		return new WP_Error( 'tsolo_rest_private', __( 'You must be logged in to access this site.', 'tso-login-organizer' ), array( 'status' => 401 ) );
	}

	/**
	 * Role-restricted singular posts: guests go to the login page, other users get a 403.
	 *
	 * This hides the title, comments and metadata too, not only the content.
	 * Return false from the tsolo_restrictions_hard_gate filter to keep the inline login prompt only.
	 */
	public function gate_singular() {
		if ( ! is_singular() || ( function_exists( 'tsolo_is_action' ) && tsolo_is_action() ) || ! apply_filters( 'tsolo_restrictions_hard_gate', true ) ) {
			return;
		}
		$post_id = (int) get_queried_object_id();
		if ( $post_id < 1 ) {
			return;
		}
		$roles = self::sanitize_roles( get_post_meta( $post_id, self::META_ROLES, true ) );
		if ( empty( $roles ) ) {
			return;
		}
		$user = is_user_logged_in() ? wp_get_current_user() : null;
		if ( self::user_can_view_roles( $roles, $user ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( get_permalink( $post_id ) ) );
			exit;
		}
		wp_die( esc_html__( 'You do not have permission to view this content.', 'tso-login-organizer' ), '', array( 'response' => 403 ) );
	}

	/**
	 * Post types that get the restrict-by-role box.
	 *
	 * @return string[]
	 */
	public static function restricted_post_types() {
		$types = get_post_types( array( 'public' => true ), 'names' );
		$types = is_array( $types ) ? array_values( array_diff( $types, array( 'attachment' ) ) ) : array( 'post', 'page' );
		return (array) apply_filters( 'tsolo_restrictions_post_types', $types );
	}

	/**
	 * Restrict singular content by role meta.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function filter_content( $content ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}
		$roles = get_post_meta( $post_id, self::META_ROLES, true );
		$roles = self::sanitize_roles( $roles );
		if ( empty( $roles ) ) {
			return $content;
		}
		$user = is_user_logged_in() ? wp_get_current_user() : null;
		if ( self::user_can_view_roles( $roles, $user ) ) {
			return $content;
		}
		if ( ! is_user_logged_in() ) {
			$form = is_singular() ? tsolo_render_form( 'login', array( 'show_links' => true ) ) : '';
			return '<p>' . esc_html__( 'Please log in to view this content.', 'tso-login-organizer' ) . '</p>' . $form;
		}
		return '<p>' . esc_html__( 'You do not have permission to view this content.', 'tso-login-organizer' ) . '</p>';
	}

	/**
	 * Hide excerpts of role-restricted posts from users who may not read them.
	 *
	 * @param string       $excerpt Excerpt.
	 * @param WP_Post|null $post    Post.
	 * @return string
	 */
	public function filter_excerpt( $excerpt, $post = null ) {
		$post_id = ( $post instanceof WP_Post ) ? $post->ID : get_the_ID();
		if ( ! $post_id ) {
			return $excerpt;
		}
		$roles = self::sanitize_roles( get_post_meta( $post_id, self::META_ROLES, true ) );
		if ( empty( $roles ) ) {
			return $excerpt;
		}
		$user = is_user_logged_in() ? wp_get_current_user() : null;
		return self::user_can_view_roles( $roles, $user ) ? $excerpt : '';
	}

	/**
	 * [tsolo-logged-in] content.
	 *
	 * @param array  $atts    Atts.
	 * @param string $content Content.
	 * @return string
	 */
	public function shortcode_logged_in( $atts, $content = '' ) {
		unset( $atts );
		if ( ! is_user_logged_in() ) {
			return '';
		}
		return do_shortcode( $content );
	}

	/**
	 * [tsolo-logged-out] content.
	 *
	 * @param array  $atts    Atts.
	 * @param string $content Content.
	 * @return string
	 */
	public function shortcode_logged_out( $atts, $content = '' ) {
		unset( $atts );
		if ( is_user_logged_in() ) {
			return '';
		}
		return do_shortcode( $content );
	}

	/**
	 * [tsolo-role role="a,b"] content.
	 *
	 * @param array  $atts    Atts.
	 * @param string $content Content.
	 * @return string
	 */
	public function shortcode_role( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'role' => '',
			),
			$atts,
			'tsolo-role'
		);
		$need = self::sanitize_roles( $atts['role'] );
		if ( empty( $need ) || ! is_user_logged_in() ) {
			return '';
		}
		$user = wp_get_current_user();
		if ( ! self::user_can_view_roles( $need, $user ) ) {
			return '';
		}
		return do_shortcode( $content );
	}

	/**
	 * Meta box on posts and pages.
	 */
	public function register_meta_box() {
		foreach ( self::restricted_post_types() as $type ) {
			add_meta_box(
				'tsolo-restrict-roles',
				__( 'TSO Login Organizer — restrict by role', 'tso-login-organizer' ),
				array( $this, 'render_meta_box' ),
				$type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Meta box UI.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_meta_box( $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}
		wp_nonce_field( 'tsolo_restrict_roles_' . $post->ID, 'tsolo_restrict_roles_nonce' );
		$selected = self::sanitize_roles( get_post_meta( $post->ID, self::META_ROLES, true ) );
		$roles    = wp_roles()->get_names();
		echo '<p class="description">' . esc_html__( 'Leave all unchecked for public content. Checked roles (and administrators) can read the full content; everyone else sees a login prompt or a permission message.', 'tso-login-organizer' ) . '</p>';
		foreach ( $roles as $slug => $label ) {
			echo '<label style="display:block;margin:0.25rem 0;"><input type="checkbox" name="tsolo_restrict_roles[]" value="' . esc_attr( $slug ) . '" ' . checked( in_array( $slug, $selected, true ), true, false ) . ' /> ' . esc_html( $label ) . '</label>';
		}
	}

	/**
	 * Save meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_meta_box( $post_id, $post ) {
		if ( ! ( $post instanceof WP_Post ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$nonce = tsolo_get_request_text( 'tsolo_restrict_roles_nonce', 'post' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'tsolo_restrict_roles_' . $post_id ) ) {
			return;
		}
		$roles = self::sanitize_roles( tsolo_get_request_post_array( 'tsolo_restrict_roles' ) );
		if ( empty( $roles ) ) {
			delete_post_meta( $post_id, self::META_ROLES );
		} else {
			update_post_meta( $post_id, self::META_ROLES, $roles );
		}
	}
}
