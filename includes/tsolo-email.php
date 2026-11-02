<?php
/**
 * Branded HTML emails (same TSO layout as TSO Link Inspector and TSO Options & Tables Cleaner).
 *
 * Every email this plugin sends or triggers (confirmation, welcome, admin notice,
 * password reset, password and email change) goes through tsolo_email_render().
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand shown in the email header and footer.
 *
 * @return string
 */
function tsolo_email_brand_name() {
	return 'TSO Login Organizer';
}

/**
 * Turn the plugin email scope on or off (used for emails sent outside plugin pages).
 *
 * @param bool|null $set True/false to change, null to only read.
 * @return bool
 */
function tsolo_email_scope( $set = null ) {
	static $scope = false;
	if ( null !== $set ) {
		$scope = (bool) $set;
	}
	return $scope;
}

/**
 * Whether the current email was triggered by this plugin.
 *
 * @return bool
 */
function tsolo_email_in_scope() {
	if ( tsolo_email_scope() ) {
		return true;
	}
	return function_exists( 'tsolo_is_action' ) && tsolo_is_action();
}

/**
 * HTML content type and From name, only while one of our emails is being sent.
 *
 * @param bool|null $set True to arm, false to disarm, null to read.
 * @return bool
 */
function tsolo_email_html_armed( $set = null ) {
	static $armed = false;
	if ( null !== $set ) {
		$armed = (bool) $set;
	}
	return $armed;
}

/**
 * Arm HTML sending for the next wp_mail() call only.
 */
function tsolo_email_arm_html() {
	tsolo_email_html_armed( true );
	add_action( 'wp_mail_succeeded', 'tsolo_email_disarm_html', 998 );
	add_action( 'wp_mail_failed', 'tsolo_email_disarm_html', 998 );
	add_action( 'shutdown', 'tsolo_email_disarm_html', 0 );
}

/**
 * Disarm HTML sending after the mail attempt.
 */
function tsolo_email_disarm_html() {
	tsolo_email_html_armed( false );
	remove_action( 'wp_mail_succeeded', 'tsolo_email_disarm_html', 998 );
	remove_action( 'wp_mail_failed', 'tsolo_email_disarm_html', 998 );
	remove_action( 'shutdown', 'tsolo_email_disarm_html', 0 );
}

/**
 * Filter: wp_mail_content_type.
 *
 * @param string $type Content type.
 * @return string
 */
function tsolo_email_filter_content_type( $type ) {
	return tsolo_email_html_armed() ? 'text/html' : $type;
}

/**
 * Filter: wp_mail_from_name.
 *
 * @param string $name From name.
 * @return string
 */
function tsolo_email_filter_from_name( $name ) {
	if ( ! tsolo_email_html_armed() ) {
		return $name;
	}
	/**
	 * Filter the From name of plugin emails (defaults to the site name).
	 *
	 * @param string $from_name From name.
	 */
	return (string) apply_filters( 'tsolo_email_from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
}

/**
 * First http(s) URL found in a plain-text message.
 *
 * @param string $text Message.
 * @return string
 */
function tsolo_email_extract_url( $text ) {
	if ( preg_match( '#https?://[^\s<>"\']+#i', (string) $text, $m ) ) {
		return rtrim( $m[0], '.,;:)' );
	}
	return '';
}

/**
 * Message to safe HTML paragraphs (plain text is escaped and auto-linked; HTML is filtered).
 *
 * @param string $message Message.
 * @return string
 */
function tsolo_email_format_body( $message ) {
	$message = (string) $message;
	$style   = 'margin:0 0 14px;font-size:15px;line-height:1.5;color:#1d2327;';
	if ( $message !== wp_strip_all_tags( $message ) ) {
		return '<div style="' . esc_attr( $style ) . '">' . wp_kses_post( $message ) . '</div>';
	}
	$message = trim( str_replace( array( "\r\n", "\r" ), "\n", $message ) );
	$out     = '';
	foreach ( preg_split( "/\n{2,}/", $message ) as $paragraph ) {
		$paragraph = trim( $paragraph );
		if ( '' === $paragraph ) {
			continue;
		}
		$html = esc_html( $paragraph );
		$html = preg_replace_callback(
			'#https?://[^\s<>"\']+#i',
			function ( $m ) {
				$url = rtrim( $m[0], '.,;:)' );
				$end = substr( $m[0], strlen( $url ) );
				return '<a href="' . esc_url( html_entity_decode( $url ) ) . '" style="color:#1d4ed8;word-break:break-all;">' . $url . '</a>' . $end;
			},
			$html
		);
		$out .= '<p style="' . esc_attr( $style ) . '">' . nl2br( $html, false ) . '</p>';
	}
	return $out;
}

/**
 * Build the HTML email (TSO layout, inline CSS for mail clients).
 *
 * @param array $args title, body_html, button_label, button_url.
 * @return string
 */
function tsolo_email_render( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'        => '',
			'body_html'    => '',
			'button_label' => '',
			'button_url'   => '',
		)
	);

	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$site_url  = home_url( '/' );
	$brand     = tsolo_email_brand_name();

	$footer_line = sprintf(
		/* translators: 1: plugin name, 2: site name. */
		__( 'This notification was sent by %1$s on %2$s.', 'tso-login-organizer' ),
		$brand,
		$site_name
	);

	$font_stack = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,sans-serif';

	$button_html = '';
	if ( '' !== (string) $args['button_url'] && '' !== (string) $args['button_label'] ) {
		$button_html = '<p style="margin:24px 0 0;text-align:center;">'
			. '<a href="' . esc_url( $args['button_url'] ) . '" style="display:inline-block;background:#1d4ed8;color:#fff;font-size:15px;font-weight:600;text-decoration:none;padding:12px 22px;border-radius:6px;">'
			. esc_html( $args['button_label'] ) . '</a></p>';
	}

	return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
		. '<body style="margin:0;padding:0;background:#f0f0f1;font-family:' . esc_attr( $font_stack ) . ';">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0f1;padding:24px 12px;">'
		. '<tr><td align="center">'
		. '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #dcdcde;">'
		. '<tr><td style="background:linear-gradient(135deg,#1e40af 0%,#1d4ed8 100%);color:#fff;padding:22px 24px;">'
		. '<p style="margin:0 0 4px;font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;opacity:.9;">'
		. esc_html( $brand ) . '</p>'
		. '<h1 style="margin:0;font-size:22px;font-weight:700;line-height:1.3;color:#fff;">'
		. esc_html( $args['title'] ) . '</h1>'
		. '<p style="margin:10px 0 0;font-size:14px;opacity:.92;">'
		. '<a href="' . esc_url( $site_url ) . '" style="color:#fff;text-decoration:underline;">'
		. esc_html( $site_name ) . '</a></p>'
		. '</td></tr>'
		. '<tr><td style="padding:24px 24px 8px;">'
		. wp_kses_post( $args['body_html'] )
		. $button_html
		. '</td></tr>'
		. '<tr><td style="padding:16px 24px 20px;background:#f6f7f7;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.5;color:#646970;text-align:center;">'
		. esc_html( $footer_line )
		. '</td></tr>'
		. '</table></td></tr></table></body></html>';
}

