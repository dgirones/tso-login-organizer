<?php
/**
 * Custom avatar uploads module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Avatars
 */
class TSOLO_Mod_Avatars {

	/**
	 * Instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Uploads override for wp_handle_upload.
	 *
	 * @var array|null
	 */
	private $upload_override = null;

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
		add_filter( 'get_avatar_url', array( $this, 'filter_avatar_url' ), 10, 3 );
		// After parse_request so tsolo_is_action( 'profile' ) works with pretty permalinks.
		add_action( 'template_redirect', array( $this, 'handle_upload' ), 1 );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'max_kb' => 512,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_AVATARS, self::defaults() );
	}

	/**
	 * Filter avatar URL.
	 *
	 * @param string $url         URL.
	 * @param mixed  $id_or_email ID or email.
	 * @param array  $args        Args.
	 * @return string
	 */
	public function filter_avatar_url( $url, $id_or_email, $args ) {
		unset( $args );
		$user_id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
			$user_id = (int) $id_or_email->user_id;
		} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user    = get_user_by( 'email', $id_or_email );
			$user_id = $user ? (int) $user->ID : 0;
		}
		if ( ! $user_id ) {
			return $url;
		}
		$custom = get_user_meta( $user_id, 'tsolo_avatar_url', true );
		return $custom ? esc_url( $custom ) : $url;
	}

	/**
	 * Filter upload_dir for avatar files only.
	 *
	 * @param array $dirs Dirs.
	 * @return array
	 */
	public function filter_upload_dir( $dirs ) {
		if ( ! is_array( $this->upload_override ) ) {
			return $dirs;
		}
		$dirs['path']   = $this->upload_override['basedir'];
		$dirs['url']    = $this->upload_override['baseurl'];
		$dirs['subdir'] = '';
		return $dirs;
	}

	/**
	 * Handle avatar upload on profile action.
	 */
	public function handle_upload() {
		if ( ! is_user_logged_in() || ! tsolo_is_action( 'profile' ) ) {
			return;
		}
		if ( empty( $_FILES['tsolo_avatar']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
			return;
		}
		$nonce = tsolo_get_request_text( 'tsolo_avatar_nonce', 'post' );
		if ( ! wp_verify_nonce( $nonce, 'tsolo_upload_avatar' ) ) {
			return;
		}
		$uploads = tsolo_get_uploads_dir();
		if ( is_wp_error( $uploads ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$s    = self::get_settings();
		$max  = max( 50, absint( $s['max_kb'] ) ) * 1024;
		$file = $_FILES['tsolo_avatar']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Handled by wp_handle_upload after nonce verify.
		if ( ! empty( $file['size'] ) && (int) $file['size'] > $max ) {
			tsolo_set_errors( new WP_Error( 'tsolo_avatar_size', __( 'Avatar file is too large.', 'tso-login-organizer' ) ) );
			return;
		}
		$overrides             = array(
			'test_form' => false,
			'mimes'     => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
				'webp'         => 'image/webp',
			),
		);
		$this->upload_override = $uploads;
		add_filter( 'upload_dir', array( $this, 'filter_upload_dir' ) );
		$moved = wp_handle_upload( $file, $overrides );
		remove_filter( 'upload_dir', array( $this, 'filter_upload_dir' ) );
		$this->upload_override = null;
		if ( isset( $moved['error'] ) ) {
			tsolo_set_errors( new WP_Error( 'tsolo_avatar', $moved['error'] ) );
			return;
		}
		update_user_meta( get_current_user_id(), 'tsolo_avatar_url', esc_url_raw( $moved['url'] ) );
		tsolo_set_errors( new WP_Error( 'tsolo_avatar_ok', __( 'Avatar updated.', 'tso-login-organizer' ), 'message' ) );
	}
}
