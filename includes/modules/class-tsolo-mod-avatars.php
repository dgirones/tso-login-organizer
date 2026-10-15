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
	 * User meta: custom avatar URL.
	 *
	 * @var string
	 */
	const META_URL = 'tsolo_avatar_url';

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
		// Priority 2 so Profiles (1) can save first; tsolo_set_errors merges both messages.
		add_action( 'template_redirect', array( $this, 'handle_upload' ), 2 );
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
	 * Sanitize settings.
	 *
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		$max = isset( $raw['max_kb'] ) ? absint( $raw['max_kb'] ) : 512;
		return array(
			'max_kb' => max( 50, min( 8192, $max ) ),
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_AVATARS, self::defaults() ) );
	}

	/**
	 * Resolve a user ID from get_avatar_url $id_or_email.
	 *
	 * @param mixed $id_or_email ID, email, WP_User, or comment-like object.
	 * @return int
	 */
	public static function resolve_user_id( $id_or_email ) {
		if ( is_numeric( $id_or_email ) ) {
			return absint( $id_or_email );
		}
		if ( $id_or_email instanceof WP_User ) {
			return absint( $id_or_email->ID );
		}
		if ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				return absint( $id_or_email->user_id );
			}
			if ( ! empty( $id_or_email->ID ) && empty( $id_or_email->comment_ID ) && empty( $id_or_email->comment_id ) ) {
				return absint( $id_or_email->ID );
			}
		}
		if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			return $user ? absint( $user->ID ) : 0;
		}
		return 0;
	}

	/**
	 * Max upload size in bytes.
	 *
	 * @param array|null $settings Settings.
	 * @return int
	 */
	public static function max_bytes( $settings = null ) {
		$s = is_array( $settings ) ? self::sanitize_settings( $settings ) : self::get_settings();
		return max( 50, absint( $s['max_kb'] ) ) * 1024;
	}

	/**
	 * Whether a path is inside the plugin uploads tree.
	 *
	 * @param string $path Absolute path.
	 * @param string $basedir Uploads basedir.
	 * @return bool
	 */
	public static function path_is_in_uploads( $path, $basedir ) {
		$path    = wp_normalize_path( (string) $path );
		$basedir = trailingslashit( wp_normalize_path( (string) $basedir ) );
		if ( '' === $path || '' === $basedir ) {
			return false;
		}
		$real_path = realpath( $path );
		$real_base = realpath( $basedir );
		if ( false === $real_path || false === $real_base ) {
			return ( 0 === strpos( $path, $basedir ) );
		}
		$real_path = wp_normalize_path( $real_path );
		$real_base = trailingslashit( wp_normalize_path( $real_base ) );
		return ( 0 === strpos( $real_path, $real_base ) );
	}

	/**
	 * Delete a previous avatar file if it lives under our uploads folder.
	 *
	 * @param string $url     Old URL.
	 * @param array  $uploads Uploads dir from tsolo_get_uploads_dir().
	 * @return bool True when a file was removed.
	 */
	public static function delete_avatar_file( $url, $uploads ) {
		$url = (string) $url;
		if ( '' === $url || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
			return false;
		}
		$baseurl = untrailingslashit( (string) $uploads['baseurl'] );
		$basedir = untrailingslashit( (string) $uploads['basedir'] );
		if ( 0 !== strpos( $url, $baseurl ) ) {
			return false;
		}
		$rel  = ltrim( substr( $url, strlen( $baseurl ) ), '/' );
		$path = $basedir . '/' . str_replace( '..', '', $rel );
		if ( ! self::path_is_in_uploads( $path, $basedir ) || ! is_file( $path ) ) {
			return false;
		}
		return (bool) unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Removing prior avatar under plugin uploads only.
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
		$user_id = self::resolve_user_id( $id_or_email );
		if ( ! $user_id ) {
			return $url;
		}
		$custom = get_user_meta( $user_id, self::META_URL, true );
		if ( ! is_string( $custom ) || '' === $custom ) {
			return $url;
		}
		// Return raw URL; core escapes on output. esc_url() here can break &amp; in filters.
		return esc_url_raw( $custom );
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
			tsolo_set_errors( new WP_Error( 'tsolo_avatar_nonce', __( 'Avatar upload could not be verified. Please try again.', 'tso-login-organizer' ) ) );
			return;
		}
		$uploads = tsolo_get_uploads_dir();
		if ( is_wp_error( $uploads ) ) {
			tsolo_set_errors( $uploads );
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$file = $_FILES['tsolo_avatar']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Handled by wp_handle_upload after nonce verify.
		if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			tsolo_set_errors( new WP_Error( 'tsolo_avatar_upload', __( 'Avatar upload failed. Please try again.', 'tso-login-organizer' ) ) );
			return;
		}
		$max = self::max_bytes();
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
		$user_id = get_current_user_id();
		$old     = get_user_meta( $user_id, self::META_URL, true );
		if ( is_string( $old ) && $old && isset( $moved['url'] ) && $old !== $moved['url'] ) {
			self::delete_avatar_file( $old, $uploads );
		}
		update_user_meta( $user_id, self::META_URL, esc_url_raw( $moved['url'] ) );
		tsolo_set_errors( new WP_Error( 'tsolo_avatar_ok', __( 'Avatar updated.', 'tso-login-organizer' ), 'message' ) );
	}
}
