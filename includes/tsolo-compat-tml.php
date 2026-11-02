<?php
/**
 * Theme My Login migration helpers.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Theme My Login is loaded on this request.
 *
 * @return bool
 */
function tsolo_tml_plugin_is_active() {
	return defined( 'THEME_MY_LOGIN_VERSION' )
		|| function_exists( 'theme_my_login' )
		|| class_exists( 'Theme_My_Login', false );
}

/**
 * Theme My Login 6.x option blob (site option first, then blog on multisite).
 *
 * @return array|null
 */
function tsolo_get_tml_legacy_option() {
	$value = get_site_option( 'theme_my_login', null );
	if ( is_array( $value ) ) {
		return $value;
	}
	if ( is_multisite() ) {
		$blog = get_option( 'theme_my_login', null );
		if ( is_array( $blog ) ) {
			return $blog;
		}
	}
	return null;
}

/**
 * Whether a Theme My Login option was saved on this site.
 *
 * @return bool
 */
function tsolo_tml_options_present() {
	foreach ( tsolo_tml_detect_keys() as $key ) {
		if ( null !== tsolo_get_tml_value( $key ) ) {
			return true;
		}
	}
	$legacy = tsolo_get_tml_legacy_option();
	return is_array( $legacy ) && ! empty( $legacy );
}

/**
 * Whether leftover-page detection should include our stored slug for an action.
 *
 * Core actions always. Profile / favorites only when the module is on, or after
 * a TML import that left a non-default slug (so old pages stay findable before
 * the module is enabled).
 *
 * @param string $key Action slug key.
 * @return bool
 */
function tsolo_tml_include_settings_slug_in_known( $key ) {
	$key      = sanitize_key( $key );
	$optional = array(
		'profile'   => 'profiles',
		'favorites' => 'favorites',
	);
	if ( ! isset( $optional[ $key ] ) ) {
		return true;
	}
	if ( function_exists( 'tsolo_is_module_enabled' ) && tsolo_is_module_enabled( $optional[ $key ] ) ) {
		return true;
	}
	if ( ! get_option( TSOLO_OPT_TML_IMPORTED ) ) {
		return false;
	}
	$defaults = tsolo_default_settings();
	$settings = tsolo_get_settings();
	$current  = isset( $settings['slugs'][ $key ] ) ? sanitize_title( (string) $settings['slugs'][ $key ] ) : '';
	$default  = isset( $defaults['slugs'][ $key ] ) ? sanitize_title( (string) $defaults['slugs'][ $key ] ) : '';
	return '' !== $current && $current !== $default;
}

/**
 * Slugs that currently occupy a virtual URL (and therefore clash with a real page).
 *
 * Always includes Theme My Login option slugs for every action (including profile
 * and favorites) so leftover pages stay detectable before those modules are on.
 *
 * @return string[]
 */
