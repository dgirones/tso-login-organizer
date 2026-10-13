<?php
/**
 * Admin assets (single enqueue callback).
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'tsolo_admin_register_assets' );

/**
 * Enqueue admin CSS/JS on plugin screens only.
 *
 * @param string $hook Hook suffix.
 */
function tsolo_admin_register_assets( $hook ) {
	if ( false === strpos( $hook, 'tso-login-organizer' ) ) {
		return;
	}
	$css = TSOLO_PATH . 'assets/css/tsolo-admin.css';
	$js  = TSOLO_PATH . 'assets/js/tsolo-admin.js';
	wp_enqueue_style(
		'tsolo-admin',
		TSOLO_URL . 'assets/css/tsolo-admin.css',
		array(),
		file_exists( $css ) ? (string) filemtime( $css ) : TSOLO_VERSION
	);
	// Critical: hide inactive tab panels before the stylesheet finishes loading (avoids F5 FOUC).
	wp_add_inline_style(
		'tsolo-admin',
		'.tsolo-admin-wrap .tsolo-tab-panel:not(.is-active){display:none!important}'
	);
	wp_enqueue_script(
		'tsolo-admin',
		TSOLO_URL . 'assets/js/tsolo-admin.js',
		array( 'jquery' ),
		file_exists( $js ) ? (string) filemtime( $js ) : TSOLO_VERSION,
		true
	);
	wp_localize_script(
		'tsolo-admin',
		'tsoloAdminConfig',
		array(
			'nonce'      => wp_create_nonce( TSOLO_NONCE_AJAX ),
			'collisions' => tsolo_detect_collision_plugins(),
			'i18n'       => array(
				'collisionWarn' => __( 'Other security/spam plugins were detected. Enable colliding modules only if you know they will not conflict.', 'tso-login-organizer' ),
			),
		)
	);
}
