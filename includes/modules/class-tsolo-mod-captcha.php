<?php
/**
 * Captcha module: Google reCAPTCHA v2/v3 or Cloudflare Turnstile (opt-in).
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
		if ( ! self::is_configured() ) {
			return;
		}
		add_action( 'login_form', array( $this, 'render_widget' ) );
		add_action( 'register_form', array( $this, 'render_widget' ) );
		add_action( 'lostpassword_form', array( $this, 'render_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login' ) );
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
			'provider'   => 'v2',
			'site_key'   => '',
			'secret_key' => '',
			'score'      => 0.5,
		);
	}

	/**
	 * Allowed providers.
	 *
	 * @return string[]
	 */
	public static function allowed_providers() {
		return array( 'v2', 'v3', 'turnstile' );
	}

	/**
	 * Settings (sanitized for runtime use).
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_CAPTCHA, self::defaults() ) );
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
		$site     = isset( $raw['site_key'] ) ? sanitize_text_field( (string) $raw['site_key'] ) : '';
		$secret   = isset( $raw['secret_key'] ) ? sanitize_text_field( (string) $raw['secret_key'] ) : '';
		$provider = isset( $raw['provider'] ) ? sanitize_key( (string) $raw['provider'] ) : 'v2';
		// Legacy "version" key from older installs.
		if ( ! isset( $raw['provider'] ) && isset( $raw['version'] ) ) {
			$provider = sanitize_key( (string) $raw['version'] );
		}
		if ( ! in_array( $provider, self::allowed_providers(), true ) ) {
			$provider = 'v2';
		}
		$score = isset( $raw['score'] ) ? (float) $raw['score'] : 0.5;
		if ( $score < 0 ) {
			$score = 0;
		}
		if ( $score > 1 ) {
			$score = 1;
		}
		return array(
			'provider'   => $provider,
			'site_key'   => $site,
			'secret_key' => $secret,
			'score'      => round( $score, 2 ),
			// Keep version alias for older tests / readers.
			'version'    => $provider,
		);
	}

	/**
	 * Whether site + secret keys are present (hooks only then).
	 *
	 * @param array|null $settings Optional settings.
	 * @return bool
	 */
	public static function is_configured( $settings = null ) {
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		return ( '' !== $s['site_key'] && '' !== $s['secret_key'] );
	}

	/**
	 * Front script URL for the chosen provider.
	 *
	 * @param array|null $settings Settings.
	 * @return string
	 */
	public static function api_script_url( $settings = null ) {
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		if ( 'turnstile' === $s['provider'] ) {
			$url = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
			/**
			 * Filter the Turnstile api.js URL.
			 *
			 * @param string $url URL.
			 */
			return (string) apply_filters( 'tsolo_captcha_turnstile_script_url', $url );
		}
		$url = 'https://www.google.com/recaptcha/api.js';
		if ( 'v3' === $s['provider'] ) {
			$url = add_query_arg( 'render', $s['site_key'], $url );
		} else {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = determine_locale();
			} elseif ( function_exists( 'get_locale' ) ) {
				$locale = get_locale();
			} else {
				$locale = 'en_US';
			}
			$hl = strtolower( str_replace( '_', '-', (string) $locale ) );
			if ( $hl ) {
				$url = add_query_arg( 'hl', $hl, $url );
			}
		}
		/**
		 * Filter the reCAPTCHA api.js URL.
		 *
		 * @param string $url URL.
		 */
		return (string) apply_filters( 'tsolo_captcha_api_script_url', $url );
	}

	/**
	 * Siteverify endpoint for the provider.
	 *
	 * @param array|null $settings Settings.
	 * @return string
	 */
	public static function verify_endpoint( $settings = null ) {
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		if ( 'turnstile' === $s['provider'] ) {
			/**
			 * Filter the Turnstile siteverify URL.
			 *
			 * @param string $url URL.
			 */
			return (string) apply_filters( 'tsolo_captcha_turnstile_verify_endpoint', 'https://challenges.cloudflare.com/turnstile/v0/siteverify' );
		}
		/**
		 * Filter the reCAPTCHA siteverify URL.
		 *
		 * @param string $url URL.
		 */
		return (string) apply_filters( 'tsolo_captcha_verify_endpoint', 'https://www.google.com/recaptcha/api/siteverify' );
	}

	/**
	 * Interpret siteverify JSON body.
	 *
	 * @param mixed      $body     Decoded JSON or null.
	 * @param array|null $settings Settings.
	 * @return bool
	 */
	public static function response_is_success( $body, $settings = null ) {
		if ( ! is_array( $body ) || empty( $body['success'] ) ) {
			return false;
		}
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		if ( 'v3' !== $s['provider'] ) {
			return true;
		}
		$score = isset( $body['score'] ) ? (float) $body['score'] : 0;
		if ( $score < (float) $s['score'] ) {
			return false;
		}
		$action = isset( $body['action'] ) ? (string) $body['action'] : '';
		if ( 'tsolo' !== $action ) {
			return false;
		}
		if ( empty( $body['hostname'] ) ) {
			return true;
		}
		return self::hostname_allowed( (string) $body['hostname'] );
	}

	/**
	 * Whether a reCAPTCHA hostname matches this site.
	 *
	 * @param string $hostname Hostname from siteverify.
	 * @return bool
	 */
	public static function hostname_allowed( $hostname ) {
		$hostname = strtolower( preg_replace( '/:\d+$/', '', (string) $hostname ) );
		if ( '' === $hostname ) {
			return false;
		}
		$hosts = array();
		foreach ( array( home_url(), site_url() ) as $url ) {
			$host = wp_parse_url( $url, PHP_URL_HOST );
			if ( is_string( $host ) && '' !== $host ) {
				$host    = strtolower( $host );
				$hosts[] = $host;
				$hosts[] = preg_replace( '/^www\./', '', $host );
				$hosts[] = 'www.' . preg_replace( '/^www\./', '', $host );
			}
		}
		$hosts = array_values( array_unique( array_filter( $hosts ) ) );
		return in_array( $hostname, $hosts, true );
	}

	/**
	 * Whether this request should enforce captcha.
	 *
	 * @return bool
	 */
	public static function should_verify_request() {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return false;
		}
		if ( is_admin() && ! ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
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
	 * Enqueue on frontend action / shortcode pages.
	 */
	public function enqueue() {
		if ( ! tsolo_is_action() && ! tsolo_content_has_shortcode() ) {
			return;
		}
		$this->enqueue_api_script();
	}

	/**
	 * Enqueue on wp-login.php.
	 */
	public function enqueue_login() {
		if ( ! function_exists( 'tsolo_is_wp_login' ) || ! tsolo_is_wp_login() ) {
			return;
		}
		$this->enqueue_api_script();
	}

	/**
	 * Register provider script + helper for v3.
	 */
	private function enqueue_api_script() {
		$s = self::get_settings();
		if ( wp_script_is( 'tsolo-captcha-api', 'enqueued' ) ) {
			return;
		}
		wp_enqueue_script(
			'tsolo-captcha-api',
			self::api_script_url( $s ),
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- CDN self-versions.
			true
		);
		if ( 'v3' === $s['provider'] ) {
			wp_enqueue_script(
				'tsolo-captcha',
				TSOLO_URL . 'assets/js/tsolo-captcha.js',
				array( 'tsolo-captcha-api' ),
				TSOLO_VERSION,
				true
			);
			wp_localize_script(
				'tsolo-captcha',
				'tsoloCaptchaConfig',
				array(
					'provider' => 'v3',
					'siteKey'  => $s['site_key'],
					'action'   => 'tsolo',
				)
			);
		}
	}

	/**
	 * Render widget markup.
	 */
	public function render_widget() {
		$s = self::get_settings();
		if ( '' === $s['site_key'] ) {
			return;
		}
		if ( 'turnstile' === $s['provider'] ) {
			echo '<div class="cf-turnstile tsolo-turnstile" data-sitekey="' . esc_attr( $s['site_key'] ) . '"></div>';
			return;
		}
		if ( 'v3' === $s['provider'] ) {
			echo '<input type="hidden" name="g-recaptcha-response" class="tsolo-recaptcha-v3-token" value="" />';
			echo '<p class="tsolo-recaptcha-v3-note description">' . esc_html__( 'This site is protected by reCAPTCHA.', 'tso-login-organizer' ) . '</p>';
			return;
		}
		echo '<div class="g-recaptcha tsolo-recaptcha" data-sitekey="' . esc_attr( $s['site_key'] ) . '"></div>';
	}

	/**
	 * POST token field name for the provider.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	public static function token_field( $s ) {
		return ( 'turnstile' === $s['provider'] ) ? 'cf-turnstile-response' : 'g-recaptcha-response';
	}

	/**
	 * Verify token with the provider.
	 *
	 * @return true|WP_Error
	 */
	private function check_token() {
		$s     = self::get_settings();
		$token = tsolo_get_request_text( self::token_field( $s ), 'post' );
		if ( '' === $token ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Please complete the captcha.', 'tso-login-organizer' ) );
		}
		$body = array(
			'secret'   => $s['secret_key'],
			'response' => $token,
		);
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$body['remoteip'] = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		$response = wp_remote_post(
			self::verify_endpoint( $s ),
			array(
				'timeout' => 10,
				'body'    => $body,
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Captcha verification failed.', 'tso-login-organizer' ) );
		}
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! self::response_is_success( $decoded, $s ) ) {
			return new WP_Error( 'tsolo_captcha', __( '<strong>Error:</strong> Captcha verification failed.', 'tso-login-organizer' ) );
		}
		return true;
	}

	/**
	 * Verify on registration.
	 *
	 * @param WP_Error $errors Errors.
	 * @return WP_Error
	 */
	public function verify( $errors ) {
		if ( ! self::should_verify_request() || ! tsolo_request_post_is_set( 'user_email' ) ) {
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
	 * @param mixed  $user     User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function verify_login( $user, $username, $password ) {
		unset( $username, $password );
		if ( ! self::should_verify_request() || ! tsolo_request_post_is_set( 'log' ) ) {
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
		if ( ! ( $errors instanceof WP_Error ) || ! self::should_verify_request() ) {
			return;
		}
		$result = $this->check_token();
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}
	}
}
