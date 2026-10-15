<?php
/**
 * TSO Login Organizer — storage and request helpers.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TSOLO_DB_SCHEMA' ) ) {
	define( 'TSOLO_DB_SCHEMA', 4 );
}

define( 'TSOLO_OPT_DB_SCHEMA', 'tso_login_organizer_db_schema' );
define( 'TSOLO_OPT_SETTINGS', 'tso_login_organizer_settings' );
define( 'TSOLO_OPT_MODULES', 'tso_login_organizer_modules' );
define( 'TSOLO_OPT_REDIRECTION', 'tso_login_organizer_redirection' );
define( 'TSOLO_OPT_MODERATION', 'tso_login_organizer_moderation' );
define( 'TSOLO_OPT_RESTRICTIONS', 'tso_login_organizer_restrictions' );
define( 'TSOLO_OPT_SECURITY', 'tso_login_organizer_security' );
define( 'TSOLO_OPT_CAPTCHA', 'tso_login_organizer_captcha' );
define( 'TSOLO_OPT_PROFILES', 'tso_login_organizer_profiles' );
define( 'TSOLO_OPT_NOTIFICATIONS', 'tso_login_organizer_notifications' );
define( 'TSOLO_OPT_TWO_FACTOR', 'tso_login_organizer_two_factor' );
define( 'TSOLO_OPT_SOCIAL', 'tso_login_organizer_social' );
define( 'TSOLO_OPT_AVATARS', 'tso_login_organizer_avatars' );
define( 'TSOLO_OPT_FAVORITES', 'tso_login_organizer_favorites' );
define( 'TSOLO_OPT_MAILCHIMP', 'tso_login_organizer_mailchimp' );
define( 'TSOLO_OPT_FIELDS', 'tso_login_organizer_fields' );
define( 'TSOLO_OPT_LOCKOUTS', 'tso_login_organizer_lockouts' );
define( 'TSOLO_OPT_ATTEMPTS', 'tso_login_organizer_attempts' );
define( 'TSOLO_OPT_TML_IMPORTED', 'tso_login_organizer_tml_imported' );
define( 'TSOLO_OPT_FLUSH_REWRITES', 'tso_login_organizer_flush_rewrites' );
define( 'TSOLO_USER_META_UI_LANG', 'tso_login_organizer_ui_lang' );
define( 'TSOLO_ADMIN_QUERY_SET_LANG', 'tsolo_set_lang' );

/**
 * Default core settings.
 *
 * @return array
 */
function tsolo_default_settings() {
	return array(
		'ajax'              => 0,
		'login_type'        => 'default',
		'registration_type' => 'default',
		'user_passwords'    => 0,
		'auto_login'        => 0,
		'texts'             => array(
			'login_heading'        => '',
			'login_button'         => '',
			'register_heading'     => '',
			'register_button'      => '',
			'lostpassword_heading' => '',
			'lostpassword_button'  => '',
			'resetpass_button'     => '',
			'reg_passmail'         => '',
		),
		'branding'          => array(
			'logo_url'       => '',
			'form_max_width' => 0,
			'extra_class'    => '',
		),
		'slugs'             => array(
			'login'         => 'login',
			'logout'        => 'logout',
			'register'      => 'register',
			'lostpassword'  => 'lostpassword',
			'resetpass'     => 'resetpass',
			'dashboard'     => 'dashboard',
			'confirmaction' => 'confirm-action',
			'profile'       => 'profile',
			'favorites'     => 'favorites',
		),
	);
}

/**
 * Default module enable flags (all OFF).
 *
 * @return array
 */
function tsolo_default_modules() {
	return array(
		'redirection'   => 0,
		'moderation'    => 0,
		'restrictions'  => 0,
		'security'      => 0,
		'captcha'       => 0,
		'fields'        => 0,
		'profiles'      => 0,
		'notifications' => 0,
		'two_factor'    => 0,
		'social'        => 0,
		'avatars'       => 0,
		'favorites'     => 0,
		'mailchimp'     => 0,
	);
}

