<?php
/**
 * Rewrite rules and query handling.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether to use pretty permalinks for actions.
 *
 * @return bool
 */
function tsolo_use_permalinks() {
	global $wp_rewrite;
	$use = ( $wp_rewrite instanceof WP_Rewrite ) && $wp_rewrite->using_permalinks();
	return (bool) apply_filters( 'tsolo_use_permalinks', $use );
}

/**
 * Add rewrite tag.
 */
function tsolo_add_rewrite_tags() {
	add_rewrite_tag( '%action%', '([^/]+)' );
}

/**
 * Add rewrite rules for each action slug.
 */
function tsolo_add_rewrite_rules() {
	if ( ! tsolo_use_permalinks() ) {
		return;
	}
	foreach ( tsolo_get_actions() as $action ) {
		$slug = $action->get_slug();
		if ( '' === $slug ) {
			continue;
		}
		add_rewrite_rule( $slug . '/?$', 'index.php?action=' . $action->get_name(), 'top' );
	}
}

/**
 * Parse request aliases.
 *
 * @param WP $wp WP object.
 */
function tsolo_parse_request( $wp ) {
	if ( ! isset( $wp->query_vars['action'] ) ) {
		return;
	}
	$action = $wp->query_vars['action'];
	if ( 'retrievepassword' === $action ) {
		$action = 'lostpassword';
	} elseif ( 'rp' === $action ) {
		$action = 'resetpass';
	}
	if ( ! tsolo_action_exists( $action ) ) {
		return;
	}
	if ( $action !== $wp->query_vars['action'] ) {
		$wp->set_query_var( 'action', $action );
	}
}

/**
 * Mark query as page for TSOLO actions.
 *
 * @param WP_Query $wp_query Query.
 */
function tsolo_parse_query( $wp_query ) {
	if ( ! $wp_query->is_main_query() ) {
		return;
	}
	if ( ! $wp_query->is_home || $wp_query->is_posts_page ) {
		return;
	}
	if ( ! tsolo_is_action() ) {
		return;
	}
	$wp_query->is_tsolo_action = true;
	$wp_query->is_page         = true;
	$wp_query->is_singular     = true;
	$wp_query->is_single       = false;
	$wp_query->is_home         = false;
	$wp_query->set( 'no_found_rows', true );
	$wp_query->set( 'post__in', array( 0 ) );
}

/**
 * Inject a virtual page for the current action (no WP page required).
 *
 * Leftover Theme My Login pages with the same slug are ignored for rendering;
 * they may be trashed safely. Rewrites still map each slug to index.php?action=.
 *
 * @param array    $posts Posts.
 * @param WP_Query $wp_query Query.
 * @return array
 */
function tsolo_the_posts( $posts, $wp_query ) {
	if ( ! $wp_query->is_main_query() || empty( $wp_query->is_tsolo_action ) ) {
		return $posts;
	}
	$action = tsolo_get_action();
	if ( ! $action ) {
		return $posts;
	}
	$post = new WP_Post(
		(object) array(
			'ID'             => 0,
			'post_type'      => 'page',
			'post_content'   => sprintf( '[tso-login-organizer action="%s"]', $action->get_name() ),
			'post_title'     => tsolo_get_action_title(),
			'post_name'      => tsolo_get_action_slug(),
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'filter'         => 'raw',
		)
	);
	return array( $post );
}

/**
 * Strip conflicting head actions on TSOLO pages.
 */
function tsolo_remove_default_actions_and_filters() {
	if ( ! tsolo_is_action() ) {
		return;
	}
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rel_canonical', 10 );
	remove_filter( 'template_redirect', 'redirect_canonical' );
}

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function tsolo_body_class( $classes ) {
	if ( tsolo_is_action() ) {
		$classes[] = 'tsolo-action';
		$classes[] = 'tsolo-action-' . sanitize_html_class( tsolo_get_action()->get_name() );
	}
	return $classes;
}
