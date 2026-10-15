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
	 * Whether the next wp_mail should use HTML (scoped; not global for all mail).
	 *
	 * @var bool
	 */
	private static $send_html = false;

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
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_NOTIFICATIONS, self::defaults() ) );
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
			'html'             => empty( $raw['html'] ) ? 0 : 1,
			'new_user_subject' => isset( $raw['new_user_subject'] ) ? sanitize_text_field( (string) $raw['new_user_subject'] ) : '',
			'new_user_body'    => isset( $raw['new_user_body'] ) ? sanitize_textarea_field( (string) $raw['new_user_body'] ) : '',
			'reset_body'       => isset( $raw['reset_body'] ) ? sanitize_textarea_field( (string) $raw['reset_body'] ) : '',
		);
	}

	/**
	 * Replace placeholders in a template.
	 *
	 * @param string               $template Template.
	 * @param array<string,string> $map      Placeholders without braces => values.
	 * @return string
	 */
	public static function apply_placeholders( $template, $map ) {
		$template = (string) $template;
		if ( '' === $template || ! is_array( $map ) ) {
			return $template;
		}
		$search  = array();
		$replace = array();
		foreach ( $map as $key => $value ) {
			$search[]  = '{' . $key . '}';
			$replace[] = (string) $value;
		}
		return str_replace( $search, $replace, $template );
	}

	/**
	 * Build password-reset URL for emails (frontend when available).
	 *
	 * @param string $key        Reset key.
	 * @param string $user_login Login.
	 * @return string
	 */
	public static function build_reset_url( $key, $user_login ) {
		$key        = (string) $key;
		$user_login = (string) $user_login;
		if ( function_exists( 'tsolo_get_action_url' ) && function_exists( 'tsolo_action_exists' ) && tsolo_action_exists( 'resetpass' ) ) {
			return add_query_arg(
				array(
					'key'   => $key,
					'login' => $user_login,
				),
				tsolo_get_action_url( 'resetpass' )
			);
		}
		return network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user_login ), 'login' );
	}

	/**
	 * HTML content type only while sending our customized messages.
	 *
	 * @param string $type Type.
	 * @return string
	 */
	public function maybe_html( $type ) {
		if ( self::$send_html ) {
			return 'text/html';
		}
		return $type;
	}

	/**
	 * Arm HTML content-type for the current outgoing mail only.
	 */
	private static function arm_html() {
		self::$send_html = true;
		add_action( 'wp_mail_succeeded', array( __CLASS__, 'disarm_html' ), 999 );
		add_action( 'wp_mail_failed', array( __CLASS__, 'disarm_html' ), 999 );
		add_action( 'shutdown', array( __CLASS__, 'disarm_html' ), 0 );
	}

	/**
	 * Clear HTML arming after mail attempt.
	 */
	public static function disarm_html() {
		self::$send_html = false;
		remove_action( 'wp_mail_succeeded', array( __CLASS__, 'disarm_html' ), 999 );
		remove_action( 'wp_mail_failed', array( __CLASS__, 'disarm_html' ), 999 );
		remove_action( 'shutdown', array( __CLASS__, 'disarm_html' ), 0 );
	}

	/**
	 * Format body for HTML emails (escaped text + line breaks).
	 *
	 * @param string $body Body.
	 * @return string
	 */
	public static function format_html_body( $body ) {
		return nl2br( esc_html( (string) $body ), false );
	}

	/**
	 * Filter new user email.
	 *
	 * @param array   $email    Email.
	 * @param WP_User $user     User.
	 * @param string  $blogname Blog name.
	 * @return array
	 */
	public function filter_new_user_email( $email, $user, $blogname ) {
		$s = self::get_settings();
		if ( empty( $s['new_user_subject'] ) && empty( $s['new_user_body'] ) ) {
			return $email;
		}
		$map = array(
			'site_name'  => $blogname,
			'user_login' => $user->user_login,
			'user_email' => $user->user_email,
			'login_url'  => wp_login_url(),
		);
		if ( ! empty( $s['new_user_subject'] ) ) {
			$email['subject'] = self::apply_placeholders( $s['new_user_subject'], $map );
		}
		if ( ! empty( $s['new_user_body'] ) ) {
			$body = self::apply_placeholders( $s['new_user_body'], $map );
			if ( ! empty( $s['html'] ) ) {
				$body = self::format_html_body( $body );
				self::arm_html();
			}
			$email['message'] = $body;
		}
		return $email;
	}

	/**
	 * Filter reset message.
	 *
	 * @param string  $message    Message.
	 * @param string  $key        Key.
	 * @param string  $user_login Login.
	 * @param WP_User $user_data  User.
	 * @return string
	 */
	public function filter_reset_message( $message, $key, $user_login, $user_data ) {
		unset( $user_data );
		$s = self::get_settings();
		if ( empty( $s['reset_body'] ) ) {
			return $message;
		}
		$url  = self::build_reset_url( $key, $user_login );
		$body = self::apply_placeholders(
			$s['reset_body'],
			array(
				'user_login' => $user_login,
				'reset_url'  => $url,
				'site_name'  => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
			)
		);
		if ( ! empty( $s['html'] ) ) {
			self::arm_html();
			return self::format_html_body( $body );
		}
		return $body;
	}
}
