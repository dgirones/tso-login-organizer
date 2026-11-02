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
 * Front query vars used by wp-login / TSO virtual pages.
 *
 * Must be registered so crawl tools (Yoast “Remove unregistered URL parameters”)
 * do not 301 /login/?redirect_to=…&reauth=1 onto itself (ERR_TOO_MANY_REDIRECTS).
 *
 * @return string[]
 */
function tsolo_login_query_vars() {
	return array(
		'redirect_to',
		'reauth',
		'loggedout',
		'checkemail',
		'registration',
		'resetpass',
		'error',
		'tsolo_error',
		'key',
		'login',
		'interim-login',
		'privacy',
		'action',
		'rememberme',
		'tsolo_social',
		'tsolo_social_linked',
		'tsolo_social_error',
	);
}

/**
 * Register login query vars with WordPress (public query vars list).
 *
 * @param string[] $vars Vars.
 * @return string[]
 */
function tsolo_register_login_query_vars( $vars ) {
	foreach ( tsolo_login_query_vars() as $var ) {
		if ( ! in_array( $var, $vars, true ) ) {
			$vars[] = $var;
		}
	}
	return $vars;
}

/**
 * Tell Yoast SEO not to strip login query args (crawl URL cleanup).
 *
 * Without this, Yoast 301s /login/?redirect_to=…&reauth=1 onto itself for
 * guests → ERR_TOO_MANY_REDIRECTS (admin auth_redirect, /perfil, etc.).
 *
 * @param string[] $allowed Allowed extra vars.
 * @return string[]
 */
function tsolo_yoast_allowlist_login_query_vars( $allowed ) {
	if ( ! is_array( $allowed ) ) {
		$allowed = array();
	}
	foreach ( tsolo_login_query_vars() as $var ) {
		if ( ! in_array( $var, $allowed, true ) ) {
			$allowed[] = $var;
		}
	}
	return $allowed;
}

/**
 * Merge login query vars into Yoast's stored “extra variables” option (read filter).
 *
 * Survives if another plugin clears the allowlist filter; also keeps the Yoast
 * settings UI in sync with what TSO needs.
 *
 * @param mixed $options wpseo option value.
 * @return mixed
 */
function tsolo_filter_wpseo_clean_permalink_extras( $options ) {
	if ( ! is_array( $options ) ) {
		return $options;
	}
	$key  = 'clean_permalinks_extra_variables';
	$have = array();
	if ( ! empty( $options[ $key ] ) && is_string( $options[ $key ] ) ) {
		foreach ( explode( ',', $options[ $key ] ) as $piece ) {
			$piece = sanitize_key( trim( $piece ) );
			if ( '' !== $piece ) {
				$have[] = $piece;
			}
		}
	}
	foreach ( tsolo_login_query_vars() as $var ) {
		$var = sanitize_key( $var );
		if ( '' !== $var && ! in_array( $var, $have, true ) ) {
			$have[] = $var;
		}
	}
	$options[ $key ] = implode( ',', $have );
	return $options;
}

/**
 * Normalize path + query for comparing “same request” redirects.
 *
 * @param string $url Absolute or root-relative URL.
 * @return string
 */
function tsolo_normalize_url_for_compare( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return '';
	}
	if ( 0 === strpos( $url, '//' ) ) {
		$url = ( tsolo_request_is_ssl() ? 'https:' : 'http:' ) . $url;
	} elseif ( 0 === strpos( $url, '/' ) ) {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized above.
		if ( '' === $host && function_exists( 'wp_parse_url' ) ) {
			$home = wp_parse_url( home_url( '/' ) );
			$host = ( is_array( $home ) && ! empty( $home['host'] ) ) ? (string) $home['host'] : '';
		}
		$url = ( tsolo_request_is_ssl() ? 'https://' : 'http://' ) . $host . $url;
	}
	$parts = function_exists( 'wp_parse_url' ) ? wp_parse_url( $url ) : parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Fallback outside WP bootstrap edge.
	if ( ! is_array( $parts ) ) {
		return '';
	}
	$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '';
	$path  = untrailingslashit( strtolower( $path ) );
	$query = array();
	if ( ! empty( $parts['query'] ) ) {
		parse_str( (string) $parts['query'], $query );
		if ( is_array( $query ) ) {
			ksort( $query );
		} else {
			$query = array();
		}
	}
	return $path . '?' . http_build_query( $query );
}

/**
 * Whether $location points at the same front path+query as the current request.
 *
 * @param string $location Redirect target.
 * @return bool
 */
function tsolo_redirect_targets_same_request( $location ) {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Compared after normalize.
	if ( '' === $uri || '' === (string) $location ) {
		return false;
	}
	$current = tsolo_normalize_url_for_compare( (string) $uri );
	$target  = tsolo_normalize_url_for_compare( (string) $location );
	if ( '' === $current || $current !== $target ) {
		return false;
	}
	// Absolute target on another scheme or host (http→https, www): a real redirect, keep it.
	$parts = wp_parse_url( (string) $location );
	if ( is_array( $parts ) && ! empty( $parts['host'] ) ) {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized above.
		if ( '' !== $host && strtolower( (string) $parts['host'] ) !== preg_replace( '/:\d+$/', '', $host ) ) {
			return false;
		}
		if ( ! empty( $parts['scheme'] ) && ( 'https' === strtolower( (string) $parts['scheme'] ) ) !== tsolo_request_is_ssl() ) {
			return false;
		}
	}
	return true;
}

