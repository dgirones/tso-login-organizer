<?php
/**
 * Mailchimp registration opt-in module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Mailchimp
 */
class TSOLO_Mod_Mailchimp {

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
		if ( ! self::credentials_ready( $s ) ) {
			return;
		}
		add_action( 'register_form', array( $this, 'render_checkbox' ) );
		add_action( 'user_register', array( $this, 'subscribe' ), 20 );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'api_key'       => '',
			'list_id'       => '',
			'checkbox'      => 1,
			'default_optin' => 0,
			'label'         => '',
		);
	}

	/**
	 * Settings with defaults merged.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$s = tsolo_get_option_array( TSOLO_OPT_MAILCHIMP, self::defaults() );
		if ( '' === trim( (string) $s['label'] ) ) {
			$s['label'] = __( 'Subscribe to our newsletter', 'tso-login-organizer' );
		}
		$s['checkbox']      = empty( $s['checkbox'] ) ? 0 : 1;
		$s['default_optin'] = empty( $s['default_optin'] ) ? 0 : 1;
		$s['api_key']       = is_string( $s['api_key'] ) ? trim( $s['api_key'] ) : '';
		$s['list_id']       = is_string( $s['list_id'] ) ? trim( $s['list_id'] ) : '';
		return $s;
	}

	/**
	 * Sanitize settings before storage.
	 *
	 * @param array $raw Raw posted values.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		$api_key = isset( $raw['api_key'] ) ? trim( (string) $raw['api_key'] ) : '';
		$api_key = preg_replace( '/\s+/', '', $api_key );
		$list_id = isset( $raw['list_id'] ) ? trim( (string) $raw['list_id'] ) : '';
		$list_id = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $list_id );
		$label   = isset( $raw['label'] ) ? sanitize_text_field( (string) $raw['label'] ) : '';
		return array(
			'api_key'       => is_string( $api_key ) ? $api_key : '',
			'list_id'       => is_string( $list_id ) ? $list_id : '',
			'checkbox'      => empty( $raw['checkbox'] ) ? 0 : 1,
			'default_optin' => empty( $raw['default_optin'] ) ? 0 : 1,
			'label'         => $label,
		);
	}

	/**
	 * Whether API key + list ID look usable.
	 *
	 * @param array|null $settings Settings or null to load.
	 * @return bool
	 */
	public static function credentials_ready( $settings = null ) {
		$s = is_array( $settings ) ? $settings : self::get_settings();
		return ( '' !== $s['api_key'] && '' !== $s['list_id'] && null !== self::parse_datacenter( $s['api_key'] ) );
	}

	/**
	 * Data center suffix from a Mailchimp API key (…-us21).
	 *
	 * @param string $api_key API key.
	 * @return string|null
	 */
	public static function parse_datacenter( $api_key ) {
		$api_key = trim( (string) $api_key );
		if ( '' === $api_key || false === strpos( $api_key, '-' ) ) {
			return null;
		}
		$parts = explode( '-', $api_key );
		$dc    = strtolower( (string) end( $parts ) );
		if ( ! preg_match( '/^[a-z]{2,4}\d{0,3}$/', $dc ) ) {
			return null;
		}
		return $dc;
	}

	/**
	 * Whether this registration should be subscribed.
	 *
	 * @param array $settings Settings.
	 * @param bool  $opted_in Whether POST opt-in was present.
	 * @return bool
	 */
	public static function should_subscribe( $settings, $opted_in ) {
		if ( ! self::credentials_ready( $settings ) ) {
			return false;
		}
		if ( ! empty( $settings['checkbox'] ) && ! $opted_in ) {
			return false;
		}
		return true;
	}

	/**
	 * Build Mailchimp member PUT payload.
	 *
	 * @param string $email Email.
	 * @param string $fname First / display name.
	 * @return array
	 */
	public static function build_member_payload( $email, $fname = '' ) {
		$payload = array(
			'email_address' => $email,
			'status_if_new' => 'subscribed',
		);
		$fname   = trim( (string) $fname );
		if ( '' !== $fname ) {
			$payload['merge_fields'] = array(
				'FNAME' => $fname,
			);
		}
		return $payload;
	}

	/**
	 * Member hash for Mailchimp API path.
	 *
	 * @param string $email Email.
	 * @return string
	 */
	public static function member_hash( $email ) {
		return md5( strtolower( trim( (string) $email ) ) );
	}

	/**
	 * Build list member URL.
	 *
	 * @param string $datacenter DC.
	 * @param string $list_id    List ID.
	 * @param string $email      Email.
	 * @return string
	 */
	public static function member_url( $datacenter, $list_id, $email ) {
		return sprintf(
			'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s',
			rawurlencode( (string) $datacenter ),
			rawurlencode( (string) $list_id ),
			self::member_hash( $email )
		);
	}

	/**
	 * Checkbox on register form.
	 */
	public function render_checkbox() {
		$s = self::get_settings();
		if ( empty( $s['checkbox'] ) ) {
			return;
		}
		echo '<p class="tsolo-field tsolo-mailchimp"><label><input type="checkbox" name="tsolo_mailchimp" value="1" ' . checked( ! empty( $s['default_optin'] ), true, false ) . ' /> ' . esc_html( $s['label'] ) . '</label></p>';
	}

	/**
	 * Subscribe user after register (never blocks registration on API failure).
	 *
	 * @param int $user_id User ID.
	 */
	public function subscribe( $user_id ) {
		$user_id = absint( $user_id );
		if ( $user_id < 1 ) {
			return;
		}
		$s = self::get_settings();
		if ( ! self::should_subscribe( $s, tsolo_request_post_is_set( 'tsolo_mailchimp' ) ) ) {
			return;
		}
		$user = get_userdata( $user_id );
		if ( ! $user || empty( $user->user_email ) || ! is_email( $user->user_email ) ) {
			return;
		}
		$fname = '';
		if ( ! empty( $user->first_name ) ) {
			$fname = (string) $user->first_name;
		} elseif ( ! empty( $user->display_name ) ) {
			$fname = (string) $user->display_name;
		}
		self::put_member( $s, $user->user_email, $fname );
	}

	/**
	 * PUT member to Mailchimp. Returns true on HTTP 2xx, false otherwise.
	 *
	 * @param array  $settings Settings.
	 * @param string $email    Email.
	 * @param string $fname    Optional first name.
	 * @return bool
	 */
	public static function put_member( $settings, $email, $fname = '' ) {
		if ( ! self::credentials_ready( $settings ) ) {
			return false;
		}
		$email = sanitize_email( $email );
		if ( '' === $email || ! is_email( $email ) ) {
			return false;
		}
		$dc       = self::parse_datacenter( $settings['api_key'] );
		$url      = self::member_url( $dc, $settings['list_id'], $email );
		$args     = array(
			'method'  => 'PUT',
			'timeout' => 12,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $settings['api_key'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Mailchimp HTTP basic auth.
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( self::build_member_payload( $email, $fname ) ),
		);
		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		return ( $code >= 200 && $code < 300 );
	}
}
