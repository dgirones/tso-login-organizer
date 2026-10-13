<?php
/**
 * Uninstall TSO Login Organizer.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$tsolo_options = array(
	'tso_login_organizer_db_schema',
	'tso_login_organizer_settings',
	'tso_login_organizer_modules',
	'tso_login_organizer_redirection',
	'tso_login_organizer_moderation',
	'tso_login_organizer_restrictions',
	'tso_login_organizer_security',
	'tso_login_organizer_captcha',
	'tso_login_organizer_profiles',
	'tso_login_organizer_notifications',
	'tso_login_organizer_two_factor',
	'tso_login_organizer_social',
	'tso_login_organizer_avatars',
	'tso_login_organizer_favorites',
	'tso_login_organizer_mailchimp',
	'tso_login_organizer_lockouts',
	'tso_login_organizer_attempts',
	'tso_login_organizer_tml_imported',
);

foreach ( $tsolo_options as $tsolo_option ) {
	delete_option( $tsolo_option );
	delete_site_option( $tsolo_option );
}

delete_metadata( 'user', 0, 'tso_login_organizer_ui_lang', '', true );

flush_rewrite_rules();
