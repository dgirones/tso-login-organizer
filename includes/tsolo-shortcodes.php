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
			'action'      => '',
			'show_links'  => 1,
			'show_form'   => 1,
			'redirect_to' => '',
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
	if ( 'logout' === $action ) {
		return '';
	}
	// Logged-in users see the account dashboard instead of login/register/lost-password forms.
	if ( is_user_logged_in() && in_array( $action, array( 'login', 'register', 'lostpassword', '' ), true ) ) {
		return tsolo_render_dashboard();
	}
	if ( $action && tsolo_action_exists( $action ) ) {
		if ( 'rp' === $action ) {
			$action = 'resetpass';
		}
		if ( '' !== $atts['redirect_to'] && 'login' === $action ) {
			$form = tsolo_get_form( 'login' );
			if ( $form && isset( $form['fields']['redirect_to'] ) ) {
				tsolo_add_form_field(
					'login',
					'redirect_to',
					array_merge(
						$form['fields']['redirect_to'],
						array(
							'value' => esc_url_raw( $atts['redirect_to'] ),
						)
					)
				);
			}
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
	$user  = wp_get_current_user();
	$html  = '<div class="tsolo tsolo-dashboard">';
	$html .= '<div class="tsolo-dashboard-avatar">' . get_avatar( $user->ID, 96 ) . '</div>';
	/* translators: %s: current user's display name. */
	$greeting = sprintf( __( 'Howdy, %s', 'tso-login-organizer' ), $user->display_name );
	$html    .= '<p class="tsolo-dashboard-greeting">' . esc_html( $greeting ) . '</p>';
	$html    .= tsolo_render_logged_in_links();
	$html    .= '</div>';
	return $html;
}

/**
 * Logged-in quick links.
 *
 * @return string
 */
function tsolo_render_logged_in_links() {
	$links = array();
	if ( tsolo_action_exists( 'dashboard' ) && ! tsolo_is_action( 'dashboard' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'dashboard' ) ) . '">' . esc_html__( 'Dashboard', 'tso-login-organizer' ) . '</a>';
	}
	if ( current_user_can( 'edit_posts' ) ) {
		$links[] = '<a href="' . esc_url( admin_url() ) . '">' . esc_html__( 'Site Admin', 'tso-login-organizer' ) . '</a>';
	}
	if ( tsolo_action_exists( 'profile' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'profile' ) ) . '">' . esc_html__( 'Profile', 'tso-login-organizer' ) . '</a>';
	} else {
		$links[] = '<a href="' . esc_url( admin_url( 'profile.php' ) ) . '">' . esc_html__( 'Profile', 'tso-login-organizer' ) . '</a>';
	}
	if ( tsolo_action_exists( 'favorites' ) ) {
		$links[] = '<a href="' . esc_url( tsolo_get_action_url( 'favorites' ) ) . '">' . esc_html__( 'Favorites', 'tso-login-organizer' ) . '</a>';
	}
	// wp_logout_url() is filtered to the TSOLO logout slug and includes the log-out nonce.
	$links[] = '<a href="' . esc_url( wp_logout_url() ) . '">' . esc_html__( 'Log Out', 'tso-login-organizer' ) . '</a>';
	$html    = '<nav class="tsolo-links" aria-label="' . esc_attr__( 'Account', 'tso-login-organizer' ) . '"><ul>';
	foreach ( $links as $link ) {
		$html .= '<li>' . $link . '</li>';
	}
	$html .= '</ul></nav>';
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
		$html   .= '<div class="tsolo-field tsolo-field-avatar"><label for="tsolo_avatar">' . esc_html__( 'Profile picture', 'tso-login-organizer' ) . '</label>';
		if ( $current ) {
			$html .= '<p class="tsolo-avatar-status">' . esc_html__( 'You already have a profile picture. It is shown below. Choose a new file only if you want to replace it. Leave the file field empty to keep this one.', 'tso-login-organizer' ) . '</p>';
			$html .= '<span class="tsolo-avatar-preview"><img src="' . esc_url( $current ) . '" alt="' . esc_attr__( 'Current profile picture', 'tso-login-organizer' ) . '" width="64" height="64" /></span>';
		} else {
			$html .= '<p class="tsolo-avatar-status">' . esc_html__( 'You do not have a profile picture yet. Until you upload one, the site uses Gravatar or the default avatar.', 'tso-login-organizer' ) . '</p>';
		}
		$html .= '<input type="file" name="tsolo_avatar" id="tsolo_avatar" accept="image/jpeg,image/png,image/gif,image/webp" />';
		$html .= '<p class="description">' . esc_html__( 'JPG, PNG, GIF or WebP.', 'tso-login-organizer' ) . '</p></div>';
	}

	if ( tsolo_is_module_enabled( 'two_factor' ) && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
		$secret  = get_user_meta( $user->ID, 'tsolo_2fa_secret', true );
		$enabled = (bool) $secret;
		$html   .= '<div class="tsolo-field tsolo-field-2fa-enable">';
		$html   .= '<label><input type="checkbox" name="tsolo_2fa_enable" value="1" ' . checked( $enabled, true, false ) . ' /> ' . esc_html__( 'Require an app code at login (two-factor)', 'tso-login-organizer' ) . '</label>';
		if ( $secret ) {
			$html .= '<p class="description tsolo-2fa-help">' . esc_html__( 'Two-factor is already on for this account. The key below is the one your authenticator app should contain. Saving again with the box checked does not create a new key. Uncheck the box and click Save Profile to turn two-factor off: the key is deleted and the app code stops working. Login then asks for the password only.', 'tso-login-organizer' ) . '</p>';
			$html .= '<p class="description">' . esc_html__( 'Key to type into the app (Google Authenticator or similar):', 'tso-login-organizer' ) . ' <code>' . esc_html( $secret ) . '</code></p>';
		} else {
			$html .= '<p class="description tsolo-2fa-help">' . esc_html__( 'Leave this unchecked if the password is enough. Check it and click Save Profile to turn two-factor on. The key is created only when you save, not when you tick the box. After saving you will see a key: type it into an authenticator app. From the next login, the password alone is not enough — the site also asks for the 6-digit code from that app.', 'tso-login-organizer' ) . '</p>';
		}
		$html .= '</div>';
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
