<?php
/**
 * Navigation menu integration.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add meta box for TSOLO actions in nav menus.
 */
function tsolo_admin_nav_menu_meta_box() {
	add_meta_box(
		'tsolo-actions',
		__( 'TSO Login Organizer', 'tso-login-organizer' ),
		'tsolo_nav_menu_meta_box',
		'nav-menus',
		'side',
		'default'
	);
}

/**
 * Meta box content.
 */
function tsolo_nav_menu_meta_box() {
	global $nav_menu_selected_id;
	$actions = array();
	foreach ( tsolo_get_actions() as $action ) {
		if ( ! $action->show_in_nav_menus ) {
			continue;
		}
		$actions[] = (object) array(
			'ID'               => $action->get_name(),
			'object_id'        => $action->get_name(),
			'type_label'       => __( 'TSOLO Action', 'tso-login-organizer' ),
			'title'            => $action->get_title(),
			'url'              => $action->get_url(),
			'type'             => 'tsolo_action',
			'object'           => 'tsolo_action',
			'db_id'            => 0,
			'menu_item_parent' => 0,
			'post_parent'      => 0,
			'target'           => '',
			'attr_title'       => '',
			'description'      => '',
			'classes'          => array(),
			'xfn'              => '',
		);
	}
	$walker = new Walker_Nav_Menu_Checklist( array() );
	?>
	<div id="tsolo-actions" class="posttypediv">
		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $actions ), 0, (object) array( 'walker' => $walker ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core walker output. ?>
			</ul>
		</div>
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'tso-login-organizer' ); ?>" name="add-tsolo-menu-item" id="submit-tsolo-actions" />
				<span class="spinner"></span>
			</span>
		</p>
	</div>
	<?php
	unset( $nav_menu_selected_id );
}

/**
 * Resolve a TSOLO action from a menu item URL path.
 *
 * @param string $url Menu item URL.
 * @return TSOLO_Action|false
 */
function tsolo_action_from_menu_url( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		return false;
	}
	$path = untrailingslashit( $path );
	foreach ( tsolo_get_actions() as $action ) {
		$action_path = wp_parse_url( $action->get_url(), PHP_URL_PATH );
		if ( ! is_string( $action_path ) || '' === $action_path ) {
			continue;
		}
		if ( untrailingslashit( $action_path ) === $path ) {
			return $action;
		}
	}
	return false;
}

/**
 * Apply show/hide + logout nonce for a matched action on a menu item.
 *
 * @param object       $menu_item Menu item.
 * @param TSOLO_Action $action    Action.
 * @return object
 */
function tsolo_apply_action_to_menu_item( $menu_item, $action ) {
	$menu_item->url = $action->get_url();
	if ( 'logout' === $action->get_name() ) {
		$menu_item->url = wp_nonce_url( $menu_item->url, 'log-out' );
	}
	if ( ! is_admin() && ! $action->show_nav_menu_item ) {
		$menu_item->_invalid = true;
	}
	return $menu_item;
}

/**
 * Setup nav menu item for TSOLO actions.
 *
 * @param object $menu_item Menu item.
 * @return object
 */
function tsolo_setup_nav_menu_item( $menu_item ) {
	if ( isset( $menu_item->type ) && 'tsolo_action' === $menu_item->type ) {
		$action = tsolo_get_action( $menu_item->object_id ? $menu_item->object_id : $menu_item->object );
		if ( $action ) {
			$menu_item->type_label = __( 'TSOLO Action', 'tso-login-organizer' );
			$menu_item             = tsolo_apply_action_to_menu_item( $menu_item, $action );
		} else {
			$menu_item->_invalid = true;
		}
	}
	// TML compatibility: treat theme-my-login action items if present.
	if ( isset( $menu_item->type ) && 'tml_action' === $menu_item->type ) {
		$action = tsolo_get_action( $menu_item->object );
		if ( $action ) {
			$menu_item = tsolo_apply_action_to_menu_item( $menu_item, $action );
		}
	}
	// Legacy TML pages kept as Page menu items.
	if ( isset( $menu_item->object ) && 'page' === $menu_item->object && ! empty( $menu_item->object_id ) ) {
		$slug   = get_post_field( 'post_name', $menu_item->object_id );
		$action = $slug ? tsolo_get_action( $slug ) : false;
		if ( ! $action && $slug ) {
			foreach ( tsolo_get_actions() as $candidate ) {
				if ( $candidate->get_slug() === $slug ) {
					$action = $candidate;
					break;
				}
			}
		}
		if ( $action ) {
			$menu_item = tsolo_apply_action_to_menu_item( $menu_item, $action );
		}
	}
	// Custom links pointing at a TSOLO action URL (e.g. menu label "LOGIN" → /login/).
	if ( isset( $menu_item->type ) && 'custom' === $menu_item->type && ! empty( $menu_item->url ) ) {
		$action = tsolo_action_from_menu_url( $menu_item->url );
		if ( $action ) {
			$menu_item = tsolo_apply_action_to_menu_item( $menu_item, $action );
		}
	}
	return $menu_item;
}

/**
 * Drop invalid TSOLO-mapped items on the front (do not rely on CSS alone).
 *
 * @param array $items Menu items.
 * @return array
 */
function tsolo_filter_nav_menu_objects( $items ) {
	if ( is_admin() ) {
		return $items;
	}
	$out = array();
	foreach ( (array) $items as $item ) {
		if ( ! empty( $item->_invalid ) ) {
			continue;
		}
		$out[] = $item;
	}
	return $out;
}

/**
 * Hide invalid TSOLO items from front (CSS fallback).
 *
 * @param array  $classes Classes.
 * @param object $item    Item.
 * @return array
 */
function tsolo_nav_menu_css_class( $classes, $item ) {
	if ( ! empty( $item->_invalid ) ) {
		$classes[] = 'tsolo-hide-menu-item';
	}
	return $classes;
}

/**
 * Customizer item types.
 *
 * @param array $types Types.
 * @return array
 */
function tsolo_filter_customize_nav_menu_available_item_types( $types ) {
	$types[] = array(
		'title'      => __( 'TSO Login Organizer', 'tso-login-organizer' ),
		'type'       => 'tsolo_action',
		'object'     => 'tsolo_action',
		'type_label' => __( 'TSOLO Action', 'tso-login-organizer' ),
	);
	return $types;
}

/**
 * Customizer items.
 *
 * @param array  $items       Items.
 * @param string $type        Type.
 * @param string $object_type Object type.
 * @param int    $page        Page.
 * @return array
 */
function tsolo_filter_customize_nav_menu_available_items( $items, $type, $object_type, $page ) {
	unset( $object_type, $page );
	if ( 'tsolo_action' !== $type ) {
		return $items;
	}
	foreach ( tsolo_get_actions() as $action ) {
		if ( ! $action->show_in_nav_menus ) {
			continue;
		}
		$items[] = array(
			'id'         => 'tsolo_action-' . $action->get_name(),
			'title'      => $action->get_title(),
			'type'       => 'tsolo_action',
			'type_label' => __( 'TSOLO Action', 'tso-login-organizer' ),
			'object'     => $action->get_name(),
			'object_id'  => $action->get_name(),
			'url'        => $action->get_url(),
		);
	}
	return $items;
}