function tsolo_known_tml_page_slugs() {
	$slugs    = array();
	$settings = tsolo_get_settings();
	foreach ( tsolo_tml_slug_keys() as $key ) {
		if ( tsolo_tml_include_settings_slug_in_known( $key )
			&& ! empty( $settings['slugs'][ $key ] )
			&& ( is_string( $settings['slugs'][ $key ] ) || is_numeric( $settings['slugs'][ $key ] ) ) ) {
			$slug = sanitize_title( (string) $settings['slugs'][ $key ] );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
		$from_tml = tsolo_get_tml_value( 'tml_' . $key . '_slug' );
		if ( is_string( $from_tml ) || is_numeric( $from_tml ) ) {
			$slug = sanitize_title( (string) $from_tml );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
	}
	if ( function_exists( 'tsolo_legacy_slug_aliases' ) ) {
		foreach ( array_keys( tsolo_legacy_slug_aliases() ) as $alias ) {
			$slugs[] = $alias;
		}
	}
	$slugs = array_values( array_unique( array_filter( $slugs ) ) );
	return $slugs;
}

/**
 * Theme My Login option keys that mean the plugin was configured here.
 *
 * @return string[]
 */
function tsolo_tml_detect_keys() {
	return array(
		'tml_ajax',
		'tml_login_type',
		'tml_registration_type',
		'tml_user_passwords',
		'tml_auto_login',
		'_tml_version',
		'tml_login_slug',
		'tml_logout_slug',
		'tml_register_slug',
		'tml_lostpassword_slug',
		'tml_resetpass_slug',
		'tml_dashboard_slug',
		'tml_confirmaction_slug',
		'tml_profile_slug',
		'tml_favorites_slug',
	);
}

/**
 * Read a Theme My Login value from network options, then from this site.
 *
 * On a single site both stores are the same. After a single site is moved
 * into a network, the old values can still sit in the blog options.
 *
 * @param string $key Option key.
 * @return mixed|null Null when the option is not stored.
 */
function tsolo_get_tml_value( $key ) {
	$key   = (string) $key;
	$value = get_site_option( $key, null );
	if ( null !== $value ) {
		return $value;
	}
	if ( is_multisite() ) {
		$blog = get_option( $key, null );
		if ( null !== $blog ) {
			return $blog;
		}
	}
	return null;
}

/**
 * Map a Theme My Login checkbox to 1 or 0.
 *
 * The string "0" is off. Only explicit on-values enable the flag.
 *
 * @param mixed $value Stored value.
 * @return int
 */
function tsolo_tml_checkbox_flag( $value ) {
	if ( true === $value || 1 === $value || '1' === $value ) {
		return 1;
	}
	if ( is_string( $value ) ) {
		$value = strtolower( trim( $value ) );
		if ( in_array( $value, array( 'true', 'yes', 'on' ), true ) ) {
			return 1;
		}
	}
	return 0;
}

/**
 * Action slug keys copied from Theme My Login.
 *
 * @return string[]
 */
function tsolo_tml_slug_keys() {
	return array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'confirmaction', 'profile', 'favorites' );
}

/**
 * Collect numeric page IDs nested in a TML options array.
 *
 * @param mixed $data Option data.
 * @return int[]
 */
function tsolo_extract_page_ids_from_tml_option( $data ) {
	$ids = array();
	if ( ! is_array( $data ) ) {
		if ( tsolo_is_positive_page_id( $data ) ) {
			$ids[] = (int) $data;
		}
		return $ids;
	}
	foreach ( $data as $key => $value ) {
		if ( tsolo_is_positive_page_id( $value ) ) {
			if ( tsolo_is_tml_page_id_key( $key ) ) {
				$ids[] = (int) $value;
			}
			continue;
		}
		if ( is_array( $value ) ) {
			$ids = array_merge( $ids, tsolo_extract_page_ids_from_tml_option( $value ) );
		}
	}
	return $ids;
}

/**
 * Page IDs stored in Theme My Login options (legacy + site options).
 *
 * @return int[]
 */
function tsolo_get_tml_stored_page_ids() {
	$legacy = tsolo_get_tml_legacy_option();
	$ids    = tsolo_extract_page_ids_from_tml_option( is_array( $legacy ) ? $legacy : array() );

	$keys = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'profile', 'confirmaction', 'favorites' );
	foreach ( $keys as $key ) {
		foreach ( array( 'tml_' . $key . '_page', 'tml_page_' . $key ) as $opt ) {
			foreach ( array( true, false ) as $use_site ) {
				$val = $use_site ? get_site_option( $opt, null ) : get_option( $opt, null );
				if ( tsolo_is_positive_page_id( $val ) ) {
					$ids[] = (int) $val;
				}
			}
		}
	}

	return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * Whether this array key is a page id, not a numeric setting.
 *
 * @param mixed $key Array key.
 * @return bool
 */
function tsolo_is_tml_page_id_key( $key ) {
	if ( ! is_string( $key ) || '' === $key ) {
		return false;
	}
	$key_l   = strtolower( $key );
	$actions = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'profile', 'confirmaction', 'favorites' );
	if ( in_array( $key_l, $actions, true ) || in_array( $key_l, array( 'page', 'page_id' ), true ) ) {
		return true;
	}
	foreach ( $actions as $action ) {
		if ( $action . '_page' === $key_l || $action . '_page_id' === $key_l || 'tml_' . $action . '_page' === $key_l || 'tml_page_' . $action === $key_l ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether a stored value is a positive integer page id.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function tsolo_is_positive_page_id( $value ) {
	if ( is_bool( $value ) || is_array( $value ) || is_object( $value ) || ! is_numeric( $value ) ) {
		return false;
	}
	$int = (int) $value;
	return $int > 0 && (string) ( 0 + $value ) === (string) $int;
}

/**
 * Copy Theme My Login 6.x values that never became site options.
 *
 * Site options already applied win. Extensions and email designs are not copied.
 *
 * @param array $settings  Settings being built.
 * @param array $from_site Keys already taken from site options.
 */
function tsolo_import_legacy_tml_option( &$settings, &$from_site ) {
	$legacy = tsolo_get_tml_legacy_option();
	if ( ! is_array( $legacy ) ) {
		return;
	}
	if ( empty( $from_site['login_type'] ) ) {
		$applied = false;
		if ( isset( $legacy['login_type'] ) && ( is_string( $legacy['login_type'] ) || is_numeric( $legacy['login_type'] ) ) ) {
			$login_type = sanitize_key( (string) $legacy['login_type'] );
			if ( in_array( $login_type, array( 'default', 'username', 'email' ), true ) ) {
				$settings['login_type'] = $login_type;
				$applied                = true;
			}
		}
		if ( ! $applied && ! empty( $legacy['email_login'] ) && 1 === tsolo_tml_checkbox_flag( $legacy['email_login'] ) ) {
			$settings['login_type'] = 'email';
		}
	}
	// Defensive: older blobs sometimes stored these beside site options.
	if ( ! isset( $from_site['ajax'] ) && array_key_exists( 'ajax', $legacy ) ) {
		$settings['ajax']  = tsolo_tml_checkbox_flag( $legacy['ajax'] );
		$from_site['ajax'] = true;
	} elseif ( ! isset( $from_site['ajax'] ) && array_key_exists( 'enable_ajax', $legacy ) ) {
		$settings['ajax']  = tsolo_tml_checkbox_flag( $legacy['enable_ajax'] );
		$from_site['ajax'] = true;
	}
	if ( empty( $from_site['registration_type'] ) && isset( $legacy['registration_type'] ) && ( is_string( $legacy['registration_type'] ) || is_numeric( $legacy['registration_type'] ) ) ) {
		$reg_type = sanitize_key( (string) $legacy['registration_type'] );
		if ( in_array( $reg_type, array( 'default', 'email' ), true ) ) {
			$settings['registration_type']  = $reg_type;
			$from_site['registration_type'] = true;
		}
	}
	if ( ! isset( $from_site['user_passwords'] ) && array_key_exists( 'user_passwords', $legacy ) ) {
		$settings['user_passwords']  = tsolo_tml_checkbox_flag( $legacy['user_passwords'] );
		$from_site['user_passwords'] = true;
	}
	if ( ! isset( $from_site['auto_login'] ) && array_key_exists( 'auto_login', $legacy ) ) {
		$settings['auto_login']  = tsolo_tml_checkbox_flag( $legacy['auto_login'] );
		$from_site['auto_login'] = true;
	}
	if ( empty( $legacy['slugs'] ) || ! is_array( $legacy['slugs'] ) ) {
		return;
	}
	$taken = isset( $from_site['slugs'] ) && is_array( $from_site['slugs'] ) ? $from_site['slugs'] : array();
	foreach ( tsolo_tml_slug_keys() as $key ) {
		if ( ! empty( $taken[ $key ] ) || ! isset( $legacy['slugs'][ $key ] ) ) {
			continue;
		}
		$raw = $legacy['slugs'][ $key ];
		if ( ! is_string( $raw ) && ! is_numeric( $raw ) ) {
			continue;
		}
		$slug = sanitize_title( (string) $raw );
		if ( '' !== $slug ) {
			$settings['slugs'][ $key ]  = $slug;
			$from_site['slugs'][ $key ] = true;
		}
	}
}

/**
 * Page option key pairs used by Theme My Login for each action.
 *
 * @return array<string,string[]>
 */
function tsolo_tml_page_option_keys() {
	$keys = array();
	foreach ( tsolo_tml_slug_keys() as $action ) {
		$keys[ $action ] = array(
			'tml_' . $action . '_page',
			'tml_page_' . $action,
		);
	}
	return $keys;
}

/**
 * Resolve a page ID stored for a Theme My Login action.
 *
 * @param string $action Action name.
 * @return int
 */
function tsolo_tml_page_id_for_action( $action ) {
	$action = sanitize_key( $action );
	$map    = tsolo_tml_page_option_keys();
	if ( empty( $map[ $action ] ) ) {
		return 0;
	}
	foreach ( $map[ $action ] as $opt ) {
		foreach ( array( true, false ) as $use_site ) {
			$val = $use_site ? get_site_option( $opt, null ) : get_option( $opt, null );
			if ( tsolo_is_positive_page_id( $val ) ) {
				return (int) $val;
			}
		}
	}
	$legacy = tsolo_get_tml_legacy_option();
	if ( is_array( $legacy ) ) {
		foreach ( array( $action, $action . '_page', $action . '_page_id', 'page_' . $action ) as $key ) {
			if ( isset( $legacy[ $key ] ) && tsolo_is_positive_page_id( $legacy[ $key ] ) ) {
				return (int) $legacy[ $key ];
			}
			if ( isset( $legacy['pages'][ $action ] ) && tsolo_is_positive_page_id( $legacy['pages'][ $action ] ) ) {
				return (int) $legacy['pages'][ $action ];
			}
		}
	}
	return 0;
}

/**
 * Fill missing slugs from Theme My Login page post_name values.
 *
 * @param array $settings  Settings being built.
 * @param array $from_site Keys already taken from site options.
 */
function tsolo_import_tml_slugs_from_pages( &$settings, &$from_site ) {
	if ( ! function_exists( 'get_post' ) ) {
		return;
	}
	$taken = isset( $from_site['slugs'] ) && is_array( $from_site['slugs'] ) ? $from_site['slugs'] : array();
	foreach ( tsolo_tml_slug_keys() as $key ) {
		if ( ! empty( $taken[ $key ] ) ) {
			continue;
		}
		$page_id = tsolo_tml_page_id_for_action( $key );
		if ( $page_id < 1 ) {
			continue;
		}
		$page = get_post( $page_id );
		if ( ! $page || empty( $page->post_name ) ) {
			continue;
		}
		$slug = sanitize_title( (string) $page->post_name );
		if ( '' === $slug || tsolo_is_reserved_action_slug( $slug ) ) {
			continue;
		}
		$settings['slugs'][ $key ]  = $slug;
		$from_site['slugs'][ $key ] = true;
	}
}

/**
 * Modules that should be enabled after a TML import (slugs/pages were present).
 *
 * Does not turn modules on automatically — only returns suggestions.
 *
 * @param array|null $imported_slugs Slug keys that came from TML (optional).
 * @return string[] Module keys (profiles, favorites).
 */
function tsolo_tml_suggested_modules_after_import( $imported_slugs = null ) {
	$suggest = array();
	$slugs   = is_array( $imported_slugs ) ? $imported_slugs : array();

	$had_profile = ! empty( $slugs['profile'] )
		|| null !== tsolo_get_tml_value( 'tml_profile_slug' )
		|| tsolo_tml_page_id_for_action( 'profile' ) > 0;
	if ( $had_profile && ( ! function_exists( 'tsolo_is_module_enabled' ) || ! tsolo_is_module_enabled( 'profiles' ) ) ) {
		$suggest[] = 'profiles';
	}

	$had_favorites = ! empty( $slugs['favorites'] )
		|| null !== tsolo_get_tml_value( 'tml_favorites_slug' )
		|| tsolo_tml_page_id_for_action( 'favorites' ) > 0;
	if ( $had_favorites && ( ! function_exists( 'tsolo_is_module_enabled' ) || ! tsolo_is_module_enabled( 'favorites' ) ) ) {
		$suggest[] = 'favorites';
	}

	return array_values( array_unique( $suggest ) );
}

/**
 * Human labels for modules suggested after TML import.
 *
 * @param string[] $module_keys Module keys.
 * @return string[]
 */
function tsolo_tml_suggested_module_labels( $module_keys ) {
	$labels = array();
	$defs   = function_exists( 'tsolo_get_module_definitions' ) ? tsolo_get_module_definitions() : array();
	foreach ( (array) $module_keys as $key ) {
		if ( ! empty( $defs[ $key ]['label'] ) ) {
			$labels[] = $defs[ $key ]['label'];
		} else {
			$labels[] = $key;
		}
	}
	return $labels;
}

/**
 * Import Theme My Login settings into TSOLO.
 *
 * Does not delete Theme My Login options and does not deactivate that plugin.
 * Copies all known action slugs (including profile and favorites). Optional
 * modules are not auto-enabled — see tsolo_tml_suggested_modules_after_import().
 *
 * @param bool $force Force re-import even if already imported.
 * @return string imported|skipped|missing.
 */
function tsolo_import_tml_settings( $force = false ) {
	if ( ! $force && get_option( TSOLO_OPT_TML_IMPORTED ) ) {
		return 'skipped';
	}
	if ( ! tsolo_tml_options_present() ) {
		return 'missing';
	}

	$settings  = tsolo_get_settings();
	$from_site = array(
		'login_type'        => false,
		'registration_type' => false,
		'slugs'             => array(),
	);

	$ajax = tsolo_get_tml_value( 'tml_ajax' );
	if ( null !== $ajax ) {
		$settings['ajax']  = tsolo_tml_checkbox_flag( $ajax );
		$from_site['ajax'] = true;
	}

	$login_type = tsolo_get_tml_value( 'tml_login_type' );
	if ( is_string( $login_type ) || is_numeric( $login_type ) ) {
		$login_type = sanitize_key( (string) $login_type );
		if ( in_array( $login_type, array( 'default', 'username', 'email' ), true ) ) {
			$settings['login_type']  = $login_type;
			$from_site['login_type'] = true;
		}
	}

	$reg_type = tsolo_get_tml_value( 'tml_registration_type' );
	if ( is_string( $reg_type ) || is_numeric( $reg_type ) ) {
		$reg_type = sanitize_key( (string) $reg_type );
		if ( in_array( $reg_type, array( 'default', 'email' ), true ) ) {
			$settings['registration_type']  = $reg_type;
			$from_site['registration_type'] = true;
		}
	}

	$user_passwords = tsolo_get_tml_value( 'tml_user_passwords' );
	if ( null !== $user_passwords ) {
		$settings['user_passwords']  = tsolo_tml_checkbox_flag( $user_passwords );
		$from_site['user_passwords'] = true;
	}

	$auto_login = tsolo_get_tml_value( 'tml_auto_login' );
	if ( null !== $auto_login ) {
		$settings['auto_login']  = tsolo_tml_checkbox_flag( $auto_login );
		$from_site['auto_login'] = true;
	}

	foreach ( tsolo_tml_slug_keys() as $key ) {
		$slug = tsolo_get_tml_value( 'tml_' . $key . '_slug' );
		if ( ! is_string( $slug ) && ! is_numeric( $slug ) ) {
			continue;
		}
		$slug = sanitize_title( (string) $slug );
		if ( '' === $slug ) {
			continue;
		}
		$settings['slugs'][ $key ]  = $slug;
		$from_site['slugs'][ $key ] = true;
	}

	tsolo_import_legacy_tml_option( $settings, $from_site );
	tsolo_import_tml_slugs_from_pages( $settings, $from_site );
	tsolo_update_settings( $settings );
	update_option( TSOLO_OPT_TML_IMPORTED, time(), false );

	$hints = tsolo_tml_suggested_modules_after_import( $from_site['slugs'] );
	if ( $hints ) {
		update_option( TSOLO_OPT_TML_MODULE_HINTS, $hints, false );
	} else {
		delete_option( TSOLO_OPT_TML_MODULE_HINTS );
	}

	return 'imported';
}

/**
 * Pending module keys suggested after the last TML import (empty when none).
 *
 * @return string[]
 */
function tsolo_tml_pending_module_hints() {
	$raw = get_option( TSOLO_OPT_TML_MODULE_HINTS, array() );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $key ) {
		$key = sanitize_key( (string) $key );
		if ( '' !== $key && ( ! function_exists( 'tsolo_is_module_enabled' ) || ! tsolo_is_module_enabled( $key ) ) ) {
			$out[] = $key;
		}
	}
	$out = array_values( array_unique( $out ) );
	if ( ! $out && false !== get_option( TSOLO_OPT_TML_MODULE_HINTS, false ) ) {
		delete_option( TSOLO_OPT_TML_MODULE_HINTS );
	}
	return $out;
}

/**
 * Clear stored TML module enable hints.
 */
function tsolo_tml_clear_module_hints() {
	delete_option( TSOLO_OPT_TML_MODULE_HINTS );
}
