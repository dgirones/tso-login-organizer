<?php
/**
 * Shortcodes and profile / favorites HTML.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main shortcode callback.
 *
 * @param array $atts Attributes.
 * @return string
 */
function tsolo_shortcode( $atts ) {
	$atts   = shortcode_atts(
		array(
			'action'     => '',
			'show_links' => 1,
			'show_form'  => 1,
		),
		$atts,
		'tso-login-organizer'
	);
	$action = sanitize_key( $atts['action'] );
	if ( '' === $action && tsolo_is_action() ) {
		$current = tsolo_get_action();
		$action  = $current ? $current->get_name() : '';
	}
	if ( 'confirmaction' === $action ) {
		return '<div class="tsolo-confirmaction"><p>' . esc_html__( 'Your request has been confirmed.', 'tso-login-organizer' ) . '</p></div>';
	}
	if ( 'profile' === $action ) {
		return tsolo_render_profile_form();
	}
	if ( 'favorites' === $action ) {
		return tsolo_render_favorites();
	}
	if ( 'dashboard' === $action ) {
		return tsolo_render_dashboard();
	}
	if ( $action && tsolo_action_exists( $action ) ) {
		if ( in_array( $action, array( 'logout', 'resetpass', 'rp' ), true ) ) {
			return '';
		}
		return tsolo_render_form(
			$action,
			array(
				'show_links' => ! empty( $atts['show_links'] ),
				'show_form'  => ! empty( $atts['show_form'] ),
			)
		);
	}
	if ( is_user_logged_in() ) {
		return tsolo_render_dashboard();
	}
	return tsolo_render_form(
		'login',
		array(
			'show_links' => ! empty( $atts['show_links'] ),
			'show_form'  => ! empty( $atts['show_form'] ),
		)
	);
}

/**
 * Account dashboard / logged-in links.
 *
 * @return string
 */
function tsolo_render_dashboard() {
	return tsolo_render_logged_in_links();
}

/**
 * Logged-in quick links.
 *
 * @return string
 */
function tsolo_render_logged_in_links() {
	$links = array();
	if ( tsolo_action_exists( 'dashboard' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'dashboard' ) ) . '">' . esc_html__( 'Dashboard', 'tso-login-organizer' ) . '</a>';
	}
	if ( tsolo_action_exists( 'profile' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'profile' ) ) . '">' . esc_html__( 'Profile', 'tso-login-organizer' ) . '</a>';
	} else {
		$links[] = '<a href="' . esc_url( admin_url( 'profile.php' ) ) . '">' . esc_html__( 'Profile', 'tso-login-organizer' ) . '</a>';
	}
	if ( tsolo_action_exists( 'favorites' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'favorites' ) ) . '">' . esc_html__( 'Favorites', 'tso-login-organizer' ) . '</a>';
	}
	$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'logout' ) ) . '">' . esc_html__( 'Log Out', 'tso-login-organizer' ) . '</a>';
	$html    = '<div class="tsolo tsolo-links"><ul>';
	foreach ( $links as $link ) {
		$html .= '<li>' . $link . '</li>';
	}
	$html .= '</ul></div>';
	return $html;
}

/**
 * Render profile form HTML.
 *
 * @return string
 */
