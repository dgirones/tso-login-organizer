<?php
/**
 * Role-based redirection module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Redirection
 */
class TSOLO_Mod_Redirection {

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
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 20, 3 );
		add_filter( 'logout_redirect', array( $this, 'logout_redirect' ), 20, 3 );
		add_filter( 'tsolo_registration_redirect', array( $this, 'registration_redirect' ), 20, 2 );
	}

	/**
	 * Settings defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'login'    => array(),
			'logout'   => array(),
			'register' => array(),
		);
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_REDIRECTION, self::defaults() );
	}

	/**
	 * Sanitize a full redirection settings blob.
	 *
	 * @param array $raw Raw maps keyed by login|logout|register.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		$out = self::defaults();
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		foreach ( array_keys( $out ) as $type ) {
			$out[ $type ] = self::sanitize_map( isset( $raw[ $type ] ) ? $raw[ $type ] : array() );
		}
		return $out;
	}

	/**
	 * Sanitize one type map (role => URL).
	 *
	 * @param mixed $map Map.
	 * @return array<string,string>
	 */
	public static function sanitize_map( $map ) {
		if ( ! is_array( $map ) ) {
			return array();
		}
		$clean = array();
		foreach ( $map as $role => $url ) {
			$role = ( '_default' === $role ) ? '_default' : sanitize_key( (string) $role );
			if ( '' === $role ) {
				continue;
			}
			$url = self::sanitize_redirect_url( $url );
			if ( '' === $url ) {
				continue;
			}
			$clean[ $role ] = $url;
		}
		return $clean;
	}

	/**
	 * Allow only http(s) absolute URLs (empty if invalid).
	 *
	 * @param mixed $url Raw URL.
	 * @return string
	 */
	public static function sanitize_redirect_url( $url ) {
		if ( ! is_string( $url ) ) {
			return '';
		}
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}
		$url = esc_url_raw( $url );
		if ( '' === $url ) {
			return '';
		}
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return '';
		}
		return $url;
	}

	/**
	 * Resolve URL for user roles (role override beats _default).
	 *
	 * @param string       $type     login|logout|register.
	 * @param WP_User|null $user     User (may be empty after failed login).
	 * @param string       $fallback Fallback URL.
	 * @param array|null   $settings Optional settings override (tests).
	 * @return string
	 */
	public static function resolve_for( $type, $user, $fallback, $settings = null ) {
		$type     = sanitize_key( (string) $type );
		$settings = is_array( $settings ) ? $settings : self::get_settings();
		$map      = isset( $settings[ $type ] ) && is_array( $settings[ $type ] ) ? $settings[ $type ] : array();
		$fallback = is_string( $fallback ) ? $fallback : '';

		if ( $user instanceof WP_User && ! empty( $user->ID ) ) {
			foreach ( (array) $user->roles as $role ) {
				$role = sanitize_key( (string) $role );
				if ( '' === $role || empty( $map[ $role ] ) ) {
					continue;
				}
				$url = self::sanitize_redirect_url( $map[ $role ] );
				if ( '' !== $url ) {
					return $url;
				}
			}
		}

		if ( ! empty( $map['_default'] ) ) {
			$url = self::sanitize_redirect_url( $map['_default'] );
			if ( '' !== $url ) {
				return $url;
			}
		}

		return $fallback;
	}

	/**
	 * Login redirect.
	 *
	 * @param string           $redirect_to Redirect.
	 * @param string           $requested   Requested.
	 * @param WP_User|WP_Error $user        User.
	 * @return string
	 */
	public function login_redirect( $redirect_to, $requested, $user ) {
		unset( $requested );
		if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
			return $redirect_to;
		}
		return self::resolve_for( 'login', $user, $redirect_to );
	}

	/**
	 * Logout redirect.
	 *
	 * @param string  $redirect_to Redirect.
	 * @param string  $requested   Requested.
	 * @param WP_User $user        User (must be the pre-logout user).
	 * @return string
	 */
	public function logout_redirect( $redirect_to, $requested, $user ) {
		unset( $requested );
		return self::resolve_for( 'logout', $user instanceof WP_User ? $user : null, $redirect_to );
	}

	/**
	 * Registration redirect.
	 *
	 * @param string $redirect Redirect.
	 * @param int    $user_id  User ID.
	 * @return string
	 */
	public function registration_redirect( $redirect, $user_id ) {
		$user = get_userdata( absint( $user_id ) );
		return self::resolve_for( 'register', $user instanceof WP_User ? $user : null, $redirect );
	}
}
