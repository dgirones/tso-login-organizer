<?php
/**
 * Security module: brute-force, password rules, hide wp-login.php.
 *
 * OFF by default — collides with Swiss Knife Login Protect and Kadence Security.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Security
 */
class TSOLO_Mod_Security {

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
		if ( ! empty( $s['brute_force'] ) ) {
			add_filter( 'authenticate', array( $this, 'check_lockout' ), 1, 3 );
			add_action( 'wp_login_failed', array( $this, 'record_failure' ) );
			add_action( 'wp_login', array( $this, 'clear_attempts' ), 10, 2 );
		}
		if ( ! empty( $s['password_min_length'] ) ) {
			add_action( 'validate_password_reset', array( $this, 'validate_password' ), 10, 2 );
			add_filter( 'registration_errors', array( $this, 'validate_registration_password' ) );
		}
		if ( ! empty( $s['hide_wp_login'] ) ) {
			add_action( 'login_init', array( $this, 'block_wp_login' ), 0 );
		}
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'brute_force'         => 0,
			'max_attempts'        => 5,
			'lockout_minutes'     => 30,
			'password_min_length' => 0,
			'hide_wp_login'       => 0,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_SECURITY, self::defaults() );
	}

	/**
	 * Client IP.
	 *
	 * @return string
	 */
	private function ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return $ip ? $ip : '0.0.0.0';
	}

	/**
	 * Check lockout before auth.
	 *
	 * @param mixed  $user User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function check_lockout( $user, $username, $password ) {
		unset( $username, $password );
		$s        = self::get_settings();
		$lockouts = get_option( TSOLO_OPT_LOCKOUTS, array() );
		$ip       = $this->ip();
		if ( ! empty( $lockouts[ $ip ] ) && time() < (int) $lockouts[ $ip ] ) {
			return new WP_Error( 'tsolo_locked', __( '<strong>Error:</strong> Too many failed login attempts. Please try again later.', 'tso-login-organizer' ) );
		}
		return $user;
	}

	/**
	 * Record failed attempt.
	 *
	 * @param string $username Username.
	 */
	public function record_failure( $username ) {
		unset( $username );
		$s        = self::get_settings();
		$ip       = $this->ip();
		$attempts = get_option( TSOLO_OPT_ATTEMPTS, array() );
		if ( ! is_array( $attempts ) ) {
			$attempts = array();
		}
		if ( empty( $attempts[ $ip ] ) || ! is_array( $attempts[ $ip ] ) ) {
			$attempts[ $ip ] = array(
				'count' => 0,
				'first' => time(),
			);
		}
		++$attempts[ $ip ]['count'];
		$max = max( 1, absint( $s['max_attempts'] ) );
		if ( $attempts[ $ip ]['count'] >= $max ) {
			$lockouts        = get_option( TSOLO_OPT_LOCKOUTS, array() );
			$lockouts[ $ip ] = time() + ( max( 1, absint( $s['lockout_minutes'] ) ) * MINUTE_IN_SECONDS );
			update_option( TSOLO_OPT_LOCKOUTS, $lockouts, false );
			unset( $attempts[ $ip ] );
		}
		update_option( TSOLO_OPT_ATTEMPTS, $attempts, false );
	}

	/**
	 * Clear attempts on success.
	 *
	 * @param string  $login Login.
	 * @param WP_User $user  User.
	 */
	public function clear_attempts( $login, $user ) {
		unset( $login, $user );
		$ip       = $this->ip();
		$attempts = get_option( TSOLO_OPT_ATTEMPTS, array() );
		if ( isset( $attempts[ $ip ] ) ) {
			unset( $attempts[ $ip ] );
			update_option( TSOLO_OPT_ATTEMPTS, $attempts, false );
		}
	}

	/**
	 * Validate password length on reset.
	 *
	 * @param WP_Error $errors Errors.
	 * @param WP_User  $user   User.
	 */
	public function validate_password( $errors, $user ) {
		unset( $user );
		$s   = self::get_settings();
		$min = absint( $s['password_min_length'] );
		if ( $min < 1 ) {
			return;
		}
		$pass = tsolo_get_request_text( 'pass1', 'post' );
		if ( strlen( $pass ) < $min ) {
			$errors->add(
				'tsolo_password_short',
				sprintf(
					/* translators: %d: minimum length */
					__( '<strong>Error:</strong> Password must be at least %d characters.', 'tso-login-organizer' ),
					$min
				)
			);
		}
	}

	/**
	 * Validate registration password.
	 *
	 * @param WP_Error $errors Errors.
	 * @return WP_Error
	 */
	public function validate_registration_password( $errors ) {
		$s   = self::get_settings();
		$min = absint( $s['password_min_length'] );
		if ( $min < 1 ) {
			return $errors;
		}
		$pass = tsolo_get_request_text( 'pass1', 'post' );
		if ( $pass && strlen( $pass ) < $min ) {
			$errors->add(
				'tsolo_password_short',
				sprintf(
					/* translators: %d: minimum length */
					__( '<strong>Error:</strong> Password must be at least %d characters.', 'tso-login-organizer' ),
					$min
				)
			);
		}
		return $errors;
	}

	/**
	 * Block direct wp-login.php access (frontend TSOLO pages still work).
	 */
	public function block_wp_login() {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return;
		}
		// Allow logout and interim login urgently.
		$action = tsolo_get_request_text( 'action', 'get' );
		if ( in_array( $action, array( 'logout', 'postpass' ), true ) ) {
			return;
		}
		status_header( 404 );
		nocache_headers();
		wp_die( esc_html__( 'Not found.', 'tso-login-organizer' ), 404 );
	}
}
