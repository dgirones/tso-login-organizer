<?php
/**
 * Action registry and handlers.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Internal action registry store.
 *
 * @param array|null $set Optional replacement registry.
 * @return array
 */
function tsolo_actions_store( $set = null ) {
	static $registry = array();
	if ( null !== $set ) {
		$registry = $set;
	}
	return $registry;
}

/**
 * Register an action.
 *
 * @param string|TSOLO_Action $action Action.
 * @param array               $args   Args.
 * @return TSOLO_Action
 */
function tsolo_register_action( $action, $args = array() ) {
	if ( ! $action instanceof TSOLO_Action ) {
		$action = new TSOLO_Action( $action, $args );
	}

	$settings = tsolo_get_settings();
	$name     = $action->get_name();
	if ( ! empty( $settings['slugs'][ $name ] ) ) {
		$action->set_slug( $settings['slugs'][ $name ] );
	}

	$registry          = tsolo_actions_store();
	$registry[ $name ] = $action;
	tsolo_actions_store( $registry );
	$action->add_callback_hook();

	do_action( 'tsolo_registered_action', $name, $action );
	return $action;
}

/**
 * Get all actions.
 *
 * @return array
 */
function tsolo_get_actions() {
	return tsolo_actions_store();
}

/**
 * Get one action.
 *
 * @param string $action Action name or empty for current.
 * @return TSOLO_Action|false
 */
function tsolo_get_action( $action = '' ) {
	global $wp;
	if ( $action instanceof TSOLO_Action ) {
		return $action;
	}
	if ( '' === $action || null === $action ) {
		if ( ! empty( $wp->query_vars['action'] ) ) {
			$action = $wp->query_vars['action'];
		} else {
			return false;
		}
	}
	$actions = tsolo_get_actions();
	return isset( $actions[ $action ] ) ? $actions[ $action ] : false;
}

/**
 * Whether action exists.
 *
 * @param string $action Action name.
 * @return bool
 */
function tsolo_action_exists( $action ) {
	$actions = tsolo_get_actions();
	return isset( $actions[ $action ] );
}

/**
 * Whether current request is a TSOLO action.
 *
 * @param string $action Optional action name.
 * @return bool
 */
function tsolo_is_action( $action = '' ) {
	$current = tsolo_get_action();
	if ( ! $current ) {
		return false;
	}
	if ( '' === $action ) {
		return true;
	}
	return $current->get_name() === $action;
}

/**
 * Get action title.
 *
 * @param string $action Action.
 * @return string
 */
function tsolo_get_action_title( $action = '' ) {
	$obj = tsolo_get_action( $action );
	return $obj ? $obj->get_title() : '';
}

/**
 * Get action slug.
 *
 * @param string $action Action.
 * @return string
 */
function tsolo_get_action_slug( $action = '' ) {
	$obj = tsolo_get_action( $action );
	return $obj ? $obj->get_slug() : '';
}

/**
 * Get action URL.
 *
 * @param string $action Action.
 * @param string $scheme Scheme.
 * @return string
 */
function tsolo_get_action_url( $action = '', $scheme = 'login' ) {
	$obj = tsolo_get_action( $action );
	return $obj ? $obj->get_url( $scheme ) : '';
}

/**
 * Find WP page matching action slug (legacy TML pages; not required for front-end).
 *
 * @param string $action Action.
 * @return WP_Post|false
 */
function tsolo_action_has_page( $action = '' ) {
	$obj = tsolo_get_action( $action );
	if ( ! $obj ) {
		return false;
	}
	$pages    = tsolo_get_wp_pages_matching_slugs();
	$filtered = wp_list_filter( $pages, array( 'post_name' => $obj->get_slug() ) );
	if ( $filtered ) {
		return reset( $filtered );
	}
	return false;
}

/**
 * WordPress pages left over from Theme My Login / login forms.
 *
 * Detects by: current + known TML slugs, TML option page IDs, and pages whose
 * content contains [theme-my-login] or [tso-login-organizer].
 *
 * @return WP_Post[]
 */
