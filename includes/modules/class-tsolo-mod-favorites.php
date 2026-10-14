<?php
/**
 * Favorites module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Favorites
 */
class TSOLO_Mod_Favorites {

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
		add_action( 'wp_ajax_tsolo_toggle_favorite', array( $this, 'ajax_toggle' ) );
		add_filter( 'the_content', array( $this, 'append_button' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'show_on_posts' => 1,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return tsolo_get_option_array( TSOLO_OPT_FAVORITES, self::defaults() );
	}

	/**
	 * Get user favorites.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public static function get_favorites( $user_id ) {
		$ids = get_user_meta( $user_id, 'tsolo_favorites', true );
		if ( ! is_array( $ids ) ) {
			return array();
		}
		return array_map( 'absint', $ids );
	}

	/**
	 * Enqueue front script when needed.
	 */
	public function enqueue() {
		if ( ! is_user_logged_in() || ! is_singular( 'post' ) ) {
			return;
		}
		wp_enqueue_script(
			'tsolo-front',
			TSOLO_URL . 'assets/js/tsolo-front.js',
			array( 'jquery' ),
			TSOLO_VERSION,
			true
		);
		wp_localize_script( 'tsolo-front', 'tsoloFrontConfig', tsolo_get_front_script_config() );
	}

	/**
	 * Append favorite button.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function append_button( $content ) {
		$s = self::get_settings();
		if ( empty( $s['show_on_posts'] ) || ! is_singular( 'post' ) || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}
		if ( ! is_user_logged_in() ) {
			return $content;
		}
		$post_id = get_the_ID();
		$favs    = self::get_favorites( get_current_user_id() );
		$active  = in_array( $post_id, $favs, true );
		$label   = $active ? __( 'Remove from favorites', 'tso-login-organizer' ) : __( 'Add to favorites', 'tso-login-organizer' );
		$btn     = '<p class="tsolo-favorite-wrap"><button type="button" class="tsolo-button tsolo-favorite-toggle" data-post="' . esc_attr( $post_id ) . '">' . esc_html( $label ) . '</button></p>';
		return $content . $btn;
	}

	/**
	 * AJAX toggle favorite.
	 */
	public function ajax_toggle() {
		if ( ! tsolo_verify_ajax_nonce() || ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}
		$post_id = absint( tsolo_get_request_text( 'post_id', 'post' ) );
		if ( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'invalid' ) );
		}
		$user_id = get_current_user_id();
		$favs    = self::get_favorites( $user_id );
		if ( in_array( $post_id, $favs, true ) ) {
			$favs   = array_values( array_diff( $favs, array( $post_id ) ) );
			$active = false;
		} else {
			$favs[] = $post_id;
			$active = true;
		}
		update_user_meta( $user_id, 'tsolo_favorites', $favs );
		wp_send_json_success(
			array(
				'active' => $active,
				'label'  => $active ? __( 'Remove from favorites', 'tso-login-organizer' ) : __( 'Add to favorites', 'tso-login-organizer' ),
			)
		);
	}
}
