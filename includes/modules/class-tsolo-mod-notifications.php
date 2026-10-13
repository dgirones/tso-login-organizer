<?php
/**
 * Custom notification emails module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Notifications
 */
class TSOLO_Mod_Notifications {

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
		add_filter( 'wp_new_user_notification_email', array( $this, 'filter_new_user_email' ), 10, 3 );
		add_filter( 'retrieve_password_message', array( $this, 'filter_reset_message' ), 10, 4 );
		add_filter( 'wp_mail_content_type', array( $this, 'maybe_html' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'html'             => 0,
			'new_user_subject' => '',
			'new_user_body'    => '',
			'reset_body'       => '',
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_NOTIFICATIONS, self::defaults() );
	}

	/**
	 * HTML content type when enabled.
	 *
	 * @param string $type Type.
	 * @return string
	 */
	public function maybe_html( $type ) {
		$s = self::get_settings();
		if ( ! empty( $s['html'] ) ) {
			return 'text/html';
		}
		return $type;
	}

	/**
	 * Filter new user email.
	 *
	 * @param array   $email Email.
	 * @param WP_User $user  User.
	 * @param string  $blogname Blog name.
	 * @return array
	 */
	public function filter_new_user_email( $email, $user, $blogname ) {
		$s = self::get_settings();
		if ( ! empty( $s['new_user_subject'] ) ) {
			$email['subject'] = str_replace(
				array( '{site_name}', '{user_login}' ),
				array( $blogname, $user->user_login ),
				$s['new_user_subject']
			);
		}
		if ( ! empty( $s['new_user_body'] ) ) {
			$email['message'] = str_replace(
				array( '{site_name}', '{user_login}', '{user_email}', '{login_url}' ),
				array( $blogname, $user->user_login, $user->user_email, wp_login_url() ),
				$s['new_user_body']
			);
		}
		return $email;
	}

	/**
	 * Filter reset message.
	 *
	 * @param string  $message Message.
	 * @param string  $key     Key.
	 * @param string  $user_login Login.
	 * @param WP_User $user_data User.
	 * @return string
	 */
	public function filter_reset_message( $message, $key, $user_login, $user_data ) {
		unset( $user_data );
		$s = self::get_settings();
		if ( empty( $s['reset_body'] ) ) {
			return $message;
		}
		$url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );
		if ( function_exists( 'tsolo_get_action_url' ) && tsolo_action_exists( 'resetpass' ) ) {
			$url = add_query_arg(
				array(
					'key'   => $key,
					'login' => rawurlencode( $user_login ),
				),
				tsolo_get_action_url( 'resetpass' )
			);
		}
		return str_replace(
			array( '{user_login}', '{reset_url}', '{site_name}' ),
			array( $user_login, $url, wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ),
			$s['reset_body']
		);
	}
}