function tsolo_render_profile_form() {
	if ( ! is_user_logged_in() ) {
		return tsolo_render_form( 'login' );
	}
	$user    = wp_get_current_user();
	$avatars = tsolo_is_module_enabled( 'avatars' );
	$enctype = $avatars ? ' enctype="multipart/form-data"' : '';
	$html    = '<div class="tsolo tsolo-profile">';
	$html   .= tsolo_render_errors_html();
	$html   .= '<form method="post" class="tsolo-form" action="' . esc_url( tsolo_get_action_url( 'profile' ) ) . '"' . $enctype . '>';
	$html   .= '<input type="hidden" name="tsolo_profile_nonce" value="' . esc_attr( wp_create_nonce( 'tsolo_save_profile' ) ) . '" />';
	$html   .= '<p class="tsolo-field"><label for="display_name">' . esc_html__( 'Display Name', 'tso-login-organizer' ) . '</label>';
	$html   .= '<input type="text" name="display_name" id="display_name" value="' . esc_attr( $user->display_name ) . '" class="input" /></p>';
	$html   .= '<p class="tsolo-field"><label for="user_email">' . esc_html__( 'Email', 'tso-login-organizer' ) . '</label>';
	$html   .= '<input type="email" name="user_email" id="user_email" value="' . esc_attr( $user->user_email ) . '" class="input" /></p>';
	$html   .= '<p class="tsolo-field"><label for="pass1">' . esc_html__( 'New Password', 'tso-login-organizer' ) . '</label>';
	$html   .= '<input type="password" name="pass1" id="pass1" class="input" autocomplete="new-password" /></p>';
	$html   .= '<p class="tsolo-field"><label for="pass2">' . esc_html__( 'Confirm Password', 'tso-login-organizer' ) . '</label>';
	$html   .= '<input type="password" name="pass2" id="pass2" class="input" autocomplete="new-password" /></p>';

	if ( $avatars ) {
		$current = get_user_meta( $user->ID, 'tsolo_avatar_url', true );
		$html   .= '<input type="hidden" name="tsolo_avatar_nonce" value="' . esc_attr( wp_create_nonce( 'tsolo_upload_avatar' ) ) . '" />';
		$html   .= '<p class="tsolo-field tsolo-field-avatar"><label for="tsolo_avatar">' . esc_html__( 'Profile picture', 'tso-login-organizer' ) . '</label>';
		if ( $current ) {
			$html .= '<span class="tsolo-avatar-preview"><img src="' . esc_url( $current ) . '" alt="" width="64" height="64" /></span> ';
			$html .= '<span class="description">' . esc_html__( 'You have a custom profile picture (shown above).', 'tso-login-organizer' ) . '</span> ';
		} else {
			$html .= '<span class="description">' . esc_html__( 'No custom picture yet — your Gravatar is used until you upload one.', 'tso-login-organizer' ) . '</span> ';
		}
		$html .= '<input type="file" name="tsolo_avatar" id="tsolo_avatar" accept="image/jpeg,image/png,image/gif,image/webp" /></p>';
		$html .= '<p class="description">' . esc_html__( 'JPG, PNG, GIF or WebP. Leave empty to keep the current picture.', 'tso-login-organizer' ) . '</p>';
	}

	if ( tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
		$secret  = get_user_meta( $user->ID, 'tsolo_2fa_secret', true );
		$enabled = (bool) $secret;
		$html   .= '<p class="tsolo-field tsolo-field-2fa-enable"><label><input type="checkbox" name="tsolo_2fa_enable" value="1" ' . checked( $enabled, true, false ) . ' /> ' . esc_html__( 'Enable two-factor authentication (authenticator app)', 'tso-login-organizer' ) . '</label></p>';
		if ( $secret ) {
			$html .= '<p class="description">' . esc_html__( 'Two-factor is ON for your account. Add this secret to Google Authenticator (or similar), then enter the 6-digit code when you log in.', 'tso-login-organizer' ) . ' <code>' . esc_html( $secret ) . '</code></p>';
		} else {
			$html .= '<p class="description">' . esc_html__( 'Checking this box and saving generates a secret key. Scan or type it into an authenticator app on your phone; after that, each login needs your password plus a 6-digit code from the app. Uncheck and save to turn 2FA off.', 'tso-login-organizer' ) . '</p>';
		}
	}

	$html .= '<p class="tsolo-field"><button type="submit" class="tsolo-button">' . esc_html__( 'Save Profile', 'tso-login-organizer' ) . '</button></p>';
	$html .= '</form></div>';
	return $html;
}

/**
 * Render favorites list.
 *
 * @return string
 */
function tsolo_render_favorites() {
	if ( ! is_user_logged_in() ) {
		return tsolo_render_form( 'login' );
	}
	if ( ! class_exists( 'TSOLO_Mod_Favorites' ) ) {
		return '<p>' . esc_html__( 'Favorites module is not available.', 'tso-login-organizer' ) . '</p>';
	}
	$ids = TSOLO_Mod_Favorites::get_favorites( get_current_user_id() );
	if ( empty( $ids ) ) {
		return '<div class="tsolo tsolo-favorites"><p>' . esc_html__( 'No favorites yet.', 'tso-login-organizer' ) . '</p></div>';
	}
	$html = '<div class="tsolo tsolo-favorites"><ul>';
	foreach ( $ids as $post_id ) {
		$title = get_the_title( $post_id );
		if ( ! $title ) {
			continue;
		}
		$html .= '<li><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $title ) . '</a></li>';
	}
	$html .= '</ul></div>';
	return $html;
}
