<?php
/**
 * Google reCAPTCHA module (opt-in; collides with CleanTalk / Kadence).
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Captcha
 */
class TSOLO_Mod_Captcha {

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
		$s = self::get_settings();
		if ( empty( $s['site_key'] ) || empty( $s['secret_key'] ) ) {
			return;
		}
		add_action( 'login_form', array( $this, 'render_widget' ) );
		add_action( 'register_form', array( $this, 'render_widget' ) );
		add_action( 'lostpassword_form', array( $this, 'render_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'registration_errors', array( $this, 'verify' ) );
		add_filter( 'authenticate', array( $this, 'verify_login' ), 30, 3 );
		add_action( 'lostpassword_post', array( $this, 'verify_lostpassword' ), 10, 1 );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'site_key'   => '',
			'secret_key' => '',
			'version'    => 'v2',
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_CAPTCHA, self::defaults() );
	}

	/**
	 * Enqueue reCAPTCHA API.
	 */
	public function enqueue() {
		if ( ! tsolo_is_action() && ! tsolo_content_has_shortcode() ) {
			return;
		}
		$s = self::get_settings();
		wp_enqueue_script(
			'tsolo-recaptcha',
			'https://www.google.com/recaptcha/api.js',
			array(),
			TSOLO_VERSION,
			true
		);
		unset( $s );
	}

	/**
	 * Render widget markup.
	 */
	public function render_widget() {
		$s = self::get_settings();
		echo '<div class="g-recaptcha tsolo-recaptcha" data-sitekey="' . esc_attr( $s['site_key'] ) . '"></div>';
	}

	/**
	 * Verify token with Google.
	 *
	 * @return bool|WP_Error
	 */
	private function check_token() {
		$s     = self::get_settings();
		$token = tsolo_get_request_text( 'g-recaptcha-response', 'post' );
		if ( '' === $token ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Please complete the captcha.', 'tso-login-organizer' ) );
		}
		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $s['secret_key'],
					'response' => $token,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Captcha verification failed.', 'tso-login-organizer' ) );
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['success'] ) ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Captcha verification failed.', 'tso-login-organizer' ) );
		}
		return true;
	}

	/**
	 * Only forms that actually show this widget should be checked.
	 *
	 * Skips wp-admin Add User screens, other plugins login forms, and cleanup tools.
	 *
	 * @return bool
	 */
	private function should_verify() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}
		if ( function_exists( 'tsolo_is_wp_login' ) && tsolo_is_wp_login() ) {
			return true;
		}
		if ( ! function_exists( 'tsolo_is_action' ) ) {
			return false;
		}
		return tsolo_is_action( 'login' ) || tsolo_is_action( 'register' ) || tsolo_is_action( 'lostpassword' );
	}

	/**
	 * Verify on registration.
	 *
	 * @param WP_Error $errors Errors.
	 * @return WP_Error
	 */
	public function verify( $errors ) {
		if ( ! $this->should_verify() || ! tsolo_request_post_is_set( 'user_email' ) ) {
			return $errors;
		}
		$result = $this->check_token();
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}
		return $errors;
	}

	/**
	 * Verify on login POST.
	 *
	 * @param mixed  $user User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function verify_login( $user, $username, $password ) {
		unset( $username, $password );
		if ( ! $this->should_verify() || ! tsolo_request_post_is_set( 'log' ) ) {
			return $user;
		}
		$result = $this->check_token();
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return $user;
	}

	/**
	 * Verify lost password.
	 *
	 * @param WP_Error $errors Errors.
	 */
	public function verify_lostpassword( $errors ) {
		if ( ! $this->should_verify() ) {
			return;
		}
		$result = $this->check_token();
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}
	}
}