/**
 * Run schema migration when needed.
 */
function tsolo_maybe_migrate_schema() {
	$current = absint( get_option( TSOLO_OPT_DB_SCHEMA, 0 ) );
	if ( $current >= TSOLO_DB_SCHEMA ) {
		return;
	}
	if ( false === get_option( TSOLO_OPT_SETTINGS, false ) ) {
		add_option( TSOLO_OPT_SETTINGS, tsolo_default_settings(), '', false );
	}
	if ( false === get_option( TSOLO_OPT_MODULES, false ) ) {
		add_option( TSOLO_OPT_MODULES, tsolo_default_modules(), '', false );
	}

	/*
	 * Do not rewrite stored slugs here. Theme My Login uses "confirmaction",
	 * and flushing now (plugins_loaded) would save rules before they exist.
	 * The flush runs on init, after rewrite rules are registered.
	 */
	update_option( TSOLO_OPT_FLUSH_REWRITES, 1, false );
	update_option( TSOLO_OPT_DB_SCHEMA, TSOLO_DB_SCHEMA, false );
}

/**
 * Paths that must never become action rewrite slugs (core WP / reserved).
 *
 * @return string[]
 */
function tsolo_reserved_action_slugs() {
	return array(
		'wp-admin',
		'wp-content',
		'wp-includes',
		'wp-json',
		'wp-login',
		'wp-cron',
		'xmlrpc',
		'feed',
		'comments',
		'trackback',
		'embed',
		'index',
		'page',
		'attachment',
		'category',
		'tag',
		'author',
		'search',
	);
}

/**
 * Whether a slug is reserved or otherwise unsafe for a virtual action URL.
 *
 * @param string $slug Candidate slug.
 * @return bool
 */
function tsolo_is_reserved_action_slug( $slug ) {
	$slug = strtolower( trim( (string) $slug, '/' ) );
	if ( '' === $slug ) {
		return true;
	}
	if ( in_array( $slug, tsolo_reserved_action_slugs(), true ) ) {
		return true;
	}
	// Purely numeric slugs collide with ?p= / pagination in some structures.
	if ( ctype_digit( $slug ) ) {
		return true;
	}
	return false;
}

/**
 * Get core settings (merged with defaults and enums re-validated).
 *
 * @return array
 */
