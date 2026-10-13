<?php
/**
 * Action object class.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Action object.
 */
class TSOLO_Action {

	/**
	 * Action name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Slug.
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Callback.
	 *
	 * @var callable|string
	 */
	protected $callback = '';

	/**
	 * AJAX callback.
	 *
	 * @var callable|string
	 */
	protected $ajax_callback = '';

	/**
	 * Show link on forms.
	 *
	 * @var bool|string
	 */
	public $show_on_forms = true;

	/**
	 * Show in widget.
	 *
	 * @var bool
	 */
	public $show_in_widget = true;

	/**
	 * Show in nav menus.
	 *
	 * @var bool
	 */
	public $show_in_nav_menus = true;

	/**
	 * Show nav menu item.
	 *
	 * @var bool
	 */
	public $show_nav_menu_item = true;

	/**
	 * Show in slug settings.
	 *
	 * @var bool
	 */
	public $show_in_slug_settings = true;

	/**
	 * Constructor.
	 *
	 * @param string $name Name.
	 * @param array  $args Args.
	 */
	public function __construct( $name, $args = array() ) {
		$this->name = sanitize_key( $name );
		$args       = wp_parse_args(
			$args,
			array(
				'title'                 => '',
				'slug'                  => $this->name,
				'callback'              => '',
				'ajax_callback'         => '',
				'show_on_forms'         => true,
				'show_in_widget'        => true,
				'show_in_nav_menus'     => true,
				'show_in_slug_settings' => true,
			)
		);
		if ( ! isset( $args['show_nav_menu_item'] ) ) {
			$args['show_nav_menu_item'] = $args['show_in_nav_menus'];
		}
		$this->title                 = $args['title'];
		$this->slug                  = sanitize_title( $args['slug'] );
		$this->callback              = $args['callback'];
		$this->ajax_callback         = $args['ajax_callback'];
		$this->show_on_forms         = $args['show_on_forms'];
		$this->show_in_widget        = (bool) $args['show_in_widget'];
		$this->show_in_nav_menus     = (bool) $args['show_in_nav_menus'];
		$this->show_nav_menu_item    = (bool) $args['show_nav_menu_item'];
		$this->show_in_slug_settings = (bool) $args['show_in_slug_settings'];
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'tsolo_get_action_title', $this->title, $this->name );
	}

	/**
	 * Set title.
	 *
	 * @param string $title Title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Set slug.
	 *
	 * @param string $slug Slug.
	 */
	public function set_slug( $slug ) {
		$this->slug = sanitize_title( $slug );
	}

	/**
	 * Get URL.
	 *
	 * @param string $scheme Scheme.
	 * @return string
	 */
	public function get_url( $scheme = 'login' ) {
		if ( tsolo_use_permalinks() ) {
			$url = home_url( user_trailingslashit( $this->get_slug() ), $scheme );
		} else {
			$url = home_url( '/', $scheme );
			$url = add_query_arg( 'action', $this->get_name(), $url );
		}
		return apply_filters( 'tsolo_get_action_url', $url, $this->get_name(), $scheme );
	}

	/**
	 * Hook callbacks.
	 */
	public function add_callback_hook() {
		if ( $this->callback ) {
			add_action( 'tsolo_action_' . $this->get_name(), $this->callback, 15 );
		}
		if ( $this->ajax_callback ) {
			add_action( 'tsolo_action_ajax_' . $this->get_name(), $this->ajax_callback, 15 );
		}
	}
}