/**
 * Whether this front request is a TSO virtual action URL (login, perfil, …).
 *
 * @return bool
 */
function tsolo_request_is_action_front() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}
	if ( function_exists( 'tsolo_is_action' ) && tsolo_is_action() ) {
		return true;
	}
	// Before parse_request finishes, match known slugs from settings.
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Path only.
	$path = (string) wp_parse_url( (string) $uri, PHP_URL_PATH );
	$path = untrailingslashit( strtolower( $path ) );
	if ( '' === $path ) {
		return false;
	}
	$settings = function_exists( 'tsolo_get_settings' ) ? tsolo_get_settings() : array();
	$slugs    = ( ! empty( $settings['slugs'] ) && is_array( $settings['slugs'] ) ) ? $settings['slugs'] : array();
	$home_path = untrailingslashit( strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ) );
	foreach ( $slugs as $slug ) {
		$slug = trim( strtolower( (string) $slug ), '/' );
		if ( '' === $slug ) {
			continue;
		}
		// Exact match under the site root only (a real page like /blog/login must not match).
		if ( $path === $home_path . '/' . $slug ) {
			return true;
		}
	}
	return false;
}

/**
 * Last-resort: cancel 301/302 that bounce a TSO action URL onto itself.
 *
 * Catches Yoast/LiteSpeed/other cleaners that mis-compare URLs and loop.
 *
 * @param string $location Destination.
 * @param int    $status   HTTP status.
 * @return string|false
 */
function tsolo_abort_action_self_redirect( $location, $status ) {
	$status = (int) $status;
	if ( $status < 300 || $status > 399 ) {
		return $location;
	}
	if ( ! tsolo_request_is_action_front() ) {
		return $location;
	}
	if ( tsolo_redirect_targets_same_request( (string) $location ) ) {
		return false;
	}
	return $location;
}

/**
 * On TSO action pages, unregister Yoast’s clean_permalinks callback.
 *
 * Allowlist alone is not enough if Yoast mis-builds $proper_url vs $current_url
 * and still 301s (observed self-redirect with X-Redirect-By: Yoast SEO).
 */
function tsolo_disable_yoast_clean_on_actions() {
	if ( ! tsolo_request_is_action_front() ) {
		return;
	}
	if ( ! function_exists( 'YoastSEO' ) ) {
		return;
	}
	$class = 'Yoast\\WP\\SEO\\Initializers\\Crawl_Cleanup_Permalinks';
	if ( ! class_exists( $class ) ) {
		return;
	}
	try {
		$seo = YoastSEO();
		if ( ! is_object( $seo ) || ! isset( $seo->classes ) || ! is_object( $seo->classes ) || ! method_exists( $seo->classes, 'get' ) ) {
			return;
		}
		$instance = $seo->classes->get( $class );
		if ( is_object( $instance ) ) {
			remove_action( 'template_redirect', array( $instance, 'clean_permalinks' ), 1 );
		}
	} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- Optional Yoast DI; ignore failures.
		unset( $e );
	} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- PHP 7+; ignore DI failures.
		unset( $e );
	}
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
		$new = tsolo_add_query_arg( $query, $new );
	}
	return $new;
}

/**
 * Keep trailing slash on pretty login URLs after core wp_login_url() rebuilds the query.
 *
 * Core uses add_query_arg() which strips /login/ → /login?redirect_to=…; LiteSpeed/Yoast
 * then fight over the slash or query cleanup.
 *
 * @param string $login_url    Login URL.
 * @param string $redirect     Redirect target.
 * @param bool   $force_reauth Force reauth.
 * @return string
 */
function tsolo_filter_login_url( $login_url, $redirect = '', $force_reauth = false ) {
	unset( $redirect, $force_reauth );
	return tsolo_preserve_pretty_path_slash( (string) $login_url );
}

/**
 * Whether the front request is already HTTPS (incl. common reverse-proxy headers).
 *
 * Prevents force_ssl_admin() → https://… loops when PHP still sees HTTP behind
 * Cloudflare / LiteSpeed / nginx TLS termination.
 *
 * @return bool
 */
function tsolo_request_is_ssl() {
	if ( is_ssl() ) {
		return true;
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Compared after sanitize.
		$proto = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) );
		if ( 'https' === $proto || 0 === strpos( $proto, 'https,' ) ) {
			return true;
		}
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Compared after sanitize.
		$fwd = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) );
		if ( 'on' === $fwd || '1' === $fwd ) {
			return true;
		}
	}
	return false;
}

/**
 * Restore a trailing slash for directory-style front URLs (with or without "?…").
 *
 * Skips URLs that already end in "/", bare hosts, and file-like paths (*.php, etc.).
 *
 * @param string $url URL that may contain a query string and/or fragment.
 * @return string
 */