function tsolo_get_settings() {
	$stored = get_option( TSOLO_OPT_SETTINGS, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	return tsolo_sanitize_settings( $stored );
}

/**
 * Update core settings.
 *
 * @param array $settings Settings.
 * @return bool
 */
function tsolo_update_settings( $settings ) {
	$clean = tsolo_sanitize_settings( $settings );
	wp_cache_delete( 'pages', 'tso-login-organizer' );
	return update_option( TSOLO_OPT_SETTINGS, $clean, false );
}

/**
 * Sanitize core settings array.
 *
 * @param array $settings Raw settings.
 * @return array
 */
function tsolo_sanitize_settings( $settings ) {
	$defaults = tsolo_default_settings();
	$out      = $defaults;
	if ( ! is_array( $settings ) ) {
		return $out;
	}
	$out['ajax']              = empty( $settings['ajax'] ) ? 0 : 1;
	$login_type               = isset( $settings['login_type'] ) ? sanitize_key( $settings['login_type'] ) : 'default';
	$out['login_type']        = in_array( $login_type, array( 'default', 'username', 'email' ), true ) ? $login_type : 'default';
	$reg_type                 = isset( $settings['registration_type'] ) ? sanitize_key( $settings['registration_type'] ) : 'default';
	$out['registration_type'] = in_array( $reg_type, array( 'default', 'email' ), true ) ? $reg_type : 'default';
	$out['user_passwords']    = empty( $settings['user_passwords'] ) ? 0 : 1;
	$out['auto_login']        = empty( $settings['auto_login'] ) ? 0 : 1;
	$out['texts']             = tsolo_sanitize_form_texts( isset( $settings['texts'] ) ? $settings['texts'] : array() );
	$out['branding']          = tsolo_sanitize_branding( isset( $settings['branding'] ) ? $settings['branding'] : array() );
	$out['slugs']             = $defaults['slugs'];
	if ( ! empty( $settings['slugs'] ) && is_array( $settings['slugs'] ) ) {
		foreach ( $defaults['slugs'] as $key => $default_slug ) {
			if ( isset( $settings['slugs'][ $key ] ) && ( is_string( $settings['slugs'][ $key ] ) || is_numeric( $settings['slugs'][ $key ] ) ) ) {
				$slug = sanitize_title( (string) $settings['slugs'][ $key ] );
				if ( '' === $slug || tsolo_is_reserved_action_slug( $slug ) ) {
					$slug = $default_slug;
				}
				$out['slugs'][ $key ] = $slug;
			}
		}
	}
	$out['slugs'] = tsolo_unique_action_slugs( $out['slugs'] );
	return $out;
}

/**
 * Sanitize custom form heading/button strings (empty = WordPress / plugin default).
 *
 * @param mixed $raw Raw.
 * @return array
 */
function tsolo_sanitize_form_texts( $raw ) {
	$defaults = tsolo_default_settings();
	$defaults = $defaults['texts'];
	$out      = $defaults;
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $defaults as $key => $empty ) {
		unset( $empty );
		if ( ! isset( $raw[ $key ] ) ) {
			continue;
		}
		$out[ $key ] = sanitize_text_field( (string) $raw[ $key ] );
	}
	return $out;
}

/**
 * Sanitize branding (logo, width, extra class).
 *
 * @param mixed $raw Raw.
 * @return array
 */
function tsolo_sanitize_branding( $raw ) {
	$defaults = tsolo_default_settings();
	$defaults = $defaults['branding'];
	$out      = $defaults;
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	$url = isset( $raw['logo_url'] ) ? esc_url_raw( trim( (string) $raw['logo_url'] ) ) : '';
	if ( $url && ! preg_match( '#^https?://#i', $url ) ) {
		$url = '';
	}
	$out['logo_url'] = $url;
	$width           = isset( $raw['form_max_width'] ) ? absint( $raw['form_max_width'] ) : 0;
	if ( $width > 0 && $width < 200 ) {
		$width = 200;
	}
	if ( $width > 1200 ) {
		$width = 1200;
	}
	$out['form_max_width'] = $width;
	$class                 = isset( $raw['extra_class'] ) ? sanitize_html_class( (string) $raw['extra_class'] ) : '';
	// Allow multiple classes separated by spaces.
	if ( isset( $raw['extra_class'] ) && is_string( $raw['extra_class'] ) ) {
		$parts = preg_split( '/\s+/', $raw['extra_class'] );
		$clean = array();
		if ( is_array( $parts ) ) {
			foreach ( $parts as $part ) {
				$part = sanitize_html_class( $part );
				if ( '' !== $part ) {
					$clean[] = $part;
				}
			}
		}
		$class = implode( ' ', array_slice( array_unique( $clean ), 0, 5 ) );
	}
	$out['extra_class'] = $class;
	return $out;
}

/**
 * Custom form text or fallback.
 *
 * @param string $key      texts key.
 * @param string $fallback Default label.
 * @return string
 */
function tsolo_get_form_text( $key, $fallback = '' ) {
	$settings = tsolo_get_settings();
	$texts    = isset( $settings['texts'] ) && is_array( $settings['texts'] ) ? $settings['texts'] : array();
	$key      = sanitize_key( $key );
	if ( isset( $texts[ $key ] ) && '' !== trim( (string) $texts[ $key ] ) ) {
		return (string) $texts[ $key ];
	}
	return $fallback;
}