function tsolo_get_wp_pages_matching_slugs() {
	$cached = wp_cache_get( 'pages', 'tso-login-organizer' );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : array();
	}

	$statuses = array( 'publish', 'draft', 'private', 'pending' );
	$by_id    = array();

	$slugs = function_exists( 'tsolo_known_tml_page_slugs' ) ? tsolo_known_tml_page_slugs() : array();
	if ( ! empty( $slugs ) ) {
		$by_slug = get_posts(
			array(
				'post_name__in'          => $slugs,
				'post_type'              => 'page',
				'post_status'            => $statuses,
				'nopaging'               => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( is_array( $by_slug ) ) {
			foreach ( $by_slug as $page ) {
				$by_id[ (int) $page->ID ] = $page;
			}
		}
	}

	$stored_ids = function_exists( 'tsolo_get_tml_stored_page_ids' ) ? tsolo_get_tml_stored_page_ids() : array();
	if ( ! empty( $stored_ids ) ) {
		$by_stored = get_posts(
			array(
				'post__in'               => $stored_ids,
				'post_type'              => 'page',
				'post_status'            => $statuses,
				'nopaging'               => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( is_array( $by_stored ) ) {
			foreach ( $by_stored as $page ) {
				$by_id[ (int) $page->ID ] = $page;
			}
		}
	}

	global $wpdb;
	$like_tml = '%' . $wpdb->esc_like( '[theme-my-login' ) . '%';
	$like_tso = '%' . $wpdb->esc_like( '[tso-login-organizer' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin detection of leftover TML shortcode pages.
	$shortcode_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'page'
			AND post_status IN ( 'publish', 'draft', 'private', 'pending' )
			AND ( post_content LIKE %s OR post_content LIKE %s )",
			$like_tml,
			$like_tso
		)
	);
	// Page builders often store the shortcode in postmeta instead of post_content.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin detection via builder meta.
	$meta_ids      = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
			WHERE meta_value LIKE %s OR meta_value LIKE %s",
			$like_tml,
			$like_tso
		)
	);
	$shortcode_ids = array_merge(
		is_array( $shortcode_ids ) ? $shortcode_ids : array(),
		is_array( $meta_ids ) ? $meta_ids : array()
	);
	if ( ! empty( $shortcode_ids ) ) {
		$shortcode_ids = array_values( array_unique( array_map( 'absint', $shortcode_ids ) ) );
		$missing       = array_values( array_diff( $shortcode_ids, array_keys( $by_id ) ) );
		if ( ! empty( $missing ) ) {
			$by_shortcode = get_posts(
				array(
					'post__in'               => $missing,
					'post_type'              => 'page',
					'post_status'            => $statuses,
					'nopaging'               => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			if ( is_array( $by_shortcode ) ) {
				foreach ( $by_shortcode as $page ) {
					$by_id[ (int) $page->ID ] = $page;
				}
			}
		}
	}

	$pages = array_values( $by_id );
	usort(
		$pages,
		static function ( $a, $b ) {
			return strcasecmp( (string) $a->post_name, (string) $b->post_name );
		}
	);

	wp_cache_set( 'pages', $pages, 'tso-login-organizer' );
	return $pages;
}

/**
 * Register default actions.
 */
function tsolo_register_default_actions() {
	$checkemail = tsolo_get_request_text( 'checkemail', 'get' );

	tsolo_register_action(
		'dashboard',
		array(
			'title'              => __( 'Dashboard', 'tso-login-organizer' ),
			'slug'               => 'dashboard',
			'callback'           => 'tsolo_dashboard_handler',
			'show_on_forms'      => false,
			'show_nav_menu_item' => is_user_logged_in(),
		)
	);

	tsolo_register_action(
		'login',
		array(
			'title'              => $checkemail ? __( 'Check your email', 'tso-login-organizer' ) : __( 'Log In', 'tso-login-organizer' ),
			'slug'               => 'login',
			'callback'           => 'tsolo_login_handler',
			'ajax_callback'      => 'tsolo_login_handler',
			'show_on_forms'      => __( 'Log in', 'tso-login-organizer' ),
			'show_nav_menu_item' => ! is_user_logged_in(),
		)
	);

	tsolo_register_action(
		'logout',
		array(
			'title'              => __( 'Log Out', 'tso-login-organizer' ),
			'slug'               => 'logout',
			'callback'           => 'tsolo_logout_handler',
			'show_on_forms'      => false,
			'show_in_widget'     => false,
			'show_nav_menu_item' => is_user_logged_in(),
		)
	);

	tsolo_register_action(
		'register',
		array(
			'title'              => __( 'Register', 'tso-login-organizer' ),
			'slug'               => 'register',
			'callback'           => 'tsolo_registration_handler',
			'ajax_callback'      => 'tsolo_registration_handler',
			'show_on_forms'      => (bool) get_option( 'users_can_register' ),
			'show_nav_menu_item' => ! is_user_logged_in(),
		)
	);

	tsolo_register_action(
		'lostpassword',
		array(
			'title'             => __( 'Lost Password', 'tso-login-organizer' ),
			'slug'              => 'lostpassword',
			'callback'          => 'tsolo_lost_password_handler',
			'ajax_callback'     => 'tsolo_lost_password_handler',
			'show_on_forms'     => __( 'Lost your password?', 'tso-login-organizer' ),
			'show_in_nav_menus' => false,
		)
	);

	tsolo_register_action(
		'resetpass',
		array(
			'title'             => __( 'Reset Password', 'tso-login-organizer' ),
			'slug'              => 'resetpass',
			'callback'          => 'tsolo_password_reset_handler',
			'show_on_forms'     => false,
			'show_in_widget'    => false,
			'show_in_nav_menus' => false,
		)
	);

	tsolo_register_action(
		'confirmaction',
		array(
			'title'                 => __( 'Your Data Request', 'tso-login-organizer' ),
			'slug'                  => 'confirm-action',
			'callback'              => 'tsolo_confirmaction_handler',
			'show_on_forms'         => false,
			'show_in_widget'        => false,
			'show_in_nav_menus'     => false,
			'show_in_slug_settings' => false,
		)
	);

	if ( tsolo_is_module_enabled( 'profiles' ) ) {
		tsolo_register_action(
			'profile',
			array(
				'title'              => __( 'Profile', 'tso-login-organizer' ),
				'slug'               => 'profile',
				'callback'           => 'tsolo_profile_handler',
				'show_on_forms'      => false,
				'show_nav_menu_item' => is_user_logged_in(),
			)
		);
	}

	if ( tsolo_is_module_enabled( 'favorites' ) ) {
		tsolo_register_action(
			'favorites',
			array(
				'title'              => __( 'Favorites', 'tso-login-organizer' ),
				'slug'               => 'favorites',
				'callback'           => 'tsolo_favorites_handler',
				'show_on_forms'      => false,
				'show_nav_menu_item' => is_user_logged_in(),
			)
		);
	}
}

/**
 * Dispatch action handler.
 */
function tsolo_action_handler() {
	if ( ! tsolo_is_action() ) {
		return;
	}

	if ( force_ssl_admin() && ! is_ssl() ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used only for redirect path.
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		if ( 0 === strpos( $request_uri, 'http' ) ) {
			wp_safe_redirect( set_url_scheme( esc_url_raw( $request_uri ), 'https' ) );
		} else {
			wp_safe_redirect( 'https://' . $host . $request_uri );
		}
		exit;
	}

	$action_param = tsolo_get_request_text( 'action', 'request' );
	if ( $action_param && tsolo_is_action( 'login' ) && 'login' !== $action_param ) {
		if ( 'retrievepassword' === $action_param ) {
			$action_param = 'lostpassword';
		} elseif ( 'rp' === $action_param ) {
			$action_param = 'resetpass';
		}
		$url = tsolo_get_action_url( $action_param );
		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	nocache_headers();

	$secure = ( 'https' === wp_parse_url( wp_login_url(), PHP_URL_SCHEME ) );
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure );
	if ( SITECOOKIEPATH !== COOKIEPATH ) {
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login hook (wp-login.php parity).
	do_action( 'login_init' );
	$action_name = tsolo_get_action()->get_name();
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login hook (wp-login.php parity).
	do_action( "login_form_{$action_name}" );

	if ( tsolo_is_ajax_request() ) {
		do_action( "tsolo_action_ajax_{$action_name}" );
	} else {
		do_action( "tsolo_action_{$action_name}" );
	}
}

/**
 * Dashboard handler.
 */
function tsolo_dashboard_handler() {
	if ( ! is_user_logged_in() ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		wp_safe_redirect( wp_login_url( $request_uri ) );
		exit;
	}
}

/**
 * Login handler.
 */
function tsolo_login_handler() {
	$errors             = new WP_Error();
	$secure_cookie      = '';
	$credentials_posted = tsolo_request_post_is_set( 'log' ) || tsolo_request_post_is_set( 'pwd' );
	$reauth_early       = (bool) tsolo_get_request_text( 'reauth', 'request' );

	// Already signed in: show account links via shortcode (no form) unless reauth is forced.
	if ( is_user_logged_in() && ! $reauth_early && ! $credentials_posted ) {
		return;
	}

	if ( tsolo_request_post_is_set( 'log' ) && ! force_ssl_admin() ) {
		$user_name = sanitize_user( tsolo_get_request_text( 'log', 'post' ) );
		$user      = get_user_by( 'login', $user_name );
		if ( ! $user && strpos( $user_name, '@' ) ) {
			$user = get_user_by( 'email', $user_name );
		}
		if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
			$secure_cookie = true;
			force_ssl_admin( true );
		}
	}

	$redirect_to = tsolo_get_request_text( 'redirect_to', 'request' );
	if ( '' === $redirect_to ) {
		$redirect_to = admin_url();
	} elseif ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
		$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
	}

	$reauth = $reauth_early;
	$user   = new WP_Error();

	if ( $credentials_posted ) {
		$user = wp_signon( array(), $secure_cookie );

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login_redirect filter.
		$redirect_to = apply_filters( 'login_redirect', $redirect_to, tsolo_get_request_text( 'redirect_to', 'request' ), $user );

		if ( ! is_wp_error( $user ) && ! $reauth ) {
			if ( ( empty( $redirect_to ) || 'wp-admin/' === $redirect_to || admin_url() === $redirect_to ) ) {
				if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
					$redirect_to = user_admin_url();
				} elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
					$redirect_to = get_dashboard_url( $user->ID );
				} elseif ( ! $user->has_cap( 'edit_posts' ) ) {
					if ( tsolo_action_exists( 'dashboard' ) ) {
						$redirect_to = tsolo_get_action_url( 'dashboard' );
					} else {
						$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url( '/' );
					}
				}
			}
			if ( tsolo_is_ajax_request() ) {
				tsolo_send_ajax_success( array( 'redirect' => tsolo_validate_redirect( $redirect_to ) ) );
			}
			wp_safe_redirect( $redirect_to );
			exit;
		}

		$errors = is_wp_error( $user ) ? $user : new WP_Error();
	}

	$has_status_query = (
		tsolo_get_request_text( 'loggedout', 'get' )
		|| tsolo_get_request_text( 'checkemail', 'get' )
		|| tsolo_get_request_text( 'registration', 'get' )
		|| tsolo_get_request_text( 'resetpass', 'get' )
	);

	if ( $has_status_query || $reauth ) {
		$errors = new WP_Error();
	}

	if ( tsolo_get_request_text( 'loggedout', 'get' ) ) {
		$errors->add( 'loggedout', __( 'You are now logged out.', 'tso-login-organizer' ), 'message' );
	} elseif ( 'disabled' === tsolo_get_request_text( 'registration', 'get' ) ) {
		$errors->add( 'registerdisabled', __( '<strong>Error:</strong> User registration is currently not allowed.', 'tso-login-organizer' ) );
	} elseif ( 'confirm' === tsolo_get_request_text( 'checkemail', 'get' ) ) {
		$errors->add( 'confirm', __( 'Check your email for the confirmation link.', 'tso-login-organizer' ), 'message' );
	} elseif ( 'registered' === tsolo_get_request_text( 'checkemail', 'get' ) ) {
		$errors->add( 'registered', __( 'Registration complete. You may now log in.', 'tso-login-organizer' ), 'message' );
	} elseif ( 'moderate' === tsolo_get_request_text( 'checkemail', 'get' ) ) {
		$errors->add( 'moderate', __( 'Registration received. You are not signed in yet: confirm your email and/or wait until an administrator approves the account.', 'tso-login-organizer' ), 'message' );
	} elseif ( 'complete' === tsolo_get_request_text( 'resetpass', 'get' ) ) {
		$errors->add( 'password_reset', __( 'Your password has been reset.', 'tso-login-organizer' ), 'message' );
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core wp_login_errors filter.
	$errors = apply_filters( 'wp_login_errors', $errors, $redirect_to );
	tsolo_set_errors( $errors );

	if ( $reauth ) {
		wp_clear_auth_cookie();
	}

	if ( tsolo_is_ajax_request() && $credentials_posted ) {
		tsolo_send_ajax_error( array( 'errors' => tsolo_render_errors_html( $errors ) ) );
	}
}

