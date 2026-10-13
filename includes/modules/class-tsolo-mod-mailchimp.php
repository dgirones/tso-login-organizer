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
		if ( empty( $s['api_key'] ) || empty( $s['list_id'] ) ) {
			return;
		}
		add_action( 'register_form', array( $this, 'render_checkbox' ) );
		add_action( 'user_register', array( $this, 'subscribe' ) );
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
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$s = tsolo_get_option_array( TSOLO_OPT_MAILCHIMP, self::defaults() );
		if ( '' === $s['label'] ) {
			$s['label'] = __( 'Subscribe to our newsletter', 'tso-login-organizer' );
		}
		return $s;
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
	 * Subscribe user after register.
	 *
	 * @param int $user_id User ID.
	 */
	public function subscribe( $user_id ) {
		$s = self::get_settings();
		if ( ! empty( $s['checkbox'] ) && ! tsolo_request_post_is_set( 'tsolo_mailchimp' ) ) {
			return;
		}
		$user = get_userdata( $user_id );
		if ( ! $user || ! $user->user_email ) {
			return;
		}
		$parts      = explode( '-', $s['api_key'] );
		$datacenter = ( count( $parts ) > 1 ) ? end( $parts ) : 'us1';
		$member     = md5( strtolower( $user->user_email ) );
		$url        = sprintf( 'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s', rawurlencode( $datacenter ), rawurlencode( $s['list_id'] ), $member );
		wp_remote_request(
			$url,
			array(
				'method'  => 'PUT',
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'user:' . $s['api_key'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Mailchimp HTTP basic auth.
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'email_address' => $user->user_email,
						'status_if_new' => 'subscribed',
						'status'        => 'subscribed',
						'merge_fields'  => array(
							'FNAME' => $user->display_name,
						),
					)
				),
			)
		);
	}
}