function tsolo_preserve_pretty_path_slash( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return $url;
	}

	$fragment = '';
	if ( false !== strpos( $url, '#' ) ) {
		$bits     = explode( '#', $url, 2 );
		$url      = $bits[0];
		$fragment = '#' . ( isset( $bits[1] ) ? $bits[1] : '' );
	}

	$query = '';
	$path  = $url;
	if ( false !== strpos( $url, '?' ) ) {
		$bits  = explode( '?', $url, 2 );
		$path  = $bits[0];
		$query = '?' . ( isset( $bits[1] ) ? $bits[1] : '' );
	}

	if ( '' === $path || '/' === substr( $path, -1 ) || preg_match( '#\.[a-zA-Z0-9]+$#', $path ) ) {
		return $path . $query . $fragment;
	}
	global $wp_rewrite;
	if ( $wp_rewrite instanceof WP_Rewrite && ! $wp_rewrite->use_trailing_slashes ) {
		return $path . $query . $fragment;
	}

	$parsed = function_exists( 'wp_parse_url' ) ? wp_parse_url( $path ) : parse_url( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Fallback outside WP.
	$ppath  = ( is_array( $parsed ) && isset( $parsed['path'] ) ) ? (string) $parsed['path'] : '';
	if ( '' !== $ppath && '/' !== $ppath ) {
		$path .= '/';
	}

	return $path . $query . $fragment;
}

/**
 * Like add_query_arg(), but keep a trailing slash before "?" on directory URLs.
 *
 * Core add_query_arg() strips the trailing slash. Servers that force trailing
 * slashes (LiteSpeed, nginx, etc.) then bounce /login?x ↔ /login/?x forever
 * (ERR_TOO_MANY_REDIRECTS) — common after logout → login?loggedout=true.
 *
 * @param string|array $key   Query key, or associative array of keys => values.
 * @param mixed        $value Value when $key is a string; base URL when $key is an array.
 * @param string|false $url   Base URL when $key is a string.
 * @return string
 */
function tsolo_add_query_arg( $key, $value = false, $url = false ) {
	if ( is_array( $key ) ) {
		$out = add_query_arg( $key, $value );
	} else {
		$out = add_query_arg( $key, $value, $url );
	}
	return is_string( $out ) ? tsolo_preserve_pretty_path_slash( $out ) : $out;
}

/**
 * Like remove_query_arg(), keeping a trailing slash before remaining "?".
 *
 * @param string|array $key Query key(s) to remove.
 * @param bool|string  $url Base URL (false = current).
 * @return string
 */
function tsolo_remove_query_arg( $key, $url = false ) {
	$out = remove_query_arg( $key, $url );
	return is_string( $out ) ? tsolo_preserve_pretty_path_slash( $out ) : $out;
}

/**
 * Like wp_nonce_url(), keeping a trailing slash before "?".
 *
 * @param string     $url        URL.
 * @param int|string $action     Nonce action.
 * @param string     $name       Nonce query arg name.
 * @return string
 */
function tsolo_nonce_url( $url, $action = -1, $name = '_wpnonce' ) {
	$url = (string) $url;
	return tsolo_add_query_arg( $name, wp_create_nonce( $action ), $url );
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
		$url = tsolo_add_query_arg( 'redirect_to', $redirect, $url );
	}
	return tsolo_nonce_url( $url, 'log-out' );
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
		$new = tsolo_add_query_arg( 'redirect_to', $redirect, $new );
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
 * Called only from tsolo_registration_handler() with the ID returned by
 * register_new_user(); it is not attached to any hook.
 *
 * @param int $user_id ID of the account that was just created.
 */
function tsolo_set_new_user_password( $user_id ) {
	$user_id = absint( $user_id );
	if ( $user_id < 1 || ! get_userdata( $user_id ) ) {
		return;
	}
	$settings = tsolo_get_settings();
	if ( empty( $settings['user_passwords'] ) ) {
		return;
	}
	$pass1 = tsolo_get_request_password( 'pass1' );
	$pass2 = tsolo_get_request_password( 'pass2' );
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
	$pass1 = tsolo_get_request_password( 'pass1' );
	$pass2 = tsolo_get_request_password( 'pass2' );
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
 * Called only from tsolo_registration_handler() with the ID returned by
 * register_new_user(); it is not attached to any hook.
 *
 * @param int $user_id ID of the account that was just created.
 */
function tsolo_handle_auto_login( $user_id ) {
	$user_id = absint( $user_id );
	if ( $user_id < 1 || ! get_userdata( $user_id ) ) {
		return;
	}
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
	// Only self-registrations choose their own password; admin-created users still need their email.
	if ( ! empty( $settings['user_passwords'] ) && 'register_new_user' === current_action() ) {
		$notify = 'admin';
	}
	// Admin-created users are emailed from wp-admin: keep them in the plugin email scope.
	tsolo_email_scope( true );
	wp_send_new_user_notifications( $user_id, $notify );
	tsolo_email_scope( false );
}
