<?php
/**
 * Core hook wiring.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'tsolo_maybe_migrate_schema', 5 );
add_action( 'plugins_loaded', 'tsolo_load_modules', 20 );

add_action( 'init', 'tsolo_register_default_actions', 0 );
add_action( 'init', 'tsolo_register_default_forms', 0 );
add_action( 'init', 'tsolo_add_rewrite_tags' );
add_action( 'init', 'tsolo_add_rewrite_rules' );
add_action( 'init', 'tsolo_flush_scheduled_rewrites', 99 );
add_action( 'init', 'tsolo_setup_login_type_filters', 5 );

add_action( 'widgets_init', array( 'TSOLO_Widget', 'register' ) );

add_action( 'parse_request', 'tsolo_parse_request' );
add_action( 'parse_query', 'tsolo_parse_query' );
add_action( 'wp', 'tsolo_remove_default_actions_and_filters' );
add_action( 'template_redirect', 'tsolo_action_handler', 0 );

add_action( 'wp_enqueue_scripts', 'tsolo_enqueue_styles', 10 );
add_action( 'wp_enqueue_scripts', 'tsolo_enqueue_scripts', 10 );

add_action( 'pre_user_login', 'tsolo_set_user_login' );
add_action( 'register_new_user', 'tsolo_set_new_user_password' );
add_action( 'register_new_user', 'tsolo_handle_auto_login' );
add_action( 'register_new_user', 'tsolo_send_new_user_notifications', 10, 1 );
add_action( 'edit_user_created_user', 'tsolo_send_new_user_notifications', 10, 2 );
remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications' );

add_filter( 'the_posts', 'tsolo_the_posts', 10, 2 );
add_filter( 'body_class', 'tsolo_body_class' );
add_filter( 'get_edit_post_link', 'tsolo_filter_get_edit_post_link', 10, 2 );
add_filter( 'comments_array', 'tsolo_filter_comments_array' );

add_filter( 'site_url', 'tsolo_filter_site_url', 10, 3 );
add_filter( 'network_site_url', 'tsolo_filter_site_url', 10, 3 );
add_filter( 'logout_url', 'tsolo_filter_logout_url', 10, 2 );
add_filter( 'lostpassword_url', 'tsolo_filter_lostpassword_url', 10, 2 );

add_filter( 'authenticate', 'tsolo_enforce_login_type', 20, 3 );
add_filter( 'registration_errors', 'tsolo_validate_new_user_password', 10 );

add_shortcode( 'tso-login-organizer', 'tsolo_shortcode' );
add_shortcode( 'theme-my-login', 'tsolo_shortcode' );

add_action( 'admin_head-nav-menus.php', 'tsolo_admin_nav_menu_meta_box' );
add_filter( 'wp_setup_nav_menu_item', 'tsolo_setup_nav_menu_item', 0 );
add_filter( 'wp_nav_menu_objects', 'tsolo_filter_nav_menu_objects', 10 );
add_filter( 'nav_menu_css_class', 'tsolo_nav_menu_css_class', 10, 2 );
add_filter( 'customize_nav_menu_available_item_types', 'tsolo_filter_customize_nav_menu_available_item_types' );
add_filter( 'customize_nav_menu_available_items', 'tsolo_filter_customize_nav_menu_available_items', 10, 4 );
