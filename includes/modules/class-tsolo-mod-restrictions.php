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
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_shortcode( 'tsolo-logged-in', array( $this, 'shortcode_logged_in' ) );
		add_shortcode( 'tsolo-logged-out', array( $this, 'shortcode_logged_out' ) );
		add_shortcode( 'tsolo-role', array( $this, 'shortcode_role' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'private_site'   => 0,
			'restrict_roles' => array(),
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_RESTRICTIONS, self::defaults() );
	}

	/**
	 * Force login for whole site.
	 */
	public function maybe_private_site() {
		$s = self::get_settings();
		if ( empty( $s['private_site'] ) || is_user_logged_in() ) {
			return;
		}
		if ( tsolo_is_action() ) {
			return;
		}
		wp_safe_redirect( wp_login_url( home_url( add_query_arg( array() ) ) ) );
		exit;
	}

	/**
	 * Restrict singular content by role meta.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( ! is_singular() || ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {
			return $content;
		}
		$roles = get_post_meta( get_the_ID(), '_tsolo_restrict_roles', true );
		if ( empty( $roles ) || ! is_array( $roles ) ) {
			return $content;
		}
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view this content.', 'tso-login-organizer' ) . '</p>' . tsolo_render_form( 'login', array( 'show_links' => true ) );
		}
		$user = wp_get_current_user();
		foreach ( (array) $user->roles as $role ) {
			if ( in_array( $role, $roles, true ) ) {
				return $content;
			}
		}
		return '<p>' . esc_html__( 'You do not have permission to view this content.', 'tso-login-organizer' ) . '</p>';
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
		$need = array_filter( array_map( 'sanitize_key', explode( ',', (string) $atts['role'] ) ) );
		if ( empty( $need ) || ! is_user_logged_in() ) {
			return '';
		}
		$user = wp_get_current_user();
		foreach ( (array) $user->roles as $role ) {
			if ( in_array( $role, $need, true ) ) {
				return do_shortcode( $content );
			}
		}
		return '';
	}
}
