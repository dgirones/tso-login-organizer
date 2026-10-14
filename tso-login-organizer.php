<?php
/**
 * Plugin Name:       TSO Login Organizer
 * Description:       Frontend login, registration and account pages with optional premium modules. Drop-in replacement for Theme My Login.
 * Version:           1.0.1
 * Author:            Tu Soporte Online
 * Author URI:        https://www.tusoporteonline.es/blog
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tso-login-organizer
 * Domain Path:       /languages
 * Contributors:      deadko
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TSOLO_FILE' ) ) {
	define( 'TSOLO_FILE', __FILE__ );
}

if ( ! defined( 'TSOLO_VERSION' ) ) {
	define( 'TSOLO_VERSION', '1.0.1' );
}

if ( ! defined( 'TSOLO_PATH' ) ) {
	define( 'TSOLO_PATH', plugin_dir_path( TSOLO_FILE ) );
}

if ( ! defined( 'TSOLO_URL' ) ) {
	define( 'TSOLO_URL', plugin_dir_url( TSOLO_FILE ) );
}

if ( ! defined( 'TSOLO_NONCE_AJAX' ) ) {
	define( 'TSOLO_NONCE_AJAX', 'tsolo_login_organizer_ajax' );
}

if ( ! defined( 'TSOLO_NONCE_FORM' ) ) {
	define( 'TSOLO_NONCE_FORM', 'tsolo_login_organizer' );
}

require_once TSOLO_PATH . 'includes/tsolo-storage.php';
require_once TSOLO_PATH . 'includes/class-tsolo-action.php';
require_once TSOLO_PATH . 'includes/tsolo-actions.php';
require_once TSOLO_PATH . 'includes/tsolo-forms.php';
require_once TSOLO_PATH . 'includes/tsolo-rewrite.php';
require_once TSOLO_PATH . 'includes/tsolo-urls.php';
require_once TSOLO_PATH . 'includes/tsolo-shortcodes.php';
require_once TSOLO_PATH . 'includes/class-tsolo-widget.php';
require_once TSOLO_PATH . 'includes/tsolo-nav-menus.php';
require_once TSOLO_PATH . 'includes/tsolo-ajax.php';
require_once TSOLO_PATH . 'includes/tsolo-compat-tml.php';
require_once TSOLO_PATH . 'includes/tsolo-modules.php';
require_once TSOLO_PATH . 'includes/tsolo-hooks.php';

add_action( 'plugins_loaded', 'tsolo_load_textdomain', 0 );

/**
 * Load bundled translations via load_textdomain (not load_plugin_textdomain).
 */
function tsolo_load_textdomain() {
	$locale     = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	$locale     = (string) $locale;
	$candidates = array( $locale );
	if ( 0 === strpos( $locale, 'ca' ) ) {
		$candidates[] = 'ca';
	}
	if ( 0 === strpos( $locale, 'es_' ) || 'es' === $locale ) {
		$candidates[] = 'es_ES';
	}
	$candidates = array_unique( $candidates );
	foreach ( $candidates as $candidate ) {
		$mofile = TSOLO_PATH . 'languages/tso-login-organizer-' . $candidate . '.mo';
		if ( is_readable( $mofile ) ) {
			load_textdomain( 'tso-login-organizer', $mofile, $locale );
			return;
		}
	}
}

register_activation_hook( TSOLO_FILE, 'tsolo_activate' );
register_deactivation_hook( TSOLO_FILE, 'tsolo_deactivate' );

/**
 * Plugin activation.
 */
function tsolo_activate() {
	tsolo_maybe_migrate_schema();
	tsolo_import_tml_settings( false );
	tsolo_register_default_actions();
	tsolo_add_rewrite_tags();
	tsolo_add_rewrite_rules();
	flush_rewrite_rules();
}

/**
 * Plugin deactivation.
 */
function tsolo_deactivate() {
	flush_rewrite_rules();
}

if ( is_admin() ) {
	require_once TSOLO_PATH . 'includes/tsolo-admin-assets.php';
	require_once TSOLO_PATH . 'includes/tsolo-admin.php';
}
