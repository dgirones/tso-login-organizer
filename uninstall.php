<?php
/**
 * Uninstall TSO Login Organizer.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Keep everything unless the admin asked for a full cleanup (Core settings > Plugin data).
$tsolo_settings = get_option( 'tso_login_organizer_settings', array() );
if ( ! is_array( $tsolo_settings ) || empty( $tsolo_settings['delete_data'] ) ) {
	flush_rewrite_rules();
	return;
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
	'tso_login_organizer_fields',
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
	'tso_login_organizer_tml_module_hints',
	'tso_login_organizer_flush_rewrites',
	'tsolo_2fa_debug', // Temporary diagnostics option from testing builds.
);

foreach ( $tsolo_options as $tsolo_option ) {
	delete_option( $tsolo_option );
	delete_site_option( $tsolo_option );
}

$tsolo_user_meta = array(
	'tso_login_organizer_ui_lang',
	'tsolo_2fa_secret',
	'tsolo_2fa_backup',
	'tsolo_2fa_last_step',
	'tsolo_favorites',
	'tsolo_avatar_url',
	'tsolo_social_provider',
	'tsolo_social_identity',
	'tsolo_moderation_status',
	'tsolo_email_confirm_key',
	'tsolo_email_confirm_time',
);
foreach ( $tsolo_user_meta as $tsolo_meta_key ) {
	delete_metadata( 'user', 0, $tsolo_meta_key, '', true );
}

global $wpdb;
// Custom profile fields and PKCE/notice/scan transients owned by this plugin.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off uninstall cleanup.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $wpdb->esc_like( 'tsolo_cf_' ) . '%' ) );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off uninstall cleanup.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_tsolo_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_tsolo_' ) . '%',
		$wpdb->esc_like( '_transient_tso_login_organizer_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_tso_login_organizer_' ) . '%'
	)
);

// Per-post role restriction meta (Restrictions module).
delete_post_meta_by_key( '_tsolo_restrict_roles' );
wp_clear_scheduled_hook( 'tsolo_mailchimp_subscribe_event' );

// Plugin-owned uploads folder (avatars, backups).
$tsolo_upload = wp_upload_dir();
if ( empty( $tsolo_upload['error'] ) ) {
	$tsolo_dir = trailingslashit( $tsolo_upload['basedir'] ) . 'tso-login-organizer';
	if ( is_dir( $tsolo_dir ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			$wp_filesystem->delete( $tsolo_dir, true );
		}
	}
}

flush_rewrite_rules();