/**
 * Keep one path per action. The first action keeps the slug.
 *
 * @param array $slugs Slug map.
 * @return array
 */
function tsolo_unique_action_slugs( $slugs ) {
	$defaults = tsolo_default_settings();
	$defaults = $defaults['slugs'];
	$used     = array();
	if ( ! is_array( $slugs ) ) {
		return $defaults;
	}
	foreach ( $slugs as $key => $slug ) {
		$slug = is_string( $slug ) || is_numeric( $slug ) ? (string) $slug : '';
		if ( '' === $slug || tsolo_is_reserved_action_slug( $slug ) ) {
			$slug = isset( $defaults[ $key ] ) ? $defaults[ $key ] : sanitize_key( (string) $key );
		}
		if ( isset( $used[ $slug ] ) || tsolo_is_reserved_action_slug( $slug ) ) {
			$fallback = isset( $defaults[ $key ] ) ? $defaults[ $key ] : sanitize_key( (string) $key );
			if ( isset( $used[ $fallback ] ) || tsolo_is_reserved_action_slug( $fallback ) ) {
				$fallback = $fallback . '-' . sanitize_key( (string) $key );
			}
			$slug = $fallback;
			$n    = 2;
			while ( isset( $used[ $slug ] ) || tsolo_is_reserved_action_slug( $slug ) ) {
				$slug = $fallback . '-' . $n;
				++$n;
			}
		}
		$used[ $slug ] = $key;
		$slugs[ $key ] = $slug;
	}
	return $slugs;
}

/**
 * Get module flags.
 *
 * @return array
 */
