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
			$menu_item->url        = $action->get_url();
			if ( 'logout' === $action->get_name() ) {
				$menu_item->url = wp_nonce_url( $menu_item->url, 'log-out' );
			}
			if ( ! $action->show_nav_menu_item ) {
				$menu_item->_invalid = true;
			}
		} else {
			$menu_item->_invalid = true;
		}
	}
	// TML compatibility: treat theme-my-login action items if present.
	if ( isset( $menu_item->type ) && 'tml_action' === $menu_item->type ) {
		$action = tsolo_get_action( $menu_item->object );
		if ( $action ) {
			$menu_item->url = $action->get_url();
			if ( 'logout' === $action->get_name() ) {
				$menu_item->url = wp_nonce_url( $menu_item->url, 'log-out' );
			}
			if ( ! $action->show_nav_menu_item ) {
				$menu_item->_invalid = true;
			}
		}
	}
	return $menu_item;
}

/**
 * Hide invalid TSOLO items from front.
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