/**
 * Logout handler.
 */
function tsolo_logout_handler() {
	$redirect_to = tsolo_get_request_text( 'redirect_to', 'request' );
	$nonce       = tsolo_get_request_text( '_wpnonce', 'request' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'log-out' ) ) {
		wp_die( esc_html__( 'Invalid logout request.', 'tso-login-organizer' ), 403 );
	}
	wp_logout();
	if ( '' === $redirect_to ) {
		$redirect_to = add_query_arg( 'loggedout', 'true', tsolo_get_action_url( 'login' ) );
	}
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core logout_redirect filter.
	$redirect_to = apply_filters( 'logout_redirect', $redirect_to, tsolo_get_request_text( 'redirect_to', 'request' ), wp_get_current_user() );
	wp_safe_redirect( $redirect_to );
	exit;
}

/**
 * Registration handler.
 */
function tsolo_registration_handler() {
	if ( is_multisite() ) {
		wp_safe_redirect( network_site_url( 'wp-signup.php' ) );
		exit;
	}
	if ( ! get_option( 'users_can_register' ) ) {
		wp_safe_redirect( add_query_arg( 'registration', 'disabled', tsolo_get_action_url( 'login' ) ) );
		exit;
	}

	$errors = new WP_Error();
	if ( tsolo_request_post_is_set( 'user_login' ) || tsolo_request_post_is_set( 'user_email' ) ) {
		$user_login = tsolo_get_request_text( 'user_login', 'post' );
		$user_email = tsolo_get_request_text( 'user_email', 'post' );
		$result     = register_new_user( $user_login, $user_email );
		if ( ! is_wp_error( $result ) ) {
			$fallback = add_query_arg( 'checkemail', 'registered', tsolo_get_action_url( 'login' ) );
			if ( class_exists( 'TSOLO_Mod_Moderation', false ) && TSOLO_Mod_Moderation::blocks_auto_login() ) {
				$fallback = add_query_arg( 'checkemail', 'moderate', tsolo_get_action_url( 'login' ) );
			}
			$redirect = apply_filters( 'tsolo_registration_redirect', $fallback, $result );
			if ( tsolo_is_ajax_request() ) {
				tsolo_send_ajax_success( array( 'redirect' => $redirect ) );
			}
			wp_safe_redirect( $redirect );
			exit;
		}
		$errors = $result;
	}

	tsolo_set_errors( $errors );
	if ( tsolo_is_ajax_request() && $errors->has_errors() ) {
		tsolo_send_ajax_error( array( 'errors' => tsolo_render_errors_html( $errors ) ) );
	}
}