function tsolo_get_modules() {
	$stored = get_option( TSOLO_OPT_MODULES, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	return array_merge( tsolo_default_modules(), $stored );
}

/**
 * Whether a module is enabled.
 *
 * @param string $module Module key.
 * @return bool
 */
function tsolo_is_module_enabled( $module ) {
	$modules = tsolo_get_modules();
	$key     = sanitize_key( $module );
	return ! empty( $modules[ $key ] );
}

/**
 * Update module flags.
 *
 * @param array $modules Module flags.
 * @return bool
 */
function tsolo_update_modules( $modules ) {
	$defaults = tsolo_default_modules();
	$out      = $defaults;
	if ( is_array( $modules ) ) {
		foreach ( $defaults as $key => $default ) {
			$out[ $key ] = empty( $modules[ $key ] ) ? 0 : 1;
		}
	}
	return update_option( TSOLO_OPT_MODULES, $out, false );
}

/**
 * Get a named option blob with defaults.
 *
 * @param string $option_name Option name constant value.
 * @param array  $defaults    Defaults.
 * @return array
 */
function tsolo_get_option_array( $option_name, $defaults = array() ) {
	$stored = get_option( $option_name, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	return array_merge( $defaults, $stored );
}

/**
 * Update a named option blob after map_deep sanitize_text_field.
 *
 * @param string $option_name Option name.
 * @param array  $value       Value.
 * @return bool
 */
function tsolo_update_option_array( $option_name, $value ) {
	if ( ! is_array( $value ) ) {
		$value = array();
	}
	$value = map_deep( $value, 'sanitize_text_field' );
	return update_option( $option_name, $value, false );
}

/**
 * Admin UI language preference (ca|es|en).
 *
 * @param int $user_id User ID; 0 = current.
 * @return string
 */
function tsolo_get_ui_lang( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$saved   = get_user_meta( $user_id, TSOLO_USER_META_UI_LANG, true );
	if ( in_array( $saved, array( 'ca', 'es', 'en' ), true ) ) {
		return $saved;
	}
	$locale = '';
	if ( function_exists( 'get_user_locale' ) ) {
		$locale = $user_id ? get_user_locale( $user_id ) : get_user_locale();
	}
	if ( '' === $locale && function_exists( 'get_locale' ) ) {
		$locale = get_locale();
	}
	$prefix = strtolower( substr( (string) $locale, 0, 2 ) );
	if ( in_array( $prefix, array( 'ca', 'es', 'en' ), true ) ) {
		return $prefix;
	}
	return 'en';
}

/**
 * Persist admin UI language.
 *
 * @param string $lang    ca|es|en.
 * @param int    $user_id User ID; 0 = current.
 */
function tsolo_set_ui_lang( $lang, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$lang    = sanitize_key( (string) $lang );
	if ( ! in_array( $lang, array( 'ca', 'es', 'en' ), true ) ) {
		return;
	}
	update_user_meta( $user_id, TSOLO_USER_META_UI_LANG, $lang );
}

/**
 * Pick CA / ES / EN string for admin chrome (donate, etc.).
 *
 * @param string $lang UI lang.
 * @param string $ca   Catalan.
 * @param string $es   Spanish.
 * @param string $en   English.
 * @return string
 */
function tsolo_ui_triple_text( $lang, $ca, $es, $en ) {
	if ( 'es' === $lang ) {
		return $es;
	}
	if ( 'en' === $lang ) {
		return $en;
	}
	return $ca;
}

/**
 * Ko-fi donation URL.
 *
 * @return string
 */
function tsolo_get_kofi_donate_url() {
	/**
	 * Filter the Ko-fi donation URL shown in the admin header.
	 *
	 * @param string $url Donation page URL.
	 */
	return (string) apply_filters( 'tsolo_kofi_donate_url', 'https://ko-fi.com/deadko_cat' );
}

/**
 * Read sanitized admin GET query arg (after nonce verify when used for writes).
 *
 * @param string $key           Query key.
 * @param string $default_value Default.
 * @return string
 */
function tsolo_get_admin_query_arg( $key, $default_value = '' ) {
	$key = (string) $key;
	if ( '' === $key || ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Caller verifies nonce when mutating.
		return $default_value;
	}
	return sanitize_text_field( (string) wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Caller verifies nonce when mutating.
}

/**
 * Apply plugin admin UI locale from user preference and reload textdomain.
 *
 * @param string $lang ca|es|en.
 */
function tsolo_apply_admin_ui_locale( $lang ) {
	static $applied = null;

	$map  = array(
		'ca' => 'ca',
		'es' => 'es_ES',
		'en' => 'en_US',
	);
	$lang = in_array( $lang, array( 'ca', 'es', 'en' ), true ) ? $lang : 'en';
	if ( $applied === $lang ) {
		return;
	}
	$applied = $lang;

	$controller = class_exists( 'WP_Translation_Controller', false ) ? WP_Translation_Controller::get_instance() : null;
	$lookup     = ( $controller && '' !== $controller->get_locale() ) ? $controller->get_locale() : '';
	if ( '' === $lookup ) {
		$lookup = function_exists( 'determine_locale' ) ? determine_locale() : 'en_US';
	}

	/*
	 * WordPress 6.5+ keeps every locale in the translation controller and
	 * returns the first file for the locale it is already using. A reloadable
	 * unload leaves Spanish in front of Catalan, and switch_to_locale( 'ca' )
	 * fails when that language pack is not installed. Attach this plugin's
	 * catalog to the locale WordPress will actually query, then put the
	 * controller locale back. load_textdomain() changes it as a side effect.
	 */
	unload_textdomain( 'tso-login-organizer' );

	if ( 'en' !== $lang ) {
		$ui_locale = $map[ $lang ];
		$mofile    = TSOLO_PATH . 'languages/tso-login-organizer-' . $ui_locale . '.mo';
		if ( is_readable( $mofile ) ) {
			load_textdomain( 'tso-login-organizer', $mofile, $lookup );
			if ( $lookup !== $ui_locale ) {
				load_textdomain( 'tso-login-organizer', $mofile, $ui_locale );
			}
		}
	}

	if ( $controller ) {
		$controller->set_locale( $lookup );
	}
}

/**
 * Verify admin form nonce.
 *
 * @param string $query_arg Request key.
 * @return bool
 */
function tsolo_verify_admin_form_nonce( $query_arg = '_wpnonce' ) {
	$query_arg = (string) $query_arg;
	$nonce     = isset( $_REQUEST[ $query_arg ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading nonce value only.
	if ( '' === $nonce ) {
		return false;
	}
	return (bool) wp_verify_nonce( $nonce, TSOLO_NONCE_FORM );
}

/**
 * Verify AJAX nonce.
 *
 * @return bool
 */
function tsolo_verify_ajax_nonce() {
	$nonce = isset( $_REQUEST['_ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_ajax_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading nonce value only.
	if ( '' === $nonce ) {
		return false;
	}
	return (bool) wp_verify_nonce( $nonce, TSOLO_NONCE_AJAX );
}

/**
 * Read sanitized admin POST text (after form nonce verify).
 *
 * @param string $key           POST key.
 * @param string $default_value Default.
 * @return string
 */
function tsolo_get_admin_post_text( $key, $default_value = '' ) {
	$key = (string) $key;
	if ( '' === $key || ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
		return $default_value;
	}
	return sanitize_text_field( (string) wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
}

/**
 * Whether an admin POST key is set (after form nonce verify).
 *
 * @param string $key POST key.
 * @return bool
 */
function tsolo_admin_post_is_set( $key ) {
	return isset( $_POST[ (string) $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
}

/**
 * Read admin POST array (after form nonce verify).
 *
 * @param string $key POST key.
 * @return array
 */
function tsolo_get_admin_post_array( $key ) {
	$key = (string) $key;
	if ( '' === $key || ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
		return array();
	}
	return map_deep( wp_unslash( $_POST[ $key ] ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
}

/**
 * Read admin POST textarea (after form nonce verify).
 *
 * @param string $key           POST key.
 * @param string $default_value Default.
 * @return string
 */
function tsolo_get_admin_post_textarea( $key, $default_value = '' ) {
	$key = (string) $key;
	if ( '' === $key || ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
		return $default_value;
	}
	return sanitize_textarea_field( (string) wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified via tsolo_verify_admin_form_nonce().
}

/**
 * Read frontend request text (login forms mirror wp-login.php; no WP nonce).
 *
 * @param string $key           Key.
 * @param string $method        get|post|request.
 * @param string $default_value Default.
 * @return string
 */
function tsolo_get_request_text( $key, $method = 'request', $default_value = '' ) {
	$key    = (string) $key;
	$method = strtolower( (string) $method );
	$src    = null;
	if ( 'post' === $method ) {
		$src = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below; frontend auth forms.
	} elseif ( 'get' === $method ) {
		$src = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
	} else {
		$src = isset( $_REQUEST[ $key ] ) ? wp_unslash( $_REQUEST[ $key ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
	}
	if ( null === $src || is_array( $src ) ) {
		return $default_value;
	}
	return sanitize_text_field( (string) $src );
}

/**
 * Whether a frontend POST key is set.
 *
 * @param string $key Key.
 * @return bool
 */
function tsolo_request_post_is_set( $key ) {
	return isset( $_POST[ (string) $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Frontend auth forms; caller validates credentials.
}

/**
 * Plugin uploads base dir under wp_upload_dir.
 *
 * @return array{basedir:string,baseurl:string}|WP_Error
 */
function tsolo_get_uploads_dir() {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'tsolo_upload', $upload['error'] );
	}
	$dir = trailingslashit( $upload['basedir'] ) . 'tso-login-organizer';
	$url = trailingslashit( $upload['baseurl'] ) . 'tso-login-organizer';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		$index = trailingslashit( $dir ) . 'index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Non-executable guard file in plugin uploads tree.
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}
	return array(
		'basedir' => $dir,
		'baseurl' => $url,
	);
}
