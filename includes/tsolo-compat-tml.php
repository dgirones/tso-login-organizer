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
 * Whether TML options appear installed.
 *
 * @return bool
 */
function tsolo_tml_options_present() {
	return ( false !== get_site_option( 'tml_login_type', false ) )
		|| ( false !== get_site_option( 'tml_ajax', false ) )
		|| ( false !== get_site_option( '_tml_version', false ) )
		|| ( false !== get_option( 'theme_my_login', false ) );
}

/**
 * Common Theme My Login / action slug variants (not only current settings).
 *
 * @return string[]
 */
function tsolo_known_tml_page_slugs() {
	$slugs = array(
		'login',
		'logout',
		'register',
		'lostpassword',
		'lost-password',
		'resetpass',
		'reset-password',
		'retrievepassword',
		'retrieve-password',
		'dashboard',
		'profile',
		'favorites',
		'confirmaction',
		'confirm-action',
		'account',
	);

	$settings = tsolo_get_settings();
	if ( ! empty( $settings['slugs'] ) && is_array( $settings['slugs'] ) ) {
		foreach ( $settings['slugs'] as $slug ) {
			$slug = sanitize_title( $slug );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
	}

	$slug_keys = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'confirmaction', 'profile' );
	foreach ( $slug_keys as $key ) {
		$from_tml = get_site_option( 'tml_' . $key . '_slug', null );
		if ( null !== $from_tml && '' !== $from_tml ) {
			$slugs[] = sanitize_title( $from_tml );
		}
	}

	$slugs = array_values( array_unique( array_filter( $slugs ) ) );
	return $slugs;
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
		if ( is_numeric( $data ) && (int) $data > 0 ) {
			$ids[] = (int) $data;
		}
		return $ids;
	}
	foreach ( $data as $key => $value ) {
		$key_l = is_string( $key ) ? strtolower( $key ) : '';
		if ( is_numeric( $value ) && (int) $value > 0 ) {
			if ( '' === $key_l || false !== strpos( $key_l, 'page' ) || in_array( $key_l, array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'profile' ), true ) ) {
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
		foreach ( array( 'tml_' . $key . '_page', 'tml_page_' . $key, $key . '_page' ) as $opt ) {
			foreach ( array( true, false ) as $use_site ) {
				$val = $use_site ? get_site_option( $opt, null ) : get_option( $opt, null );
				if ( is_numeric( $val ) && (int) $val > 0 ) {
					$ids[] = (int) $val;
				}
			}
		}
	}

	return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * Import Theme My Login settings into TSOLO.
 *
 * @param bool $force Force re-import even if already imported.
 * @return bool True if imported.
 */
function tsolo_import_tml_settings( $force = false ) {
	if ( ! $force && get_option( TSOLO_OPT_TML_IMPORTED ) ) {
		return false;
	}
	if ( ! tsolo_tml_options_present() ) {
		return false;
	}

	$settings = tsolo_get_settings();

	$ajax = get_site_option( 'tml_ajax', null );
	if ( null !== $ajax ) {
		$settings['ajax'] = $ajax ? 1 : 0;
	}

	$login_type = get_site_option( 'tml_login_type', null );
	if ( null !== $login_type ) {
		$login_type = sanitize_key( $login_type );
		if ( in_array( $login_type, array( 'default', 'username', 'email' ), true ) ) {
			$settings['login_type'] = $login_type;
		}
	}

	$reg_type = get_site_option( 'tml_registration_type', null );
	if ( null !== $reg_type ) {
		$reg_type = sanitize_key( $reg_type );
		if ( in_array( $reg_type, array( 'default', 'email' ), true ) ) {
			$settings['registration_type'] = $reg_type;
		}
	}

	$user_passwords = get_site_option( 'tml_user_passwords', null );
	if ( null !== $user_passwords ) {
		$settings['user_passwords'] = $user_passwords ? 1 : 0;
	}

	$auto_login = get_site_option( 'tml_auto_login', null );
	if ( null !== $auto_login ) {
		$settings['auto_login'] = $auto_login ? 1 : 0;
	}

	$slug_keys = array( 'login', 'logout', 'register', 'lostpassword', 'resetpass', 'dashboard', 'confirmaction' );
	foreach ( $slug_keys as $key ) {
		$slug = get_site_option( 'tml_' . $key . '_slug', null );
		if ( null !== $slug && '' !== $slug ) {
			$settings['slugs'][ $key ] = sanitize_title( $slug );
		}
	}

	tsolo_update_settings( $settings );
	update_option( TSOLO_OPT_TML_IMPORTED, time(), false );
	return true;
}
