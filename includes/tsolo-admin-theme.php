<?php
/**
 * Admin day / night / auto theme (same cycle as the other TSO plugins).
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site timezone string.
 *
 * @return string
 */
function tsolo_theme_timezone_string() {
	if ( function_exists( 'wp_timezone_string' ) ) {
		return (string) wp_timezone_string();
	}
	return (string) get_option( 'timezone_string', '' );
}

/**
 * Approximate coordinates for sunrise and sunset.
 *
 * @return array{lat: float, lng: float}
 */
function tsolo_theme_coords() {
	$tz  = tsolo_theme_timezone_string();
	$map = array(
		'Europe/Madrid'                  => array( 40.42, -3.70 ),
		'Europe/Andorra'                 => array( 42.51, 1.52 ),
		'Atlantic/Canary'                => array( 28.29, -16.63 ),
		'Europe/London'                  => array( 51.51, -0.13 ),
		'Europe/Paris'                   => array( 48.86, 2.35 ),
		'Europe/Berlin'                  => array( 52.52, 13.41 ),
		'Europe/Rome'                    => array( 41.90, 12.50 ),
		'Europe/Lisbon'                  => array( 38.72, -9.14 ),
		'Europe/Brussels'                => array( 50.85, 4.35 ),
		'Europe/Amsterdam'               => array( 52.37, 4.90 ),
		'Europe/Zurich'                  => array( 47.37, 8.54 ),
		'America/Mexico_City'            => array( 19.43, -99.13 ),
		'America/New_York'               => array( 40.71, -74.01 ),
		'America/Chicago'                => array( 41.88, -87.63 ),
		'America/Los_Angeles'            => array( 34.05, -118.24 ),
		'America/Argentina/Buenos_Aires' => array( -34.60, -58.38 ),
		'America/Sao_Paulo'              => array( -23.55, -46.63 ),
		'America/Bogota'                 => array( 4.71, -74.07 ),
		'America/Lima'                   => array( -12.05, -77.04 ),
		'America/Santiago'               => array( -33.45, -70.67 ),
		'Asia/Tokyo'                     => array( 35.68, 139.69 ),
		'Australia/Sydney'               => array( -33.87, 151.21 ),
		'UTC'                            => array( 0.0, 0.0 ),
	);

	if ( isset( $map[ $tz ] ) ) {
		return array(
			'lat' => (float) $map[ $tz ][0],
			'lng' => (float) $map[ $tz ][1],
		);
	}
	if ( 0 === strpos( $tz, 'Europe/' ) ) {
		return array(
			'lat' => 41.39,
			'lng' => 2.17,
		);
	}
	if ( 0 === strpos( $tz, 'America/' ) ) {
		return array(
			'lat' => 19.43,
			'lng' => -99.13,
		);
	}
	return array(
		'lat' => 41.39,
		'lng' => 2.17,
	);
}

/**
 * Theme toggle labels.
 *
 * @param string $lang UI language.
 * @return array<string,string>
 */
function tsolo_theme_i18n( $lang ) {
	return array(
		'themeDay'      => tsolo_ui_triple_text( $lang, 'Mode dia', 'Modo día', 'Day mode' ),
		'themeNight'    => tsolo_ui_triple_text( $lang, 'Mode nit', 'Modo noche', 'Night mode' ),
		'themeAuto'     => tsolo_ui_triple_text( $lang, 'Mode auto', 'Modo auto', 'Auto mode' ),
		'themeAutoHint' => tsolo_ui_triple_text(
			$lang,
			'Segueix la sortida i la posta de sol (canvia amb les estacions)',
			'Sigue la salida y la puesta de sol (cambia con las estaciones)',
			'Follows sunrise and sunset (changes with the seasons)'
		),
	);
}

/**
 * Day / night / auto button.
 *
 * @param string $lang UI language.
 */
function tsolo_render_theme_toggle( $lang = 'ca' ) {
	$i18n  = tsolo_theme_i18n( $lang );
	$label = isset( $i18n['themeAuto'] ) ? $i18n['themeAuto'] : 'Auto mode';
	echo '<button type="button" id="tsolo-theme-toggle" class="tsolo-theme-btn" aria-pressed="mixed" title="' . esc_attr( $label ) . '">';
	echo '<span class="tsolo-theme-icon" aria-hidden="true">🌓</span>';
	echo '<span class="tsolo-theme-label">' . esc_html( $label ) . '</span>';
	echo '</button>';
}
