<?php
/**
 * URL filters replacing wp-login.php links.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether current request is wp-login.php.
 *
 * @return bool
 */
function tsolo_is_wp_login() {
	global $pagenow;
	return ( isset( $pagenow ) && 'wp-login.php' === $pagenow );
}

/**
 * Filter site_url for login actions.
 *
 * @param string $url    URL.
 * @param string $path   Path.
 * @param string $scheme Scheme.
 * @return string
 */
function tsolo_filter_site_url( $url, $path, $scheme ) {
	if ( tsolo_is_wp_login() ) {
		return $url;
	}
	if ( false === strpos( $url, 'wp-login.php' ) ) {
		return $url;
	}

	$parsed = wp_parse_url( $url );
	$query  = array();
	if ( ! empty( $parsed['query'] ) ) {
		parse_str( $parsed['query'], $query );
	}
	$action = isset( $query['action'] ) ? sanitize_key( $query['action'] ) : 'login';
	if ( 'retrievepassword' === $action || 'rp' === $action ) {
		$action = ( 'rp' === $action ) ? 'resetpass' : 'lostpassword';
	}
	if ( ! tsolo_action_exists( $action ) ) {
		return $url;
	}

	$new = tsolo_get_action_url( $action, $scheme ? $scheme : 'login' );
	unset( $query['action'] );
	if ( ! empty( $query ) ) {
		$new = add_query_arg( $query, $new );
	}
	return $new;
}

/**
 * Filter logout URL.
 *
 * @param string $logout_url Logout URL.
 * @param string $redirect   Redirect.
 * @return string
 */
function tsolo_filter_logout_url( $logout_url, $redirect ) {
	if ( ! tsolo_action_exists( 'logout' ) ) {
		return $logout_url;
	}
	$url = tsolo_get_action_url( 'logout' );
	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
	}
	$url = wp_nonce_url( $url, 'log-out' );
	return $url;
}

/**
 * Filter lost password URL.
 *
 * @param string $url      URL.
 * @param string $redirect Redirect.
 * @return string
 */
function tsolo_filter_lostpassword_url( $url, $redirect ) {
	if ( ! tsolo_action_exists( 'lostpassword' ) ) {
		return $url;
	}
	$new = tsolo_get_action_url( 'lostpassword' );
	if ( $redirect ) {
		$new = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $new );
	}
	return $new;
}

/**
 * Enforce login type on authenticate.
 *
 * @param WP_User|WP_Error|null $user     User.
 * @param string                $username Username.
 * @param string                $password Password.
 * @return WP_User|WP_Error|null
 */
function tsolo_enforce_login_type( $user, $username, $password ) {
	unset( $password );
	$settings = tsolo_get_settings();
	if ( 'email' === $settings['login_type'] && $username && ! is_email( $username ) ) {
		return new WP_Error( 'invalid_email', __( '<strong>Error:</strong> Please enter a valid email address.', 'tso-login-organizer' ) );
	}
	if ( 'username' === $settings['login_type'] && $username && is_email( $username ) ) {
		return new WP_Error( 'invalid_username', __( '<strong>Error:</strong> Please enter a username, not an email address.', 'tso-login-organizer' ) );
	}
	return $user;
}

/**
 * Adjust authenticate filters for login type.
 */
function tsolo_setup_login_type_filters() {
	$settings = tsolo_get_settings();
	if ( 'username' === $settings['login_type'] ) {
		remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
	} elseif ( 'email' === $settings['login_type'] ) {
		remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
	}
}

/**
 * Set generated username for email-only registration.
 *
 * @param string $user_login Login.
 * @return string
 */
function tsolo_set_user_login( $user_login ) {
	$settings = tsolo_get_settings();
	if ( 'email' !== $settings['registration_type'] ) {
		return $user_login;
	}
	$email = tsolo_get_request_text( 'user_email', 'post' );
	if ( $email && is_email( $email ) ) {
		$base = sanitize_user( current( explode( '@', $email ) ), true );
		if ( '' === $base ) {
			$base = 'user';
		}
		$user_login = $base;
		$i          = 1;
		while ( username_exists( $user_login ) ) {
			$user_login = $base . $i;
			++$i;
		}
	}
	return $user_login;
}

/**
 * Save user-chosen password on registration.
 *
 * @param int $user_id User ID.
 */
function tsolo_set_new_user_password( $user_id ) {
	$settings = tsolo_get_settings();
	if ( empty( $settings['user_passwords'] ) ) {
		return;
	}
	$pass1 = tsolo_get_request_text( 'pass1', 'post' );
	$pass2 = tsolo_get_request_text( 'pass2', 'post' );
	if ( $pass1 && $pass1 === $pass2 ) {
		wp_set_password( $pass1, $user_id );
		update_user_option( $user_id, 'default_password_nag', false, true );
	}
}

/**
 * Validate registration password.
 *
 * @param WP_Error $errors Errors.
 * @return WP_Error
 */
function tsolo_validate_new_user_password( $errors ) {
	$settings = tsolo_get_settings();
	if ( empty( $settings['user_passwords'] ) || tsolo_is_wp_login() ) {
		return $errors;
	}
	$pass1 = tsolo_get_request_text( 'pass1', 'post' );
	$pass2 = tsolo_get_request_text( 'pass2', 'post' );
	if ( '' === $pass1 || '' === $pass2 ) {
		$errors->add( 'empty_password', __( '<strong>Error:</strong> Please enter a password.', 'tso-login-organizer' ) );
	} elseif ( $pass1 !== $pass2 ) {
		$errors->add( 'password_mismatch', __( '<strong>Error:</strong> Passwords do not match.', 'tso-login-organizer' ) );
	}
	return $errors;
}

/**
 * Auto-login after registration.
 *
 * @param int $user_id User ID.
 */
function tsolo_handle_auto_login( $user_id ) {
	$settings = tsolo_get_settings();
	if ( empty( $settings['auto_login'] ) || is_user_logged_in() ) {
		return;
	}
	if ( class_exists( 'TSOLO_Mod_Moderation', false ) && TSOLO_Mod_Moderation::blocks_auto_login() ) {
		return;
	}
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id );
}

/**
 * Control new user notifications when custom passwords used.
 *
 * @param int    $user_id User ID.
 * @param string $notify  Notify type.
 */
function tsolo_send_new_user_notifications( $user_id, $notify = 'both' ) {
	$settings = tsolo_get_settings();
	if ( ! empty( $settings['user_passwords'] ) ) {
		$notify = 'admin';
	}
	wp_send_new_user_notifications( $user_id, $notify );
}