/**
 * Lost password handler.
 */
function tsolo_lost_password_handler() {
	$errors = new WP_Error();
	if ( tsolo_request_post_is_set( 'user_login' ) ) {
		$result = retrieve_password();
		if ( true === $result ) {
			$redirect = add_query_arg( 'checkemail', 'confirm', tsolo_get_action_url( 'login' ) );
			if ( tsolo_is_ajax_request() ) {
				tsolo_send_ajax_success( array( 'redirect' => $redirect ) );
			}
			wp_safe_redirect( $redirect );
			exit;
		}
		$errors = is_wp_error( $result ) ? $result : new WP_Error( 'error', __( 'Unable to send reset email.', 'tso-login-organizer' ) );
	}
	if ( 'invalidkey' === tsolo_get_request_text( 'error', 'get' ) ) {
		$errors->add( 'invalidkey', __( 'Your password reset link appears to be invalid.', 'tso-login-organizer' ) );
	}
	tsolo_set_errors( $errors );
	if ( tsolo_is_ajax_request() && $errors->has_errors() ) {
		tsolo_send_ajax_error( array( 'errors' => tsolo_render_errors_html( $errors ) ) );
	}
}

/**
 * Password reset handler.
 */
function tsolo_password_reset_handler() {
	list( $rp_path ) = explode( '?', wp_unslash( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cookie path only.
	$rp_cookie       = 'wp-resetpass-' . COOKIEHASH;

	if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core reset flow.
		$value = sprintf( '%s:%s', sanitize_user( wp_unslash( $_GET['login'] ) ), wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended -- Core-compatible reset cookie.
		setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_safe_redirect( remove_query_arg( array( 'key', 'login' ) ) );
		exit;
	}

	$rp_cookie_value = isset( $_COOKIE[ $rp_cookie ] ) ? wp_unslash( $_COOKIE[ $rp_cookie ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Core reset cookie format.
	if ( $rp_cookie_value && 0 < strpos( $rp_cookie_value, ':' ) ) {
		list( $rp_login, $rp_key ) = explode( ':', $rp_cookie_value, 2 );
		$user                      = check_password_reset_key( $rp_key, $rp_login );
	} else {
		$user = false;
	}

	if ( ! $user || is_wp_error( $user ) ) {
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_safe_redirect( add_query_arg( 'error', 'invalidkey', tsolo_get_action_url( 'lostpassword' ) ) );
		exit;
	}

	$errors = new WP_Error();
	if ( tsolo_request_post_is_set( 'pass1' ) && tsolo_request_post_is_set( 'pass2' ) ) {
		$pass1 = tsolo_get_request_text( 'pass1', 'post' );
		$pass2 = tsolo_get_request_text( 'pass2', 'post' );
		if ( $pass1 !== $pass2 ) {
			$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.', 'tso-login-organizer' ) );
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core validate_password_reset action.
		do_action( 'validate_password_reset', $errors, $user );
		if ( ! $errors->has_errors() && $pass1 ) {
			reset_password( $user, $pass1 );
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
			wp_safe_redirect( add_query_arg( 'resetpass', 'complete', tsolo_get_action_url( 'login' ) ) );
			exit;
		}
	}
	tsolo_set_errors( $errors );
}

/**
 * Privacy confirmaction handler.
 */
function tsolo_confirmaction_handler() {
	$request_id = absint( tsolo_get_request_text( 'request_id', 'get' ) );
	$confirm    = tsolo_get_request_text( 'confirm_key', 'get' );
	$result     = wp_validate_user_request_key( $request_id, $confirm );
	if ( is_wp_error( $result ) ) {
		tsolo_set_errors( $result );
	} else {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core privacy request confirmation hook.
		do_action( 'user_request_action_confirmed', $request_id );
	}
}

/**
 * Profile stub when module loaded via action registry before module file hooks.
 */
function tsolo_profile_handler() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( tsolo_get_action_url( 'profile' ) ) );
		exit;
	}
}

/**
 * Favorites stub handler.
 */
function tsolo_favorites_handler() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( tsolo_get_action_url( 'favorites' ) ) );
		exit;
	}
}

