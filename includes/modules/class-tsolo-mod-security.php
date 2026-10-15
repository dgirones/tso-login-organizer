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
		$s                        = tsolo_get_option_array( TSOLO_OPT_SECURITY, self::defaults() );
		$s['brute_force']         = empty( $s['brute_force'] ) ? 0 : 1;
		$s['hide_wp_login']       = empty( $s['hide_wp_login'] ) ? 0 : 1;
		$s['max_attempts']        = max( 1, (int) ( isset( $s['max_attempts'] ) ? $s['max_attempts'] : 5 ) );
		$s['lockout_minutes']     = max( 1, (int) ( isset( $s['lockout_minutes'] ) ? $s['lockout_minutes'] : 30 ) );
		$s['password_min_length'] = max( 0, (int) ( isset( $s['password_min_length'] ) ? $s['password_min_length'] : 0 ) );
		return $s;
	}

	/**
	 * Sanitize settings before storage.
	 *
	 * @param array $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array(
			'brute_force'         => empty( $raw['brute_force'] ) ? 0 : 1,
			'hide_wp_login'       => empty( $raw['hide_wp_login'] ) ? 0 : 1,
			'max_attempts'        => max( 1, (int) ( isset( $raw['max_attempts'] ) ? $raw['max_attempts'] : 5 ) ),
			'lockout_minutes'     => max( 1, (int) ( isset( $raw['lockout_minutes'] ) ? $raw['lockout_minutes'] : 30 ) ),
			'password_min_length' => max( 0, (int) ( isset( $raw['password_min_length'] ) ? $raw['password_min_length'] : 0 ) ),
		);
	}

	/**
	 * Client IP (overridable in tests via filter).
	 *
	 * @return string
	 */
	public static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip = $ip ? $ip : '0.0.0.0';
		/**
		 * Filter the IP used for brute-force tracking.
		 *
		 * @param string $ip IP.
		 */
		return (string) apply_filters( 'tsolo_security_client_ip', $ip );
	}

	/**
	 * Whether an IP is currently locked out.
	 *
	 * @param string     $ip       IP.
	 * @param array|null $lockouts Lockouts map or null to load.
	 * @param int|null   $now      Unix time.
	 * @return bool
	 */
	public static function is_locked_out( $ip, $lockouts = null, $now = null ) {
		$ip       = (string) $ip;
		$now      = null === $now ? time() : (int) $now;
		$lockouts = is_array( $lockouts ) ? $lockouts : get_option( TSOLO_OPT_LOCKOUTS, array() );
		if ( ! is_array( $lockouts ) || empty( $lockouts[ $ip ] ) ) {
			return false;
		}
		return $now < (int) $lockouts[ $ip ];
	}

	/**
	 * Purge expired lockout timestamps from a map.
	 *
	 * @param array    $lockouts Map.
	 * @param int|null $now      Unix time.
	 * @return array
	 */
	public static function purge_expired_lockouts( $lockouts, $now = null ) {
		if ( ! is_array( $lockouts ) ) {
			return array();
		}
		$now = null === $now ? time() : (int) $now;
		foreach ( $lockouts as $ip => $until ) {
			if ( (int) $until <= $now ) {
				unset( $lockouts[ $ip ] );
			}
		}
		return $lockouts;
	}

	/**
	 * Apply one failed attempt; returns updated attempts + whether a lockout was created.
	 *
	 * Attempts older than the lockout window are reset so sparse failures do not accumulate forever.
	 *
	 * @param array  $attempts Existing attempts map.
	 * @param string $ip       IP.
	 * @param int    $max      Max attempts.
	 * @param int    $window   Window seconds (usually lockout_minutes * 60).
	 * @param int    $now      Unix time.
	 * @return array{attempts:array,locked:bool,lock_until:int}
	 */
	public static function apply_failure( $attempts, $ip, $max, $window, $now ) {
		$ip     = (string) $ip;
		$max    = max( 1, (int) $max );
		$window = max( 1, (int) $window );
		$now    = (int) $now;
		$locked = false;
		$until  = 0;
		if ( ! is_array( $attempts ) ) {
			$attempts = array();
		}
		$row   = ( isset( $attempts[ $ip ] ) && is_array( $attempts[ $ip ] ) ) ? $attempts[ $ip ] : array(
			'count' => 0,
			'first' => $now,
		);
		$first = isset( $row['first'] ) ? (int) $row['first'] : $now;
		$count = isset( $row['count'] ) ? (int) $row['count'] : 0;
		if ( $first > 0 && ( $now - $first ) > $window ) {
			$count = 0;
			$first = $now;
		}
		++$count;
		if ( $count >= $max ) {
			$locked = true;
			$until  = $now + $window;
			unset( $attempts[ $ip ] );
		} else {
			$attempts[ $ip ] = array(
				'count' => $count,
				'first' => $first ? $first : $now,
			);
		}
		return array(
			'attempts'   => $attempts,
			'locked'     => $locked,
			'lock_until' => $until,
		);
	}

	/**
	 * Whether a password meets the configured minimum length.
	 *
	 * @param string $password Password.
	 * @param int    $min      Minimum length (0 = no rule).
	 * @return bool
	 */
	public static function password_meets_min_length( $password, $min ) {
		$min = absint( $min );
		if ( $min < 1 ) {
			return true;
		}
		return strlen( (string) $password ) >= $min;
	}

	/**
	 * Whether a wp-login.php action must stay reachable when the file is hidden.
	 *
	 * @param string $action Action.
	 * @return bool
	 */
	public static function is_allowed_wp_login_action( $action ) {
		$action = sanitize_key( (string) $action );
		// Keep logout / post password / confirm + in-flight reset links that still point at wp-login.php.
		$allow = array( 'logout', 'postpass', 'confirmaction', 'rp', 'resetpass' );
		/**
		 * Filter allowed wp-login.php actions when hide_wp_login is on.
		 *
		 * @param string[] $allow  Actions.
		 * @param string   $action Current action.
		 */
		$allow = apply_filters( 'tsolo_security_allowed_wp_login_actions', $allow, $action );
		return in_array( $action, (array) $allow, true );
	}

	/**
	 * Check lockout before auth.
	 *
	 * @param mixed  $user     User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function check_lockout( $user, $username, $password ) {
		unset( $username, $password );
		$ip       = self::client_ip();
		$lockouts = get_option( TSOLO_OPT_LOCKOUTS, array() );
		if ( ! is_array( $lockouts ) ) {
			$lockouts = array();
		}
		$clean = self::purge_expired_lockouts( $lockouts );
		if ( $clean !== $lockouts ) {
			update_option( TSOLO_OPT_LOCKOUTS, $clean, false );
			$lockouts = $clean;
		}
		if ( self::is_locked_out( $ip, $lockouts ) ) {
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
		$ip       = self::client_ip();
		$attempts = get_option( TSOLO_OPT_ATTEMPTS, array() );
		$window   = max( 1, absint( $s['lockout_minutes'] ) ) * MINUTE_IN_SECONDS;
		$result   = self::apply_failure( $attempts, $ip, $s['max_attempts'], $window, time() );
		if ( ! empty( $result['locked'] ) ) {
			$lockouts        = self::purge_expired_lockouts( get_option( TSOLO_OPT_LOCKOUTS, array() ) );
			$lockouts[ $ip ] = (int) $result['lock_until'];
			update_option( TSOLO_OPT_LOCKOUTS, $lockouts, false );
		}
		update_option( TSOLO_OPT_ATTEMPTS, $result['attempts'], false );
	}

	/**
	 * Clear attempts (and any lockout) on success.
	 *
	 * @param string  $login Login.
	 * @param WP_User $user  User.
	 */
	public function clear_attempts( $login, $user ) {
		unset( $login, $user );
		$ip       = self::client_ip();
		$attempts = get_option( TSOLO_OPT_ATTEMPTS, array() );
		$changed  = false;
		if ( is_array( $attempts ) && isset( $attempts[ $ip ] ) ) {
			unset( $attempts[ $ip ] );
			update_option( TSOLO_OPT_ATTEMPTS, $attempts, false );
			$changed = true;
		}
		$lockouts = get_option( TSOLO_OPT_LOCKOUTS, array() );
		if ( is_array( $lockouts ) && isset( $lockouts[ $ip ] ) ) {
			unset( $lockouts[ $ip ] );
			update_option( TSOLO_OPT_LOCKOUTS, $lockouts, false );
			$changed = true;
		}
		unset( $changed );
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
		if ( ! self::password_meets_min_length( $pass, $min ) ) {
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
		if ( '' === $pass ) {
			$pass = tsolo_get_request_text( 'user_pass', 'post' );
		}
		// Only enforce when a password was submitted (user-chooses-password flows).
		if ( '' === $pass ) {
			return $errors;
		}
		if ( ! self::password_meets_min_length( $pass, $min ) ) {
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
	 *
	 * Core emails are rewritten to frontend URLs via tsolo_filter_site_url, so
	 * reset/lost-password links do not need wp-login.php. Logout and postpass stay.
	 */
	public function block_wp_login() {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return;
		}
		$action = tsolo_get_request_text( 'action', 'get' );
		if ( '' === $action ) {
			$action = tsolo_get_request_text( 'action', 'post' );
		}
		if ( self::is_allowed_wp_login_action( $action ) ) {
			return;
		}
		status_header( 404 );
		nocache_headers();
		wp_die( esc_html__( 'Not found.', 'tso-login-organizer' ), 404 );
	}
}