/**
 * Wrap a plain/HTML message array ($email['message']) in the TSO layout.
 *
 * @param array  $email        Email array with a message key.
 * @param string $title        Header title.
 * @param string $button_label Label of the call-to-action button (shown when the message has a link).
 * @return array
 */
function tsolo_email_brand_message( $email, $title, $button_label = '' ) {
	if ( ! is_array( $email ) || empty( $email['message'] ) || ! tsolo_email_in_scope() ) {
		return $email;
	}
	// Core wraps reset/set-password links as <https://…>; unwrap so they are not read as HTML tags.
	$message = preg_replace( '#<(https?://[^>\s]+)>#i', '$1', (string) $email['message'] );
	if ( 0 === stripos( ltrim( $message ), '<!doctype' ) ) {
		return $email;
	}
	$email['message'] = tsolo_email_render(
		array(
			'title'        => $title,
			'body_html'    => tsolo_email_format_body( $message ),
			'button_label' => $button_label,
			'button_url'   => tsolo_email_extract_url( $message ),
		)
	);
	tsolo_email_arm_html();
	return $email;
}

/**
 * Send a branded email directly (used for the confirmation email).
 *
 * @param string $to           Recipient.
 * @param string $subject      Subject.
 * @param string $title        Header title.
 * @param string $message      Plain-text message.
 * @param string $button_label Button label.
 * @param string $button_url   Button URL.
 * @return bool
 */
function tsolo_send_email( $to, $subject, $title, $message, $button_label = '', $button_url = '' ) {
	$to = sanitize_email( (string) $to );
	if ( '' === $to ) {
		return false;
	}
	$html = tsolo_email_render(
		array(
			'title'        => $title,
			'body_html'    => tsolo_email_format_body( $message ),
			'button_label' => $button_label,
			'button_url'   => $button_url,
		)
	);
	tsolo_email_arm_html();
	$sent = wp_mail( $to, $subject, $html );
	tsolo_email_disarm_html();
	return (bool) $sent;
}

/**
 * Filter: welcome email to the new user.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_new_user( $email ) {
	return tsolo_email_brand_message( $email, __( 'Welcome to your new account', 'tso-login-organizer' ), __( 'Set your password', 'tso-login-organizer' ) );
}

/**
 * Filter: new user notice to the administrator.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_new_user_admin( $email ) {
	return tsolo_email_brand_message( $email, __( 'New user registration', 'tso-login-organizer' ) );
}

/**
 * Filter: password reset email.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_reset( $email ) {
	return tsolo_email_brand_message( $email, __( 'Password reset request', 'tso-login-organizer' ), __( 'Reset password', 'tso-login-organizer' ) );
}

/**
 * Filter: password changed email to the user.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_password_change( $email ) {
	return tsolo_email_brand_message( $email, __( 'Your password was changed', 'tso-login-organizer' ) );
}

/**
 * Filter: email address changed email to the user.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_email_change( $email ) {
	return tsolo_email_brand_message( $email, __( 'Your email address was changed', 'tso-login-organizer' ) );
}

/**
 * Filter: password changed notice to the administrator.
 *
 * @param array $email Email.
 * @return array
 */
function tsolo_email_filter_password_change_admin( $email ) {
	return tsolo_email_brand_message( $email, __( 'Password changed', 'tso-login-organizer' ) );
}
