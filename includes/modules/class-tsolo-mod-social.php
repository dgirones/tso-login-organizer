<?php
/**
 * Social login module (Google / Facebook / X) — opt-in, requires API keys.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Social
 */
class TSOLO_Mod_Social {

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
		add_action( 'tsolo_after_form', array( $this, 'render_buttons' ) );
		add_action( 'init', array( $this, 'handle_callback' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'google_client_id'     => '',
			'google_client_secret' => '',
			'facebook_app_id'      => '',
			'facebook_app_secret'  => '',
			'x_client_id'          => '',
			'x_client_secret'      => '',
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_SOCIAL, self::defaults() );
	}

	/**
	 * Callback URL.
	 *
	 * @param string $provider Provider.
	 * @return string
	 */
	private function callback_url( $provider ) {
		return add_query_arg(
			array(
				'tsolo_social' => sanitize_key( $provider ),
			),
			home_url( '/' )
		);
	}

	/**
	 * Render buttons under login/register.
	 *
	 * @param string $form Form.
	 */
	public function render_buttons( $form ) {
		if ( ! in_array( $form, array( 'login', 'register' ), true ) ) {
			return;
		}
		$s     = self::get_settings();
		$links = array();
		if ( $s['google_client_id'] && $s['google_client_secret'] ) {
			$links['google'] = $this->google_auth_url();
		}
		if ( $s['facebook_app_id'] && $s['facebook_app_secret'] ) {
			$links['facebook'] = $this->facebook_auth_url();
		}
		if ( $s['x_client_id'] && $s['x_client_secret'] ) {
			$links['x'] = $this->x_auth_url();
		}
		if ( empty( $links ) ) {
			return;
		}
		echo '<div class="tsolo-social">';
		foreach ( $links as $provider => $url ) {
			$label = 'google' === $provider ? __( 'Continue with Google', 'tso-login-organizer' ) : ( 'facebook' === $provider ? __( 'Continue with Facebook', 'tso-login-organizer' ) : __( 'Continue with X', 'tso-login-organizer' ) );
			echo '<p><a class="tsolo-button tsolo-social-' . esc_attr( $provider ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></p>';
		}
		echo '</div>';
	}

	/**
	 * Google auth URL.
	 *
	 * @return string
	 */
	private function google_auth_url() {
		$s = self::get_settings();
		return add_query_arg(
			array(
				'client_id'     => $s['google_client_id'],
				'redirect_uri'  => $this->callback_url( 'google' ),
				'response_type' => 'code',
				'scope'         => 'openid email profile',
				'state'         => wp_create_nonce( 'tsolo_social_google' ),
			),
			'https://accounts.google.com/o/oauth2/v2/auth'
		);
	}

	/**
	 * Facebook auth URL.
	 *
	 * @return string
	 */
	private function facebook_auth_url() {
		$s = self::get_settings();
		return add_query_arg(
			array(
				'client_id'    => $s['facebook_app_id'],
				'redirect_uri' => $this->callback_url( 'facebook' ),
				'scope'        => 'email,public_profile',
				'state'        => wp_create_nonce( 'tsolo_social_facebook' ),
			),
			'https://www.facebook.com/v19.0/dialog/oauth'
		);
	}

	/**
	 * X auth URL (OAuth 2).
	 *
	 * @return string
	 */
	private function x_auth_url() {
		$s = self::get_settings();
		return add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $s['x_client_id'],
				'redirect_uri'  => $this->callback_url( 'x' ),
				'scope'         => 'users.read tweet.read offline.access',
				'state'         => wp_create_nonce( 'tsolo_social_x' ),
			),
			'https://twitter.com/i/oauth2/authorize'
		);
	}

	/**
	 * Handle OAuth callback.
	 */
	public function handle_callback() {
		$provider = tsolo_get_request_text( 'tsolo_social', 'get' );
		if ( ! in_array( $provider, array( 'google', 'facebook', 'x' ), true ) ) {
			return;
		}
		$code  = tsolo_get_request_text( 'code', 'get' );
		$state = tsolo_get_request_text( 'state', 'get' );
		if ( ! $code || ! wp_verify_nonce( $state, 'tsolo_social_' . $provider ) ) {
			wp_die( esc_html__( 'Invalid social login request.', 'tso-login-organizer' ), 403 );
		}
		$email = '';
		$name  = '';
		if ( 'google' === $provider ) {
			$data  = $this->exchange_google( $code );
			$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
			$name  = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		} elseif ( 'facebook' === $provider ) {
			$data  = $this->exchange_facebook( $code );
			$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
			$name  = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		} else {
			$data  = $this->exchange_x( $code );
			$name  = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
			$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		}
		if ( ! $email ) {
			wp_die( esc_html__( 'Social login did not return an email address.', 'tso-login-organizer' ) );
		}
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			$login = sanitize_user( current( explode( '@', $email ) ), true );
			if ( username_exists( $login ) ) {
				$login .= wp_generate_password( 4, false );
			}
			$user_id = wp_create_user( $login, wp_generate_password( 24 ), $email );
			if ( is_wp_error( $user_id ) ) {
				wp_die( esc_html( $user_id->get_error_message() ) );
			}
			if ( $name ) {
				wp_update_user(
					array(
						'ID'           => $user_id,
						'display_name' => $name,
					)
				);
			}
			update_user_meta( $user_id, 'tsolo_social_provider', $provider );
			$user = get_userdata( $user_id );
		}
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login_redirect filter.
		wp_safe_redirect( apply_filters( 'login_redirect', admin_url(), '', $user ) );
		exit;
	}

	/**
	 * Exchange Google code.
	 *
	 * @param string $code Code.
	 * @return array
	 */
	private function exchange_google( $code ) {
		$s    = self::get_settings();
		$resp = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			array(
				'timeout' => 15,
				'body'    => array(
					'code'          => $code,
					'client_id'     => $s['google_client_id'],
					'client_secret' => $s['google_client_secret'],
					'redirect_uri'  => $this->callback_url( 'google' ),
					'grant_type'    => 'authorization_code',
				),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return array();
		}
		$token = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $token['access_token'] ) ) {
			return array();
		}
		$info = wp_remote_get(
			'https://www.googleapis.com/oauth2/v3/userinfo',
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ),
			)
		);
		if ( is_wp_error( $info ) ) {
			return array();
		}
		$data = json_decode( wp_remote_retrieve_body( $info ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Exchange Facebook code.
	 *
	 * @param string $code Code.
	 * @return array
	 */
	private function exchange_facebook( $code ) {
		$s    = self::get_settings();
		$resp = wp_remote_get(
			add_query_arg(
				array(
					'client_id'     => $s['facebook_app_id'],
					'client_secret' => $s['facebook_app_secret'],
					'redirect_uri'  => $this->callback_url( 'facebook' ),
					'code'          => $code,
				),
				'https://graph.facebook.com/v19.0/oauth/access_token'
			),
			array( 'timeout' => 15 )
		);
		if ( is_wp_error( $resp ) ) {
			return array();
		}
		$token = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $token['access_token'] ) ) {
			return array();
		}
		$info = wp_remote_get(
			add_query_arg(
				array(
					'fields'       => 'id,name,email',
					'access_token' => $token['access_token'],
				),
				'https://graph.facebook.com/me'
			),
			array( 'timeout' => 15 )
		);
		if ( is_wp_error( $info ) ) {
			return array();
		}
		$data = json_decode( wp_remote_retrieve_body( $info ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Exchange X code (best-effort).
	 *
	 * @param string $code Code.
	 * @return array
	 */
	private function exchange_x( $code ) {
		$s    = self::get_settings();
		$resp = wp_remote_post(
			'https://api.twitter.com/2/oauth2/token',
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type'  => 'application/x-www-form-urlencoded',
					'Authorization' => 'Basic ' . base64_encode( $s['x_client_id'] . ':' . $s['x_client_secret'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- OAuth client basic auth.
				),
				'body'    => array(
					'code'          => $code,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $this->callback_url( 'x' ),
					'code_verifier' => 'challenge',
				),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return array();
		}
		$token = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $token['access_token'] ) ) {
			return array();
		}
		$info = wp_remote_get(
			'https://api.twitter.com/2/users/me?user.fields=name',
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ),
			)
		);
		if ( is_wp_error( $info ) ) {
			return array();
		}
		$body = json_decode( wp_remote_retrieve_body( $info ), true );
		if ( empty( $body['data'] ) ) {
			return array();
		}
		return array(
			'name'  => isset( $body['data']['name'] ) ? $body['data']['name'] : '',
			'email' => '',
		);
	}
}
