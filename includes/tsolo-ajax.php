<?php
/**
 * AJAX helpers and front assets.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether current request is TSOLO AJAX form submit.
 *
 * @return bool
 */
function tsolo_is_ajax_request() {
	return (bool) tsolo_get_request_text( 'ajax', 'request' );
}

/**
 * Send AJAX success JSON and exit.
 *
 * @param array $data Data.
 */
function tsolo_send_ajax_success( $data = array() ) {
	$data = apply_filters( 'tsolo_ajax_success_data', $data );
	wp_send_json_success( $data );
}

/**
 * Send AJAX error JSON and exit.
 *
 * @param array $data Data.
 */
function tsolo_send_ajax_error( $data = array() ) {
	$data = apply_filters( 'tsolo_ajax_error_data', $data );
	wp_send_json_error( $data );
}

/**
 * Whether front assets are needed on this request.
 *
 * @return bool
 */
function tsolo_needs_front_assets() {
	if ( tsolo_is_action() || tsolo_content_has_shortcode() ) {
		return true;
	}
	if ( is_active_widget( false, false, 'tsolo_login_organizer', true ) ) {
		return true;
	}
	if ( is_singular( 'post' ) && tsolo_is_module_enabled( 'favorites' ) ) {
		return true;
	}
	return (bool) apply_filters( 'tsolo_needs_front_assets', false );
}

/**
 * Enqueue front styles.
 */
function tsolo_enqueue_styles() {
	if ( ! tsolo_needs_front_assets() ) {
		return;
	}
	wp_enqueue_style(
		'tsolo-front',
		TSOLO_URL . 'assets/css/tsolo-front.css',
		array(),
		TSOLO_VERSION
	);
}

/**
 * Shared front JS config (merge-safe for modules that also localize).
 *
 * @param array $extra Extra keys.
 * @return array
 */
function tsolo_get_front_script_config( $extra = array() ) {
	$settings = tsolo_get_settings();
	$base     = array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( TSOLO_NONCE_AJAX ),
		'ajaxEnabled' => ! empty( $settings['ajax'] ),
		'i18n'        => array(
			'unexpected' => __( 'Unexpected server response. Please try again.', 'tso-login-organizer' ),
		),
	);
	return array_merge( $base, $extra );
}

/**
 * Enqueue front scripts.
 */
function tsolo_enqueue_scripts() {
	$settings = tsolo_get_settings();
	if ( ! tsolo_needs_front_assets() ) {
		return;
	}
	$need_js = ! empty( $settings['ajax'] )
		|| ! empty( $settings['user_passwords'] )
		|| tsolo_is_action( 'resetpass' )
		|| ( is_singular( 'post' ) && tsolo_is_module_enabled( 'favorites' ) );
	if ( $need_js ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'tsolo-front',
			TSOLO_URL . 'assets/js/tsolo-front.js',
			array( 'jquery' ),
			TSOLO_VERSION,
			true
		);
		wp_localize_script( 'tsolo-front', 'tsoloFrontConfig', tsolo_get_front_script_config() );
	}
	if ( ! empty( $settings['user_passwords'] ) || tsolo_is_action( 'resetpass' ) || tsolo_is_action( 'register' ) ) {
		wp_enqueue_script( 'password-strength-meter' );
	}
}

/**
 * Rough check for shortcode in main query post (widget/shortcode elsewhere still enqueues on action pages).
 *
 * @return bool
 */
function tsolo_content_has_shortcode() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'tso-login-organizer' ) || has_shortcode( $post->post_content, 'theme-my-login' );
}
