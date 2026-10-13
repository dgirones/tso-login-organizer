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
	 * Resolve URL for user roles.
	 *
	 * @param string  $type login|logout|register.
	 * @param WP_User $user User.
	 * @param string  $fallback Fallback URL.
	 * @return string
	 */
	private function resolve( $type, $user, $fallback ) {
		$settings = self::get_settings();
		$map      = isset( $settings[ $type ] ) && is_array( $settings[ $type ] ) ? $settings[ $type ] : array();
		if ( ! $user instanceof WP_User ) {
			return $fallback;
		}
		foreach ( (array) $user->roles as $role ) {
			if ( ! empty( $map[ $role ] ) ) {
				return esc_url_raw( $map[ $role ] );
			}
		}
		if ( ! empty( $map['_default'] ) ) {
			return esc_url_raw( $map['_default'] );
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
		if ( is_wp_error( $user ) || ! $user ) {
			return $redirect_to;
		}
		return $this->resolve( 'login', $user, $redirect_to );
	}

	/**
	 * Logout redirect.
	 *
	 * @param string  $redirect_to Redirect.
	 * @param string  $requested   Requested.
	 * @param WP_User $user        User.
	 * @return string
	 */
	public function logout_redirect( $redirect_to, $requested, $user ) {
		return $this->resolve( 'logout', $user, $redirect_to );
	}

	/**
	 * Registration redirect.
	 *
	 * @param string $redirect Redirect.
	 * @param int    $user_id  User ID.
	 * @return string
	 */
	public function registration_redirect( $redirect, $user_id ) {
		$user = get_userdata( $user_id );
		return $this->resolve( 'register', $user, $redirect );
	}
}
