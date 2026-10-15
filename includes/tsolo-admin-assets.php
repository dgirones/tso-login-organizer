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
	$css  = TSOLO_PATH . 'assets/css/tsolo-admin.css';
	$js   = TSOLO_PATH . 'assets/js/tsolo-admin.js';
	$boot = TSOLO_PATH . 'assets/js/tsolo-theme-boot.js';
	wp_enqueue_script(
		'tsolo-theme-boot',
		TSOLO_URL . 'assets/js/tsolo-theme-boot.js',
		array(),
		file_exists( $boot ) ? (string) filemtime( $boot ) : TSOLO_VERSION,
		false
	);
	wp_enqueue_style(
		'tsolo-admin',
		TSOLO_URL . 'assets/css/tsolo-admin.css',
		array(),
		file_exists( $css ) ? (string) filemtime( $css ) : TSOLO_VERSION
	);
	// Critical: hide inactive tab panels before the stylesheet finishes loading (avoids F5 FOUC).
	wp_add_inline_style(
		'tsolo-admin',
		'.tsolo-admin-wrap .tsolo-tab-panel:not(.is-active){display:none!important}.tsolo-admin-wrap .tsolo-nav-tabs{display:flex;clear:both;float:none;width:100%}.tsolo-admin-wrap .tsolo-nav-tabs .nav-tab{float:none}.tsolo-admin-wrap .tsolo-nav-primary{flex-wrap:nowrap}.tsolo-admin-wrap .tsolo-nav-modules{flex-wrap:wrap}.tsolo-admin-wrap .tsolo-tab-panels{clear:both;display:block;width:100%}'
	);
	wp_enqueue_script(
		'tsolo-admin',
		TSOLO_URL . 'assets/js/tsolo-admin.js',
		array( 'jquery' ),
		file_exists( $js ) ? (string) filemtime( $js ) : TSOLO_VERSION,
		true
	);
	$lang   = function_exists( 'tsolo_get_ui_lang' ) ? tsolo_get_ui_lang() : 'en';
	$coords = function_exists( 'tsolo_theme_coords' ) ? tsolo_theme_coords() : array(
		'lat' => 41.39,
		'lng' => 2.17,
	);
	wp_localize_script(
		'tsolo-admin',
		'tsoloAdminConfig',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'homeUrl'    => home_url( '/' ),
			'nonce'      => wp_create_nonce( TSOLO_NONCE_AJAX ),
			'collisions' => tsolo_detect_collision_plugins(),
			'timezone'   => function_exists( 'tsolo_theme_timezone_string' ) ? tsolo_theme_timezone_string() : '',
			'lat'        => isset( $coords['lat'] ) ? $coords['lat'] : 41.39,
			'lng'        => isset( $coords['lng'] ) ? $coords['lng'] : 2.17,
			'themeI18n'  => function_exists( 'tsolo_theme_i18n' ) ? tsolo_theme_i18n( $lang ) : array(),
			'i18n'       => array(
				'collisionWarn'   => __( 'Other security/spam plugins were detected. Enable colliding modules only if you know they will not conflict.', 'tso-login-organizer' ),
				'permalinkBusy'   => __( 'Checking friendly URLs…', 'tso-login-organizer' ),
				'permalinkFailed' => __( 'Could not check friendly URLs. Please try again.', 'tso-login-organizer' ),
			),
		)
	);
}
