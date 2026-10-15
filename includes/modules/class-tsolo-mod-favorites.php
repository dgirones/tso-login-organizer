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
	 * User meta: favorite post IDs.
	 *
	 * @var string
	 */
	const META_IDS = 'tsolo_favorites';

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
	 * Sanitize settings.
	 *
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array(
			'show_on_posts' => empty( $raw['show_on_posts'] ) ? 0 : 1,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_FAVORITES, self::defaults() ) );
	}

	/**
	 * Normalize a list of post IDs (unique positive ints).
	 *
	 * @param mixed $ids Raw IDs.
	 * @return int[]
	 */
	public static function sanitize_ids( $ids ) {
		if ( ! is_array( $ids ) ) {
			return array();
		}
		$out = array();
		foreach ( $ids as $id ) {
			$id = (int) $id;
			// Do not use absint(): negatives would become positive IDs.
			if ( $id > 0 ) {
				$out[] = $id;
			}
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Get user favorites.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public static function get_favorites( $user_id ) {
		$user_id = absint( $user_id );
		if ( $user_id < 1 ) {
			return array();
		}
		return self::sanitize_ids( get_user_meta( $user_id, self::META_IDS, true ) );
	}

	/**
	 * Persist favorites for a user.
	 *
	 * @param int   $user_id User ID.
	 * @param int[] $ids     Post IDs.
	 * @return int[] Stored list.
	 */
	public static function set_favorites( $user_id, $ids ) {
		$user_id = absint( $user_id );
		$ids     = self::sanitize_ids( $ids );
		if ( $user_id < 1 ) {
			return array();
		}
		update_user_meta( $user_id, self::META_IDS, $ids );
		return $ids;
	}

	/**
	 * Whether a post is in the user's favorites.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_favorite( $user_id, $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id < 1 ) {
			return false;
		}
		return in_array( $post_id, self::get_favorites( $user_id ), true );
	}

	/**
	 * Whether a post can be favorited / listed for the current user.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_favoriteable_post( $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id < 1 ) {
			return false;
		}
		$post = get_post( $post_id );
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}
		/**
		 * Filter allowed post types for favorites (default: post).
		 *
		 * @param string[] $types Post types.
		 */
		$types = apply_filters( 'tsolo_favorites_post_types', array( 'post' ) );
		if ( ! is_array( $types ) || ! in_array( $post->post_type, $types, true ) ) {
			return false;
		}
		if ( 'publish' !== $post->post_status ) {
			return false;
		}
		if ( ! is_user_logged_in() || ! current_user_can( 'read_post', $post_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Toggle a post in the user's favorites.
	 *
	 * Removing always works if the ID is already saved (e.g. post later unpublished).
	 * Adding requires a favoriteable published post the user can read.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return array{active:bool,ids:int[]}|WP_Error
	 */
	public static function toggle( $user_id, $post_id ) {
		$user_id = absint( $user_id );
		$post_id = absint( $post_id );
		if ( $user_id < 1 || $post_id < 1 ) {
			return new WP_Error( 'tsolo_favorite_invalid', __( 'This post cannot be added to favorites.', 'tso-login-organizer' ) );
		}
		$favs = self::get_favorites( $user_id );
		if ( in_array( $post_id, $favs, true ) ) {
			$favs   = array_values( array_diff( $favs, array( $post_id ) ) );
			$active = false;
		} else {
			if ( ! self::is_favoriteable_post( $post_id ) ) {
				return new WP_Error( 'tsolo_favorite_invalid', __( 'This post cannot be added to favorites.', 'tso-login-organizer' ) );
			}
			$favs[] = $post_id;
			$active = true;
		}
		$favs = self::set_favorites( $user_id, $favs );
		return array(
			'active' => $active,
			'ids'    => $favs,
		);
	}

	/**
	 * Label for the toggle button.
	 *
	 * @param bool $active Whether favorited.
	 * @return string
	 */
	public static function button_label( $active ) {
		return $active ? __( 'Remove from favorites', 'tso-login-organizer' ) : __( 'Add to favorites', 'tso-login-organizer' );
	}

	/**
	 * HTML for a toggle button.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $active  Active.
	 * @return string
	 */
	public static function render_toggle_button( $post_id, $active ) {
		$post_id = absint( $post_id );
		$label   = self::button_label( (bool) $active );
		return '<button type="button" class="tsolo-button tsolo-favorite-toggle" data-post="' . esc_attr( (string) $post_id ) . '" aria-pressed="' . ( $active ? 'true' : 'false' ) . '">' . esc_html( $label ) . '</button>';
	}

	/**
	 * Append favorite button on single posts.
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
		if ( ! self::is_favoriteable_post( $post_id ) ) {
			return $content;
		}
		$active = self::is_favorite( get_current_user_id(), $post_id );
		$btn    = '<p class="tsolo-favorite-wrap">' . self::render_toggle_button( $post_id, $active ) . '</p>';
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
		$result  = self::toggle( get_current_user_id(), $post_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'active' => ! empty( $result['active'] ),
				'label'  => self::button_label( ! empty( $result['active'] ) ),
			)
		);
	}
}
