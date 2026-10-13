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
 * Enqueue front styles.
 */
function tsolo_enqueue_styles() {
	if ( ! tsolo_is_action() && ! tsolo_content_has_shortcode() ) {
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
 * Enqueue front scripts.
 */
function tsolo_enqueue_scripts() {
	$settings = tsolo_get_settings();
	$need     = tsolo_is_action() || tsolo_content_has_shortcode();
	if ( ! $need ) {
		return;
	}
	if ( ! empty( $settings['ajax'] ) || ! empty( $settings['user_passwords'] ) || tsolo_is_action( 'resetpass' ) ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'tsolo-front',
			TSOLO_URL . 'assets/js/tsolo-front.js',
			array( 'jquery' ),
			TSOLO_VERSION,
			true
		);
		wp_localize_script(
			'tsolo-front',
			'tsoloFrontConfig',
			array(
				'ajaxEnabled' => ! empty( $settings['ajax'] ),
				'i18n'        => array(
					'unexpected' => __( 'Unexpected server response. Please try again.', 'tso-login-organizer' ),
				),
			)
		);
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
