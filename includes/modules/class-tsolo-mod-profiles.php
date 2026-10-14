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
		return tsolo_get_option_array( TSOLO_OPT_PROFILES, self::defaults() );
	}

	/**
	 * Block wp-admin for subscribers.
	 */
	public function block_admin() {
		if ( wp_doing_ajax() || current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_safe_redirect( tsolo_get_action_url( 'profile' ) ? tsolo_get_action_url( 'profile' ) : home_url( '/' ) );
		exit;
	}

	/**
	 * Hide admin bar for blocked roles.
	 *
	 * @param bool $show Show.
	 * @return bool
	 */
	public function maybe_hide_admin_bar( $show ) {
		if ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' ) ) {
			return $show;
		}
		return false;
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
		$user_id  = get_current_user_id();
		$userdata = array(
			'ID'           => $user_id,
			'display_name' => tsolo_get_request_text( 'display_name', 'post' ),
			'user_email'   => tsolo_get_request_text( 'user_email', 'post' ),
		);
		$pass1    = tsolo_get_request_text( 'pass1', 'post' );
		$pass2    = tsolo_get_request_text( 'pass2', 'post' );
		if ( $pass1 || $pass2 ) {
			if ( $pass1 !== $pass2 ) {
				tsolo_set_errors( new WP_Error( 'pass', __( '<strong>Error:</strong> The passwords do not match.', 'tso-login-organizer' ) ) );
				return;
			}
			$userdata['user_pass'] = $pass1;
		}
		$result = wp_update_user( $userdata );
		if ( is_wp_error( $result ) ) {
			tsolo_set_errors( $result );
			return;
		}
		if ( tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
			TSOLO_Mod_Two_Factor::save_front_profile( $user_id );
		}
		tsolo_set_errors( new WP_Error( 'updated', __( 'Profile updated.', 'tso-login-organizer' ), 'message' ) );
	}
}
