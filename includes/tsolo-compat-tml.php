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
	$legacy = get_option( 'theme_my_login', null );
	return is_array( $legacy ) && ! empty( $legacy );
}

/**
 * Slugs that currently occupy a virtual URL (and therefore clash with a real page).
 *
 * @return string[]
 */
function tsolo_known_tml_page_slugs() {
	$slugs    = array();
	$routed   = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'confirmaction' );
	$settings = tsolo_get_settings();
	if ( function_exists( 'tsolo_is_module_enabled' ) ) {
		if ( tsolo_is_module_enabled( 'profiles' ) ) {
			$routed[] = 'profile';
		}
		if ( tsolo_is_module_enabled( 'favorites' ) ) {
			$routed[] = 'favorites';
		}
	}
	foreach ( $routed as $key ) {
		if ( ! empty( $settings['slugs'][ $key ] ) && ( is_string( $settings['slugs'][ $key ] ) || is_numeric( $settings['slugs'][ $key ] ) ) ) {
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
	$ids = tsolo_extract_page_ids_from_tml_option( get_option( 'theme_my_login', array() ) );

	$keys = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'profile', 'confirmaction' );
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
function tsolo_import_legacy_tml_option( &$settings, $from_site ) {
	$legacy = get_option( 'theme_my_login', null );
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
			$settings['slugs'][ $key ] = $slug;
		}
	}
}

/**
 * Import Theme My Login settings into TSOLO.
 *
 * Does not delete Theme My Login options and does not deactivate that plugin.
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
		'login_type' => false,
		'slugs'      => array(),
	);

	$ajax = tsolo_get_tml_value( 'tml_ajax' );
	if ( null !== $ajax ) {
		$settings['ajax'] = tsolo_tml_checkbox_flag( $ajax );
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
			$settings['registration_type'] = $reg_type;
		}
	}

	$user_passwords = tsolo_get_tml_value( 'tml_user_passwords' );
	if ( null !== $user_passwords ) {
		$settings['user_passwords'] = tsolo_tml_checkbox_flag( $user_passwords );
	}

	$auto_login = tsolo_get_tml_value( 'tml_auto_login' );
	if ( null !== $auto_login ) {
		$settings['auto_login'] = tsolo_tml_checkbox_flag( $auto_login );
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
	tsolo_update_settings( $settings );
	update_option( TSOLO_OPT_TML_IMPORTED, time(), false );
	return 'imported';
}