/**
 * Errors store.
 *
 * @param WP_Error|null $set Optional errors to store.
 * @return WP_Error
 */
function tsolo_errors_store( $set = null ) {
	static $errors = null;
	if ( null !== $set ) {
		$errors = $set instanceof WP_Error ? $set : new WP_Error();
	}
	if ( null === $errors ) {
		$errors = new WP_Error();
	}
	return $errors;
}

/**
 * Store errors for rendering.
 *
 * @param WP_Error $errors Errors.
 */
function tsolo_set_errors( $errors ) {
	tsolo_errors_store( $errors instanceof WP_Error ? $errors : new WP_Error() );
}

/**
 * Get stored errors.
 *
 * @return WP_Error
 */
function tsolo_get_errors() {
	return tsolo_errors_store();
}

/**
 * Render errors HTML.
 *
 * @param WP_Error|null $errors Errors.
 * @return string
 */
function tsolo_render_errors_html( $errors = null ) {
	if ( null === $errors ) {
		$errors = tsolo_get_errors();
	}
	if ( ! $errors instanceof WP_Error || ! $errors->has_errors() ) {
		return '';
	}
	$html = '<div class="tsolo-alerts">';
	foreach ( $errors->get_error_codes() as $code ) {
		$severity = 'message' === $errors->get_error_data( $code ) ? 'message' : 'error';
		foreach ( $errors->get_error_messages( $code ) as $message ) {
			$html .= '<div class="tsolo-alert tsolo-alert--' . esc_attr( $severity ) . '">' . wp_kses_post( $message ) . '</div>';
		}
	}
	$html .= '</div>';
	return $html;
}

/**
 * Validate redirect URL.
 *
 * @param string $url URL.
 * @return string
 */
function tsolo_validate_redirect( $url ) {
	$validated = wp_validate_redirect( $url, admin_url() );
	return $validated ? $validated : admin_url();
}
