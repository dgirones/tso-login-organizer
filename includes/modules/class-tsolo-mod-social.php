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
	 * User meta: last social provider slug.
	 *
	 * @var string
	 */
	const META_PROVIDER = 'tsolo_social_provider';

	/**
	 * User meta: provider:remote_id identity.
	 *
	 * @var string
	 */
	const META_IDENTITY = 'tsolo_social_identity';

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
		add_action( 'tsolo_profile_fields', array( $this, 'render_profile_links' ), 20 );
		add_action( 'init', array( $this, 'handle_callback' ), 5 );
		add_action( 'template_redirect', array( $this, 'handle_unlink' ), 2 );
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
			'link_existing'        => 1,
		);
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
		$out = self::defaults();
		foreach ( array_keys( $out ) as $key ) {
			if ( 'link_existing' === $key ) {
				continue;
			}
			if ( ! isset( $raw[ $key ] ) ) {
				continue;
			}
			$val         = preg_replace( '/\s+/', '', sanitize_text_field( (string) $raw[ $key ] ) );
			$out[ $key ] = is_string( $val ) ? $val : '';
		}
		if ( array_key_exists( 'link_existing', $raw ) ) {
			$out['link_existing'] = empty( $raw['link_existing'] ) ? 0 : 1;
		}
		return $out;
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_SOCIAL, self::defaults() ) );
	}

	/**
	 * Whether a provider has both credentials.
	 *
	 * @param string     $provider google|facebook|x.
	 * @param array|null $settings Settings.
	 * @return bool
	 */
	public static function provider_ready( $provider, $settings = null ) {
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		if ( 'google' === $provider ) {
			return ( '' !== $s['google_client_id'] && '' !== $s['google_client_secret'] );
		}
		if ( 'facebook' === $provider ) {
			return ( '' !== $s['facebook_app_id'] && '' !== $s['facebook_app_secret'] );
		}
		if ( 'x' === $provider ) {
			return ( '' !== $s['x_client_id'] && '' !== $s['x_client_secret'] );
		}
		return false;
	}

	/**
	 * Callback URL for a provider.
	 *
	 * @param string $provider Provider.
	 * @return string
	 */
	public static function callback_url( $provider ) {
		return add_query_arg(
			array(
				'tsolo_social' => sanitize_key( $provider ),
			),
			home_url( '/' )
		);
	}

	/**
	 * Build identity meta value.
	 *
	 * @param string $provider Provider.
	 * @param string $remote_id Remote user id.
	 * @return string
	 */
	public static function identity_key( $provider, $remote_id ) {
		return sanitize_key( $provider ) . ':' . sanitize_text_field( (string) $remote_id );
	}

	/**
	 * Synthetic email when a provider has no email (X).
	 *
	 * @param string $provider Provider.
	 * @param string $remote_id Remote id.
	 * @return string
	 */
	public static function synthetic_email( $provider, $remote_id ) {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			$host = 'localhost';
		}
		$local = sanitize_user( $provider . '_' . $remote_id, true );
		if ( '' === $local ) {
			$local = 'social_' . md5( $provider . $remote_id );
		}
		$email = strtolower( $local ) . '@' . $host;
		return is_email( $email ) ? $email : ( 'social.' . md5( $provider . $remote_id ) . '@' . $host );
	}

	/**
	 * Find user by social identity meta.
	 *
	 * @param string $identity Identity key.
	 * @return WP_User|null
	 */
	public static function find_user_by_identity( $identity ) {
		$identity = (string) $identity;
		if ( '' === $identity ) {
			return null;
		}
		$users = get_users(
			array(
				'meta_key'    => self::META_IDENTITY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Lookup by unique social identity.
				'meta_value'  => $identity, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Exact match, single user.
				'number'      => 1,
				'count_total' => false,
			)
		);
		return ! empty( $users[0] ) && $users[0] instanceof WP_User ? $users[0] : null;
	}

	/**
	 * Attach social identity meta to a user (refuses if identity belongs to another).
	 *
	 * @param WP_User $user     User.
	 * @param string  $provider Provider.
	 * @param string  $remote_id Remote id.
	 * @return true|WP_Error
	 */
	public static function attach_identity( $user, $provider, $remote_id ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return new WP_Error( 'tsolo_social_user', __( 'Invalid user for social login.', 'tso-login-organizer' ) );
		}
		$provider  = sanitize_key( $provider );
		$remote_id = sanitize_text_field( (string) $remote_id );
		if ( '' !== $remote_id ) {
			$identity = self::identity_key( $provider, $remote_id );
			$owner    = self::find_user_by_identity( $identity );
			if ( $owner && (int) $owner->ID !== (int) $user->ID ) {
				return new WP_Error( 'tsolo_social_taken', __( 'This social account is already linked to another user.', 'tso-login-organizer' ) );
			}
			update_user_meta( $user->ID, self::META_IDENTITY, $identity );
		}
		update_user_meta( $user->ID, self::META_PROVIDER, $provider );
		return true;
	}

	/**
	 * Resolve or create a WP user from social profile data.
	 *
	 * @param string $provider Provider.
	 * @param array  $profile  Keys: id, email, name.
	 * @return WP_User|WP_Error
	 */
	public static function resolve_user( $provider, $profile ) {
		$provider  = sanitize_key( $provider );
		$remote_id = isset( $profile['id'] ) ? sanitize_text_field( (string) $profile['id'] ) : '';
		$email     = isset( $profile['email'] ) ? sanitize_email( (string) $profile['email'] ) : '';
		$name      = isset( $profile['name'] ) ? sanitize_text_field( (string) $profile['name'] ) : '';
		$settings  = self::get_settings();

		if ( '' === $remote_id && '' === $email ) {
			return new WP_Error( 'tsolo_social_identity', __( 'Social login did not return a usable identity.', 'tso-login-organizer' ) );
		}

		// Logged-in user connecting a provider from profile.
		if ( is_user_logged_in() ) {
			$current = wp_get_current_user();
			$linked  = self::attach_identity( $current, $provider, $remote_id );
			if ( is_wp_error( $linked ) ) {
				return $linked;
			}
			return $current;
		}

		$user = null;
		if ( '' !== $remote_id ) {
			$user = self::find_user_by_identity( self::identity_key( $provider, $remote_id ) );
		}
		if ( ! $user && '' !== $email ) {
			$by_email = get_user_by( 'email', $email );
			if ( $by_email instanceof WP_User ) {
				if ( empty( $settings['link_existing'] ) ) {
					return new WP_Error(
						'tsolo_social_exists',
						__( 'An account with this email already exists. Log in with your password, then link this provider from your profile.', 'tso-login-organizer' )
					);
				}
				if ( array_key_exists( 'email_verified', $profile ) && empty( $profile['email_verified'] ) ) {
					return new WP_Error(
						'tsolo_social_unverified',
						__( 'The social provider did not verify this email address, so it cannot be linked to an existing account.', 'tso-login-organizer' )
					);
				}
				$user = $by_email;
			}
		}
		if ( ! $user ) {
			if ( ! get_option( 'users_can_register' ) ) {
				return new WP_Error(
					'tsolo_social_closed',
					__( 'Registration is currently closed on this site.', 'tso-login-organizer' )
				);
			}
			if ( '' === $email ) {
				$email = self::synthetic_email( $provider, $remote_id ? $remote_id : wp_generate_password( 8, false ) );
			}
			$login = sanitize_user( current( explode( '@', $email ) ), true );
			if ( '' === $login ) {
				$login = 'user' . wp_generate_password( 6, false );
			}
			if ( username_exists( $login ) ) {
				$login .= wp_generate_password( 4, false );
			}
			$user_id = wp_create_user( $login, wp_generate_password( 24, true, true ), $email );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
			if ( $name ) {
				wp_update_user(
					array(
						'ID'           => $user_id,
						'display_name' => $name,
					)
				);
			}
			$user = get_userdata( $user_id );
			if ( ! ( $user instanceof WP_User ) ) {
				return new WP_Error( 'tsolo_social_user', __( 'Could not create the social login account.', 'tso-login-organizer' ) );
			}
		}

		$attached = self::attach_identity( $user, $provider, $remote_id );
		if ( is_wp_error( $attached ) ) {
			return $attached;
		}
		return $user;
	}

	/**
	 * Whether moderation should block this user from social sign-in.
	 *
	 * @param WP_User $user User.
	 * @return string '' or checkemail query value.
	 */
	public static function moderation_block_flag( $user ) {
		if ( ! ( $user instanceof WP_User ) || ! class_exists( 'TSOLO_Mod_Moderation' ) || ! tsolo_is_module_enabled( 'moderation' ) ) {
			return '';
		}
		$status = (string) get_user_meta( $user->ID, 'tsolo_moderation_status', true );
		$key    = (string) get_user_meta( $user->ID, 'tsolo_email_confirm_key', true );
		$reason = TSOLO_Mod_Moderation::block_reason( $status, $key );
		if ( 'pending_email' === $reason || 'pending_admin' === $reason ) {
			return 'moderate';
		}
		return '';
	}

	/**
	 * PKCE challenge (S256) from verifier.
	 *
	 * @param string $verifier Verifier.
	 * @return string
	 */
	public static function pkce_challenge( $verifier ) {
		$hash = hash( 'sha256', (string) $verifier, true );
		return rtrim( strtr( base64_encode( $hash ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- PKCE S256 encoding.
	}

	/**
	 * Store PKCE verifier for a state token.
	 *
	 * @param string $state    State.
	 * @param string $verifier Verifier.
	 */
	public static function store_pkce_verifier( $state, $verifier ) {
		set_transient( 'tsolo_social_pkce_' . md5( (string) $state ), (string) $verifier, 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Consume PKCE verifier.
	 *
	 * @param string $state State.
	 * @return string
	 */
	public static function consume_pkce_verifier( $state ) {
		$key      = 'tsolo_social_pkce_' . md5( (string) $state );
		$verifier = get_transient( $key );
		delete_transient( $key );
		return is_string( $verifier ) ? $verifier : '';
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
		if ( self::provider_ready( 'google', $s ) ) {
			$links['google'] = $this->google_auth_url();
		}
		if ( self::provider_ready( 'facebook', $s ) ) {
			$links['facebook'] = $this->facebook_auth_url();
		}
		if ( self::provider_ready( 'x', $s ) ) {
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
				'redirect_uri'  => self::callback_url( 'google' ),
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
				'redirect_uri' => self::callback_url( 'facebook' ),
				'scope'        => 'email,public_profile',
				'state'        => wp_create_nonce( 'tsolo_social_facebook' ),
			),
			'https://www.facebook.com/v19.0/dialog/oauth'
		);
	}

	/**
	 * X auth URL (OAuth 2 + PKCE).
	 *
	 * @return string
	 */
	private function x_auth_url() {
		$s        = self::get_settings();
		$state    = wp_create_nonce( 'tsolo_social_x' );
		$verifier = wp_generate_password( 64, false, false );
		self::store_pkce_verifier( $state, $verifier );
		return add_query_arg(
			array(
				'response_type'         => 'code',
				'client_id'             => $s['x_client_id'],
				'redirect_uri'          => self::callback_url( 'x' ),
				'scope'                 => 'users.read tweet.read offline.access',
				'state'                 => $state,
				'code_challenge'        => self::pkce_challenge( $verifier ),
				'code_challenge_method' => 'S256',
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
		if ( ! self::provider_ready( $provider ) ) {
			wp_die( esc_html__( 'Social login is not configured for this provider.', 'tso-login-organizer' ), 403 );
		}
		$code  = tsolo_get_request_text( 'code', 'get' );
		$state = tsolo_get_request_text( 'state', 'get' );
		if ( '' === $code || ! wp_verify_nonce( $state, 'tsolo_social_' . $provider ) ) {
			wp_die( esc_html__( 'Invalid social login request.', 'tso-login-organizer' ), 403 );
		}

		if ( 'google' === $provider ) {
			$profile = $this->exchange_google( $code );
		} elseif ( 'facebook' === $provider ) {
			$profile = $this->exchange_facebook( $code );
		} else {
			$verifier = self::consume_pkce_verifier( $state );
			if ( '' === $verifier ) {
				wp_die( esc_html__( 'Invalid social login request.', 'tso-login-organizer' ), 403 );
			}
			$profile = $this->exchange_x( $code, $verifier );
		}

		$was_logged_in = is_user_logged_in();
		$user          = self::resolve_user( $provider, is_array( $profile ) ? $profile : array() );
		if ( is_wp_error( $user ) ) {
			wp_die( esc_html( $user->get_error_message() ) );
		}

		if ( $was_logged_in ) {
			$profile_url = function_exists( 'tsolo_get_action_url' ) ? tsolo_get_action_url( 'profile' ) : home_url( '/' );
			wp_safe_redirect( add_query_arg( 'tsolo_social_linked', $provider, $profile_url ) );
			exit;
		}

		$flag = self::moderation_block_flag( $user );
		if ( $flag ) {
			$login = function_exists( 'tsolo_get_action_url' ) ? tsolo_get_action_url( 'login' ) : wp_login_url();
			wp_safe_redirect( add_query_arg( 'checkemail', $flag, $login ) );
			exit;
		}

		if ( function_exists( 'tsolo_is_module_enabled' ) && tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
			if ( TSOLO_Mod_Two_Factor::user_needs_2fa( $user ) ) {
				wp_die(
					esc_html__( 'This account requires two-factor authentication. Please log in with your username and password, then enter the code from your authenticator app.', 'tso-login-organizer' ),
					esc_html__( 'Two-factor required', 'tso-login-organizer' ),
					array(
						'response'  => 403,
						'back_link' => true,
					)
				);
			}
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login_redirect filter.
		wp_safe_redirect( apply_filters( 'login_redirect', admin_url(), '', $user ) );
		exit;
	}

	/**
	 * Profile: connect / disconnect social providers.
	 */
	public function render_profile_links() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$s        = self::get_settings();
		$user_id  = get_current_user_id();
		$identity = (string) get_user_meta( $user_id, self::META_IDENTITY, true );
		$linked   = '';
		if ( $identity && false !== strpos( $identity, ':' ) ) {
			$linked = sanitize_key( strtok( $identity, ':' ) );
		}
		$providers = array();
		if ( self::provider_ready( 'google', $s ) ) {
			$providers['google'] = __( 'Google', 'tso-login-organizer' );
		}
		if ( self::provider_ready( 'facebook', $s ) ) {
			$providers['facebook'] = __( 'Facebook', 'tso-login-organizer' );
		}
		if ( self::provider_ready( 'x', $s ) ) {
			$providers['x'] = __( 'X', 'tso-login-organizer' );
		}
		if ( empty( $providers ) ) {
			return;
		}
		$linked_get = tsolo_get_request_text( 'tsolo_social_linked', 'get' );
		echo '<div class="tsolo-field tsolo-field-social">';
		echo '<p><strong>' . esc_html__( 'Social accounts', 'tso-login-organizer' ) . '</strong></p>';
		if ( $linked_get && isset( $providers[ $linked_get ] ) ) {
			echo '<p class="tsolo-notice tsolo-notice-success">' . esc_html__( 'Social account linked.', 'tso-login-organizer' ) . '</p>';
		}
		foreach ( $providers as $key => $label ) {
			if ( $linked === $key ) {
				$unlink = wp_nonce_url(
					add_query_arg(
						array(
							'tsolo_social_unlink' => $key,
						),
						function_exists( 'tsolo_get_action_url' ) ? tsolo_get_action_url( 'profile' ) : home_url( '/' )
					),
					'tsolo_social_unlink_' . $key,
					'tsolo_social_unlink_nonce'
				);
				echo '<p>' . esc_html(
					sprintf(
						/* translators: %s: provider name */
						__( 'Linked to %s.', 'tso-login-organizer' ),
						$label
					)
				) . ' <a href="' . esc_url( $unlink ) . '">' . esc_html__( 'Disconnect', 'tso-login-organizer' ) . '</a></p>';
			} else {
				$url = 'google' === $key ? $this->google_auth_url() : ( 'facebook' === $key ? $this->facebook_auth_url() : $this->x_auth_url() );
				echo '<p><a class="tsolo-button tsolo-social-' . esc_attr( $key ) . '" href="' . esc_url( $url ) . '">' . esc_html(
					sprintf(
						/* translators: %s: provider name */
						__( 'Connect %s', 'tso-login-organizer' ),
						$label
					)
				) . '</a></p>';
			}
		}
		echo '</div>';
	}

	/**
	 * Disconnect social identity from profile.
	 */
	public function handle_unlink() {
		$provider = tsolo_get_request_text( 'tsolo_social_unlink', 'get' );
		if ( ! in_array( $provider, array( 'google', 'facebook', 'x' ), true ) || ! is_user_logged_in() ) {
			return;
		}
		if ( ! function_exists( 'tsolo_is_action' ) || ! tsolo_is_action( 'profile' ) ) {
			return;
		}
		$nonce = tsolo_get_request_text( 'tsolo_social_unlink_nonce', 'get' );
		if ( ! wp_verify_nonce( $nonce, 'tsolo_social_unlink_' . $provider ) ) {
			return;
		}
		$user_id  = get_current_user_id();
		$identity = (string) get_user_meta( $user_id, self::META_IDENTITY, true );
		if ( 0 === strpos( $identity, $provider . ':' ) ) {
			delete_user_meta( $user_id, self::META_IDENTITY );
			delete_user_meta( $user_id, self::META_PROVIDER );
		}
		$profile_url = function_exists( 'tsolo_get_action_url' ) ? tsolo_get_action_url( 'profile' ) : home_url( '/' );
		wp_safe_redirect( $profile_url );
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
					'redirect_uri'  => self::callback_url( 'google' ),
					'grant_type'    => 'authorization_code',
				),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return array();
		}
		$token = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $token['access_token'] ) || ! is_array( $token ) ) {
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
		if ( ! is_array( $data ) ) {
			return array();
		}
		return array(
			'id'             => isset( $data['sub'] ) ? (string) $data['sub'] : ( isset( $data['id'] ) ? (string) $data['id'] : '' ),
			'email'          => isset( $data['email'] ) ? (string) $data['email'] : '',
			'name'           => isset( $data['name'] ) ? (string) $data['name'] : '',
			'email_verified' => ! empty( $data['email_verified'] ),
		);
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
					'redirect_uri'  => self::callback_url( 'facebook' ),
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
		if ( empty( $token['access_token'] ) || ! is_array( $token ) ) {
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
		if ( ! is_array( $data ) ) {
			return array();
		}
		return array(
			'id'    => isset( $data['id'] ) ? (string) $data['id'] : '',
			'email' => isset( $data['email'] ) ? (string) $data['email'] : '',
			'name'  => isset( $data['name'] ) ? (string) $data['name'] : '',
		);
	}

	/**
	 * Exchange X code with PKCE verifier.
	 *
	 * @param string $code     Code.
	 * @param string $verifier PKCE verifier.
	 * @return array
	 */
	private function exchange_x( $code, $verifier ) {
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
					'redirect_uri'  => self::callback_url( 'x' ),
					'code_verifier' => $verifier,
				),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return array();
		}
		$token = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $token['access_token'] ) || ! is_array( $token ) ) {
			return array();
		}
		$info = wp_remote_get(
			'https://api.twitter.com/2/users/me?user.fields=name,username',
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ),
			)
		);
		if ( is_wp_error( $info ) ) {
			return array();
		}
		$body = json_decode( wp_remote_retrieve_body( $info ), true );
		if ( empty( $body['data'] ) || ! is_array( $body['data'] ) ) {
			return array();
		}
		$data = $body['data'];
		$name = '';
		if ( ! empty( $data['name'] ) ) {
			$name = (string) $data['name'];
		} elseif ( ! empty( $data['username'] ) ) {
			$name = (string) $data['username'];
		}
		return array(
			'id'    => isset( $data['id'] ) ? (string) $data['id'] : '',
			'email' => '',
			'name'  => $name,
		);
	}
}
