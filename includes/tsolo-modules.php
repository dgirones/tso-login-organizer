<?php
/**
 * Feature modules loader and collision detection.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module definitions.
 *
 * @return array
 */
function tsolo_get_module_definitions() {
	$slugs         = tsolo_get_settings();
	$slugs         = isset( $slugs['slugs'] ) && is_array( $slugs['slugs'] ) ? $slugs['slugs'] : array();
	$login_slug    = ! empty( $slugs['login'] ) ? $slugs['login'] : 'login';
	$register_slug = ! empty( $slugs['register'] ) ? $slugs['register'] : 'register';
	$profile_slug  = ! empty( $slugs['profile'] ) ? $slugs['profile'] : 'profile';
	$fav_slug      = ! empty( $slugs['favorites'] ) ? $slugs['favorites'] : 'favorites';
	$login_url     = home_url( '/' . trim( (string) $login_slug, '/' ) . '/' );
	$register_url  = home_url( '/' . trim( (string) $register_slug, '/' ) . '/' );
	$profile_url   = home_url( '/' . trim( (string) $profile_slug, '/' ) . '/' );
	$favorites_url = home_url( '/' . trim( (string) $fav_slug, '/' ) . '/' );

	return array(
		'redirection'   => array(
			'label'       => __( 'Redirection', 'tso-login-organizer' ),
			'description' => __( 'Send users to different URLs after login, logout or registration, optionally per role.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Redirection tab to set default and per-role URLs for login, logout and registration.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module and click Save modules.', 'tso-login-organizer' ),
				__( 'Open the Redirection tab. Set a default URL and, if needed, a different URL per role for After login, After logout and After registration.', 'tso-login-organizer' ),
				__( 'Leave a field empty to keep the WordPress default for that case.', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'Other login/redirect plugins (membership, LMS, WooCommerce “redirect after login”) may override these URLs depending on filter priority.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-redirection.php',
			'class'       => 'TSOLO_Mod_Redirection',
			'collide'     => false,
		),
		'moderation'    => array(
			'label'       => __( 'Moderation', 'tso-login-organizer' ),
			'description' => __( 'Require email confirmation and/or admin approval before new accounts can log in.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Moderation tab to turn email confirmation and/or admin approval on or off.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module and Save modules, then open the Moderation tab.', 'tso-login-organizer' ),
				__( 'Turn on Email confirmation and/or Admin approval, then Save module settings.', 'tso-login-organizer' ),
				__( 'New registrations receive a confirmation email and/or stay pending until an administrator approves them (Users screen).', 'tso-login-organizer' ),
				__( 'Turn off Core → Auto login when using moderation, or new users may be signed in before they are approved.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open Users screen', 'tso-login-organizer' ) => admin_url( 'users.php' ),
				__( 'Open register page', 'tso-login-organizer' ) => $register_url,
			),
			'compat'      => array(
				__( 'Conflicts with other registration-approval plugins that also block wp_authenticate_user.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-moderation.php',
			'class'       => 'TSOLO_Mod_Moderation',
			'collide'     => false,
		),
		'restrictions'  => array(
			'label'       => __( 'Restrictions', 'tso-login-organizer' ),
			'description' => __( 'Make the site private (login required) and restrict content with shortcodes.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Restrictions tab for private site and shortcode examples.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module and Save modules, then open the Restrictions tab.', 'tso-login-organizer' ),
				__( 'Optional: tick Private site so guests must log in before seeing any page (login/register URLs stay reachable).', 'tso-login-organizer' ),
				__( 'In posts/pages, wrap content with [tsolo-logged-in]…[/tsolo-logged-in] or [tsolo-logged-out]…[/tsolo-logged-out].', 'tso-login-organizer' ),
				__( 'To allow only some roles: [tsolo-role role="subscriber,customer"]…[/tsolo-role].', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'Private site redirects guests site-wide — do not combine with another “force login” / members-only plugin.', 'tso-login-organizer' ),
				__( 'Caching plugins may serve public HTML to guests; exclude restricted pages or disable cache for logged-out users when testing.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-restrictions.php',
			'class'       => 'TSOLO_Mod_Restrictions',
			'collide'     => false,
		),
		'security'      => array(
			'label'       => __( 'Security', 'tso-login-organizer' ),
			'description' => __( 'Lock accounts after failed logins, enforce password length, and optionally hide wp-login.php.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Security tab for lockout attempts, duration, min password length and hide wp-login.php.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable only if Swiss Knife Login Protect / Kadence Security are not already doing the same job.', 'tso-login-organizer' ),
				__( 'Open the Security tab: enable lockouts, set max attempts and lockout minutes, optional min password length.', 'tso-login-organizer' ),
				__( 'Hide wp-login.php returns 404 on the default login file — use only with this plugin’s frontend login URL and no other custom-login plugins.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open login page', 'tso-login-organizer' ) => $login_url,
			),
			'compat'      => array(
				__( 'May conflict with TSO Swiss Knife Login Protect and Kadence Security (lockouts / hide login).', 'tso-login-organizer' ),
				__( 'Hide wp-login.php conflicts with any plugin that renames or protects wp-login.php.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-security.php',
			'class'       => 'TSOLO_Mod_Security',
			'collide'     => true,
		),
		'captcha'       => array(
			'label'       => __( 'reCAPTCHA', 'tso-login-organizer' ),
			'description' => __( 'Protect login and registration forms with Google reCAPTCHA (needs site and secret keys).', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the reCAPTCHA tab and paste your Google site key and secret key.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Open https://www.google.com/recaptcha/admin and create a reCAPTCHA v2 "I\'m not a robot" key for your domain.', 'tso-login-organizer' ),
				__( 'Enable this module, paste Site key and Secret key below, Save module settings.', 'tso-login-organizer' ),
				__( 'The widget appears on this plugin’s login, register and lost-password forms. Leave keys empty to disable checks.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Google reCAPTCHA admin', 'tso-login-organizer' ) => 'https://www.google.com/recaptcha/admin',
				__( 'Open login page', 'tso-login-organizer' )        => $login_url,
			),
			'compat'      => array(
				__( 'Do not enable together with CleanTalk, Kadence captcha, or another reCAPTCHA on the same forms (double challenges / failed verifies).', 'tso-login-organizer' ),
				__( 'Sends visitor IP and token to Google for verification when forms are submitted (see readme External services).', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-captcha.php',
			'class'       => 'TSOLO_Mod_Captcha',
			'collide'     => true,
		),
		'profiles'      => array(
			'label'       => __( 'Profiles', 'tso-login-organizer' ),
			'description' => __( 'Frontend profile page so users can edit their account without using wp-admin.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: a /profile/ page is available. Open the Profiles tab to block wp-admin for users without edit_posts.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module and Save modules. A virtual profile page is created (default slug /profile/ — change under Core → URL slugs).', 'tso-login-organizer' ),
				/* translators: %s: profile URL */
				sprintf( __( 'Logged-in users open %s to edit display name, email and password.', 'tso-login-organizer' ), $profile_url ),
				__( 'Optional: Block wp-admin redirects subscribers (no edit_posts) away from /wp-admin/ to the profile page and hides the admin bar.', 'tso-login-organizer' ),
				__( 'Enable Avatars as well if you want a profile-picture upload on that same page.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open profile page', 'tso-login-organizer' ) => $profile_url,
			),
			'compat'      => array(
				__( 'Block wp-admin prevents those users from reaching wp-admin screens (including other plugins’ profile tabs). Prefer frontend setup for 2FA when this is on.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-profiles.php',
			'class'       => 'TSOLO_Mod_Profiles',
			'collide'     => false,
		),
		'notifications' => array(
			'label'       => __( 'Notifications', 'tso-login-organizer' ),
			'description' => __( 'Customize welcome and password-reset emails (subject, body, optional HTML).', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Notifications tab to edit email subjects, bodies and HTML mode.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module, open the Notifications tab, edit subjects/bodies, Save module settings.', 'tso-login-organizer' ),
				__( 'Use placeholders listed under each field (e.g. {user_login}, {reset_url}). Empty fields keep the WordPress default text.', 'tso-login-organizer' ),
				__( 'HTML emails: tick the option only if your body contains HTML; otherwise leave plain text.', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'SMTP / email-template plugins may still wrap or override outgoing mail after this filter runs.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-notifications.php',
			'class'       => 'TSOLO_Mod_Notifications',
			'collide'     => false,
		),
		'two_factor'    => array(
			'label'       => __( 'Two-Factor Authentication', 'tso-login-organizer' ),
			'description' => __( 'TOTP authenticator codes for selected roles after password login.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Two-Factor tab to choose which roles must use 2FA. Users enable a secret on the frontend profile or in wp-admin.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module (keep off if another 2FA plugin is active), Save modules, open the Two-Factor tab.', 'tso-login-organizer' ),
				__( 'Tick the roles that must use 2FA, Save module settings.', 'tso-login-organizer' ),
				__( 'Each user enables Authenticator on the frontend profile page (Profiles module) or under Users → Profile in wp-admin, then adds the secret to an app (Google Authenticator, etc.).', 'tso-login-organizer' ),
				__( 'On login they enter the 6-digit code in the Authentication code field.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open profile page', 'tso-login-organizer' ) => $profile_url,
				__( 'Open login page', 'tso-login-organizer' )   => $login_url,
			),
			'compat'      => array(
				__( 'Conflicts with other WordPress 2FA / TOTP plugins on the same accounts.', 'tso-login-organizer' ),
				__( 'If Profiles → Block wp-admin is on, users should enable 2FA on the frontend profile (not wp-admin).', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-two-factor.php',
			'class'       => 'TSOLO_Mod_Two_Factor',
			'collide'     => true,
		),
		'social'        => array(
			'label'       => __( 'Social Login', 'tso-login-organizer' ),
			'description' => __( 'Sign in with Google, Facebook or X using OAuth app credentials you provide.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Social Login tab and fill Google / Facebook / X client IDs and secrets.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Follow the provider steps on this tab (each has a direct console link). Copy the redirect URI shown for that provider into the console.', 'tso-login-organizer' ),
				__( 'Paste Client ID / App ID and Secret below for each provider you want. Leave a provider empty to hide its button.', 'tso-login-organizer' ),
				__( 'Save module settings, then open the login page and try the social button.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open login page', 'tso-login-organizer' )    => $login_url,
				__( 'Open register page', 'tso-login-organizer' ) => $register_url,
				__( 'Google Cloud credentials', 'tso-login-organizer' ) => 'https://console.cloud.google.com/apis/credentials',
				__( 'Facebook apps', 'tso-login-organizer' )     => 'https://developers.facebook.com/apps/',
				__( 'X developer portal', 'tso-login-organizer' ) => 'https://developer.x.com/en/portal/dashboard',
			),
			'compat'      => array(
				__( 'Other social-login plugins on the same forms can show duplicate buttons or fight over callbacks.', 'tso-login-organizer' ),
				__( 'Sends users to Google / Facebook / X and exchanges tokens with those APIs (see readme External services). Off until credentials are saved.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-social.php',
			'class'       => 'TSOLO_Mod_Social',
			'collide'     => false,
		),
		'avatars'       => array(
			'label'       => __( 'Avatars', 'tso-login-organizer' ),
			'description' => __( 'Let users upload a local profile picture instead of only using Gravatar.', 'tso-login-organizer' ),
			'configure'   => __( 'Requires Profiles. After enabling both: open /profile/, upload under Profile picture. Optional max size on the Avatars tab.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable Profiles and Avatars, then Save modules (Avatars alone has nowhere to upload).', 'tso-login-organizer' ),
				__( 'Optional: set Max size (KB) below and Save module settings.', 'tso-login-organizer' ),
				/* translators: %s: profile URL */
				sprintf( __( 'While logged in, open the profile page: %s', 'tso-login-organizer' ), $profile_url ),
				__( 'Choose an image (JPG, PNG, GIF or WebP) under Profile picture and save. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user site-wide.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open profile page', 'tso-login-organizer' ) => $profile_url,
			),
			'compat'      => array(
				__( 'Requires the Profiles module.', 'tso-login-organizer' ),
				__( 'Other avatar plugins that filter get_avatar / get_avatar_url may override or conflict.', 'tso-login-organizer' ),
			),
			'requires'    => array( 'profiles' ),
			'file'        => 'class-tsolo-mod-avatars.php',
			'class'       => 'TSOLO_Mod_Avatars',
			'collide'     => false,
		),
		'favorites'     => array(
			'label'       => __( 'Favorites', 'tso-login-organizer' ),
			'description' => __( 'Logged-in users can save favorite posts and view them on a dedicated page.', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: a /favorites/ page is available. Open the Favorites tab to show controls on single posts.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable this module and Save modules. A virtual page is created (default /favorites/ — change under Core → URL slugs).', 'tso-login-organizer' ),
				__( 'On the Favorites tab, keep “Show on posts” checked to display Add/Remove on single blog posts for logged-in users.', 'tso-login-organizer' ),
				/* translators: %s: favorites URL */
				sprintf( __( 'Users manage the list at %s.', 'tso-login-organizer' ), $favorites_url ),
			),
			'test_links'  => array(
				__( 'Open favorites page', 'tso-login-organizer' ) => $favorites_url,
			),
			'compat'      => array(
				__( 'Buttons are injected into post content; page builders that replace the_content may hide them — use the favorites page URL as a fallback.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-favorites.php',
			'class'       => 'TSOLO_Mod_Favorites',
			'collide'     => false,
		),
		'mailchimp'     => array(
			'label'       => __( 'Mailchimp', 'tso-login-organizer' ),
			'description' => __( 'Optionally subscribe new registrants to a Mailchimp audience (API key + list ID).', 'tso-login-organizer' ),
			'configure'   => __( 'After enabling: open the Mailchimp tab for API key, list ID and the registration consent checkbox.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Create an API key at https://admin.mailchimp.com/account/api/ and copy your Audience (list) ID from Audience → Settings.', 'tso-login-organizer' ),
				__( 'Enable this module, paste API key and List ID, optionally keep “Ask for newsletter consent” on, Save module settings.', 'tso-login-organizer' ),
				__( 'On the registration form, users who tick the box are added to that audience after signup. No data is sent until keys are saved and the module is on.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Mailchimp API keys', 'tso-login-organizer' ) => 'https://admin.mailchimp.com/account/api/',
				__( 'Open register page', 'tso-login-organizer' ) => $register_url,
			),
			'compat'      => array(
				__( 'Sends subscriber email (and name if available) to Mailchimp’s API (see readme External services).', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-mailchimp.php',
			'class'       => 'TSOLO_Mod_Mailchimp',
			'collide'     => false,
		),
	);
}

/**
 * Detect collision plugins.
 *
 * @return array List of human-readable names.
 */
function tsolo_detect_collision_plugins() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$found  = array();
	$checks = array(
		'tso-swiss-knife-advanced-maintenance-developer-toolkit/tso-swiss-knife-advanced-maintenance-developer-toolkit.php' => 'TSO Swiss Knife (Login Protect)',
		'kadence-security/kadence-security.php'     => 'Kadence Security',
		'kadence-security/kadence-security-pro.php' => 'Kadence Security',
		'cleantalk-spam-protect/cleantalk.php'      => 'CleanTalk',
		'security-malware-firewall/security-malware-firewall.php' => 'CleanTalk Security',
	);
	foreach ( $checks as $file => $label ) {
		if ( is_plugin_active( $file ) ) {
			$found[ $label ] = $label;
		}
	}
	// Also detect Swiss by class / option presence.
	if ( class_exists( 'TSOSK_Mod_Login_Protect', false ) || get_option( 'tsosk_login_protect' ) ) {
		$found['TSO Swiss Knife (Login Protect)'] = 'TSO Swiss Knife (Login Protect)';
	}
	return array_values( $found );
}

/**
 * Load enabled modules.
 */
function tsolo_load_modules() {
	$flags = tsolo_get_modules();
	$defs  = tsolo_get_module_definitions();
	foreach ( $defs as $key => $def ) {
		if ( empty( $flags[ $key ] ) ) {
			continue;
		}
		$path = TSOLO_PATH . 'includes/modules/' . $def['file'];
		if ( ! is_readable( $path ) ) {
			continue;
		}
		require_once $path;
		if ( class_exists( $def['class'] ) && method_exists( $def['class'], 'get_instance' ) ) {
			call_user_func( array( $def['class'], 'get_instance' ) );
		}
	}
}
