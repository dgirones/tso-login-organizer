<?php
/**
 * Registration moderation module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Moderation
 */
class TSOLO_Mod_Moderation {

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
		add_action( 'user_register', array( $this, 'on_register' ), 5 );
		add_filter( 'wp_authenticate_user', array( $this, 'block_unapproved' ), 10, 2 );
		add_action( 'init', array( $this, 'maybe_confirm_email' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_approval' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'email_confirm' => 0,
			'admin_approve' => 0,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_MODERATION, self::defaults() );
	}

	/**
	 * Whether a new account must not be signed in yet.
	 *
	 * Email confirmation and admin approval both override Core auto-login.
	 *
	 * @return bool
	 */
	public static function blocks_auto_login() {
		if ( ! tsolo_is_module_enabled( 'moderation' ) ) {
			return false;
		}
		$settings = self::get_settings();
		return ! empty( $settings['email_confirm'] ) || ! empty( $settings['admin_approve'] );
	}

	/**
	 * On register: set pending flags.
	 *
	 * @param int $user_id User ID.
	 */
	public function on_register( $user_id ) {
		$s = self::get_settings();
		if ( ! empty( $s['admin_approve'] ) ) {
			update_user_meta( $user_id, 'tsolo_moderation_status', 'pending_admin' );
		}
		if ( ! empty( $s['email_confirm'] ) ) {
			$key = wp_generate_password( 20, false );
			update_user_meta( $user_id, 'tsolo_email_confirm_key', $key );
			update_user_meta( $user_id, 'tsolo_moderation_status', ! empty( $s['admin_approve'] ) ? 'pending_admin' : 'pending_email' );
			$user = get_userdata( $user_id );
			if ( $user ) {
				$url = add_query_arg(
					array(
						'tsolo_confirm' => rawurlencode( $key ),
						'user'          => $user_id,
					),
					home_url( '/' )
				);
				wp_mail(
					$user->user_email,
					__( 'Confirm your email', 'tso-login-organizer' ),
					sprintf(
						/* translators: %s: confirmation URL */
						__( "Please confirm your email by visiting:\n\n%s", 'tso-login-organizer' ),
						$url
					)
				);
			}
		}
	}

	/**
	 * Block unapproved users.
	 *
	 * @param WP_User $user User.
	 * @param string  $password Password.
	 * @return WP_User|WP_Error
	 */
	public function block_unapproved( $user, $password ) {
		unset( $password );
		if ( is_wp_error( $user ) || ! $user ) {
			return $user;
		}
		$status = get_user_meta( $user->ID, 'tsolo_moderation_status', true );
		if ( 'pending_email' === $status ) {
			return new WP_Error( 'tsolo_pending_email', __( '<strong>Error:</strong> Please confirm your email before logging in.', 'tso-login-organizer' ) );
		}
		if ( 'pending_admin' === $status ) {
			return new WP_Error( 'tsolo_pending_admin', __( '<strong>Error:</strong> Your account is awaiting admin approval.', 'tso-login-organizer' ) );
		}
		return $user;
	}

	/**
	 * Confirm email from link.
	 */
	public function maybe_confirm_email() {
		$key = tsolo_get_request_text( 'tsolo_confirm', 'get' );
		if ( '' === $key ) {
			return;
		}
		$user_id = absint( tsolo_get_request_text( 'user', 'get' ) );
		$stored  = get_user_meta( $user_id, 'tsolo_email_confirm_key', true );
		if ( $user_id && $stored && hash_equals( (string) $stored, $key ) ) {
			delete_user_meta( $user_id, 'tsolo_email_confirm_key' );
			$s = self::get_settings();
			if ( ! empty( $s['admin_approve'] ) ) {
				update_user_meta( $user_id, 'tsolo_moderation_status', 'pending_admin' );
			} else {
				delete_user_meta( $user_id, 'tsolo_moderation_status' );
			}
			wp_safe_redirect( add_query_arg( 'checkemail', 'confirm', tsolo_get_action_url( 'login' ) ) );
			exit;
		}
	}

	/**
	 * Approve user from Users list action.
	 */
	public function handle_admin_approval() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$action  = tsolo_get_request_text( 'tsolo_mod_action', 'get' );
		$user_id = absint( tsolo_get_request_text( 'user_id', 'get' ) );
		$nonce   = tsolo_get_request_text( '_wpnonce', 'get' );
		if ( 'approve' !== $action || ! $user_id || ! wp_verify_nonce( $nonce, 'tsolo_approve_' . $user_id ) ) {
			return;
		}
		delete_user_meta( $user_id, 'tsolo_moderation_status' );
		wp_safe_redirect( admin_url( 'users.php?tsolo_approved=1' ) );
		exit;
	}
}
