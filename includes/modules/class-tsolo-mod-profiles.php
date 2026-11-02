<?php
/**
 * Frontend profiles module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Profiles
 */
class TSOLO_Mod_Profiles {

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
		// After parse_request so tsolo_is_action( 'profile' ) works with pretty permalinks.
		add_action( 'template_redirect', array( $this, 'handle_profile_save' ), 1 );
		$s = self::get_settings();
		if ( ! empty( $s['block_admin'] ) ) {
			add_action( 'admin_init', array( $this, 'block_admin' ) );
			add_filter( 'show_admin_bar', array( $this, 'maybe_hide_admin_bar' ) );
		}
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'block_admin' => 0,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_PROFILES, self::defaults() ) );
	}

	/**
	 * Sanitize settings before storage / runtime.
	 *
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array(
			'block_admin' => empty( $raw['block_admin'] ) ? 0 : 1,
		);
	}

	/**
	 * Whether a user may keep wp-admin when block_admin is on.
	 *
	 * Editors+ (edit_posts) and anyone with manage_options stay.
	 *
	 * @param int|null $user_id User ID or null for current.
	 * @return bool True = may access admin.
	 */
	public static function user_can_access_admin( $user_id = null ) {
		if ( null === $user_id ) {
			return current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
		}
		$user_id = absint( $user_id );
		return user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'manage_options' );
	}

	/**
	 * Build userdata array from profile POST (no side effects).
	 *
	 * @param int   $user_id User ID.
	 * @param array $post    Map of field => value (already sanitized text).
	 * @return array{userdata:array,error:WP_Error|null}
	 */
	public static function prepare_userdata( $user_id, $post ) {
		$user_id = absint( $user_id );
		$post    = is_array( $post ) ? $post : array();
		$display = isset( $post['display_name'] ) ? sanitize_text_field( (string) $post['display_name'] ) : '';
		$email   = isset( $post['user_email'] ) ? sanitize_email( (string) $post['user_email'] ) : '';
		$pass1   = isset( $post['pass1'] ) ? (string) $post['pass1'] : '';
		$pass2   = isset( $post['pass2'] ) ? (string) $post['pass2'] : '';

		if ( '' === $email || ! is_email( $email ) ) {
			return array(
				'userdata' => array(),
				'error'    => new WP_Error( 'email', __( '<strong>Error:</strong> Please enter a valid email address.', 'tso-login-organizer' ) ),
			);
		}

		$userdata = array(
			'ID'           => $user_id,
			'display_name' => $display,
			'user_email'   => $email,
		);

		if ( '' !== $pass1 || '' !== $pass2 ) {
			if ( $pass1 !== $pass2 ) {
				return array(
					'userdata' => array(),
					'error'    => new WP_Error( 'pass', __( '<strong>Error:</strong> The passwords do not match.', 'tso-login-organizer' ) ),
				);
			}
			$min = 0;
			if ( class_exists( 'TSOLO_Mod_Security' ) && function_exists( 'tsolo_is_module_enabled' ) && tsolo_is_module_enabled( 'security' ) ) {
				$sec = TSOLO_Mod_Security::get_settings();
				$min = absint( $sec['password_min_length'] );
			}
			if ( $min > 0 && class_exists( 'TSOLO_Mod_Security' ) && ! TSOLO_Mod_Security::password_meets_min_length( $pass1, $min ) ) {
				return array(
					'userdata' => array(),
					'error'    => new WP_Error(
						'tsolo_password_short',
						sprintf(
							/* translators: %d: minimum length */
							__( '<strong>Error:</strong> Password must be at least %d characters.', 'tso-login-organizer' ),
							$min
						)
					),
				);
			}
			$userdata['user_pass'] = $pass1;
		}

		return array(
			'userdata' => $userdata,
			'error'    => null,
		);
	}

	/**
	 * Redirect target when blocking wp-admin.
	 *
	 * @return string
	 */
	public static function blocked_admin_redirect_url() {
		$url = function_exists( 'tsolo_get_action_url' ) ? tsolo_get_action_url( 'profile' ) : '';
		return $url ? $url : home_url( '/' );
	}

	/**
	 * Block wp-admin for subscribers.
	 */
	public function block_admin() {
		global $pagenow;
		if ( wp_doing_ajax() || 'admin-post.php' === $pagenow || self::user_can_access_admin() ) {
			return;
		}
		wp_safe_redirect( self::blocked_admin_redirect_url() );
		exit;
	}

	/**
	 * Hide admin bar for blocked roles.
	 *
	 * @param bool $show Show.
	 * @return bool
	 */
	public function maybe_hide_admin_bar( $show ) {
		if ( self::user_can_access_admin() ) {
			return $show;
		}
		return false;
	}

	/**
	 * Whether the posted profile changes email, password or switches two-factor off.
	 *
	 * These changes need the current password so a stolen session cannot take over the account.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function sensitive_change_requested( $user_id ) {
		$user = get_userdata( absint( $user_id ) );
		if ( ! ( $user instanceof WP_User ) ) {
			return false;
		}
		$new_email = sanitize_email( tsolo_get_request_text( 'user_email', 'post' ) );
		if ( '' !== $new_email && strtolower( $new_email ) !== strtolower( (string) $user->user_email ) ) {
			return true;
		}
		if ( '' !== tsolo_get_request_password( 'pass1' ) || '' !== tsolo_get_request_password( 'pass2' ) ) {
			return true;
		}
		if ( tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) && TSOLO_Mod_Two_Factor::user_needs_2fa( $user ) && ! tsolo_request_post_is_set( 'tsolo_2fa_enable' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Whether the posted current password matches the account.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function current_password_is_valid( $user_id ) {
		$user    = get_userdata( absint( $user_id ) );
		$current = tsolo_get_request_password( 'current_pass' );
		if ( ! ( $user instanceof WP_User ) || '' === $current ) {
			return false;
		}
		return (bool) wp_check_password( $current, $user->user_pass, $user->ID );
	}

	/**
	 * Save profile POST.
	 */
	public function handle_profile_save() {
		if ( ! tsolo_is_action( 'profile' ) || ! is_user_logged_in() ) {
			return;
		}
		if ( ! tsolo_request_post_is_set( 'tsolo_profile_nonce' ) ) {
			return;
		}
		$nonce = tsolo_get_request_text( 'tsolo_profile_nonce', 'post' );
		if ( ! wp_verify_nonce( $nonce, 'tsolo_save_profile' ) ) {
			return;
		}
		$user_id = get_current_user_id();
		if ( self::sensitive_change_requested( $user_id ) && ! self::current_password_is_valid( $user_id ) ) {
			tsolo_set_errors( new WP_Error( 'tsolo_current_pass', __( '<strong>Error:</strong> Enter your current password to change your email or password, or to turn two-factor off.', 'tso-login-organizer' ) ) );
			return;
		}
		$prepared = self::prepare_userdata(
			$user_id,
			array(
				'display_name' => tsolo_get_request_text( 'display_name', 'post' ),
				'user_email'   => tsolo_get_request_text( 'user_email', 'post' ),
				'pass1'        => tsolo_get_request_password( 'pass1' ),
				'pass2'        => tsolo_get_request_password( 'pass2' ),
			)
		);
		if ( $prepared['error'] instanceof WP_Error ) {
			tsolo_set_errors( $prepared['error'] );
			return;
		}
		/**
		 * Validate extra profile fields before writing userdata.
		 *
		 * @param WP_Error|null $errors  Errors or null.
		 * @param int           $user_id User ID.
		 */
		$extra_err = apply_filters( 'tsolo_profile_validate', null, $user_id );
		if ( $extra_err instanceof WP_Error && $extra_err->has_errors() ) {
			tsolo_set_errors( $extra_err );
			return;
		}
		$result = wp_update_user( $prepared['userdata'] );
		if ( is_wp_error( $result ) ) {
			tsolo_set_errors( $result );
			return;
		}
		/**
		 * After core profile userdata is saved (Custom Fields, etc.).
		 *
		 * @param int $user_id User ID.
		 */
		do_action( 'tsolo_profile_save', $user_id );
		if ( tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
			TSOLO_Mod_Two_Factor::save_front_profile( $user_id );
		}
		tsolo_set_errors( new WP_Error( 'updated', __( 'Profile updated.', 'tso-login-organizer' ), 'message' ) );
	}
}
