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
			'configure'   => __( 'Set default and per-role URLs for after login, logout and registration.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Under each heading (After login, After logout, After registration), paste a full URL in Default (all roles) — for example https://yoursite.com/welcome/ after login.', 'tso-login-organizer' ),
				__( 'Use a role row only when that role should go somewhere else (example: Subscriber → https://yoursite.com/members/, leave Administrator empty to use the default).', 'tso-login-organizer' ),
				__( 'Leave any field empty to keep the WordPress default for that case (no redirect from this module).', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'Other login/redirect plugins (membership, LMS, WooCommerce “redirect after login”) may override these URLs depending on filter priority.', 'tso-login-organizer' ),
			),
			'compat_plugins' => array(
				'woocommerce/woocommerce.php'                    => 'WooCommerce',
				'memberpress/memberpress.php'                      => 'MemberPress',
				'restrict-content-pro/restrict-content-pro.php'    => 'Restrict Content Pro',
				'paid-memberships-pro/paid-memberships-pro.php'    => 'Paid Memberships Pro',
				'ultimate-member/ultimate-member.php'            => 'Ultimate Member',
				'wp-user-frontend/wp-user-frontend.php'           => 'WP User Frontend',
			),
			'file'        => 'class-tsolo-mod-redirection.php',
			'class'       => 'TSOLO_Mod_Redirection',
			'collide'     => false,
		),
		'moderation'    => array(
			'label'       => __( 'Moderation', 'tso-login-organizer' ),
			'description' => __( 'Require email confirmation and/or admin approval before new accounts can log in.', 'tso-login-organizer' ),
			'configure'   => __( 'Email confirmation and/or admin approval before the first successful login.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Turn on Email confirmation and/or Admin approval below, then Save module settings.', 'tso-login-organizer' ),
				__( 'Email confirmation: the user must click the link in the message before wp_authenticate_user allows login.', 'tso-login-organizer' ),
				__( 'Admin approval: the account stays blocked until an administrator approves it under Users.', 'tso-login-organizer' ),
				__( 'These checks apply on every login attempt. They are separate from Core → Log users in after registration — keep that option off when moderation is on, or new users may get a session cookie before they are approved.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open Users screen', 'tso-login-organizer' ) => admin_url( 'users.php' ),
				__( 'Open register page', 'tso-login-organizer' ) => $register_url,
			),
			'compat'      => array(
				__( 'Conflicts with other registration-approval plugins that also block wp_authenticate_user.', 'tso-login-organizer' ),
			),
			'compat_plugins' => array(
				'new-user-approve/new-user-approve.php'           => 'New User Approve',
				'user-approval/user-approval.php'                   => 'User Approval',
				'wp-user-approval/wp-user-approval.php'           => 'WP User Approval',
				'user-verification/user-verification.php'         => 'User Verification',
				'ultimate-member/ultimate-member.php'             => 'Ultimate Member',
				'profile-builder/index.php'                       => 'Profile Builder',
				'pie-register/pie-register.php'                     => 'Pie Register',
			),
			'file'        => 'class-tsolo-mod-moderation.php',
			'class'       => 'TSOLO_Mod_Moderation',
			'collide'     => false,
		),
		'restrictions'  => array(
			'label'       => __( 'Restrictions', 'tso-login-organizer' ),
			'description' => __( 'Make the site private (login required) and restrict content with shortcodes.', 'tso-login-organizer' ),
			'configure'   => __( 'Optional private site plus shortcodes in page/post content.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Optional: tick Private site so guests must log in before seeing any page (login/register URLs stay reachable).', 'tso-login-organizer' ),
				__( 'Shortcodes go in the WordPress editor (block “Shortcode” or Classic editor) on pages and posts — not in this settings screen.', 'tso-login-organizer' ),
				__( 'Example — members-only paragraph: [tsolo-logged-in]Welcome back, member content here.[/tsolo-logged-in]', 'tso-login-organizer' ),
				__( 'Example — teaser for guests: [tsolo-logged-out]Please log in to read more.[/tsolo-logged-out]', 'tso-login-organizer' ),
				__( 'Example — WooCommerce customers only: [tsolo-role role="customer"]Your order history…[/tsolo-role]', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'Private site redirects guests site-wide — do not combine with another “force login” / members-only plugin.', 'tso-login-organizer' ),
				__( 'Caching plugins may serve public HTML to guests; exclude restricted pages or disable cache for logged-out users when testing.', 'tso-login-organizer' ),
			),
			'compat_plugins' => array(
				'members/members.php'                             => 'Members',
				'restrict-user-access/restrict-user-access.php'   => 'Restrict User Access',
				'password-protected/password-protected.php'       => 'Password Protected',
				'coming-soon/coming-soon.php'                     => 'Coming Soon',
				'ultimate-member/ultimate-member.php'             => 'Ultimate Member',
			),
			'file'        => 'class-tsolo-mod-restrictions.php',
			'class'       => 'TSOLO_Mod_Restrictions',
			'collide'     => false,
		),
		'security'      => array(
			'label'       => __( 'Security', 'tso-login-organizer' ),
			'description' => __( 'Lock accounts after failed logins, enforce password length, and optionally hide wp-login.php.', 'tso-login-organizer' ),
			'configure'   => __( 'Lockouts, password length and optional hide wp-login.php.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Enable only if Swiss Knife Login Protect / Kadence Security are not already doing the same job.', 'tso-login-organizer' ),
				__( 'Enable lockouts, set max attempts and lockout minutes, optional min password length.', 'tso-login-organizer' ),
				__( 'Hide wp-login.php returns 404 on the default login file — use only with this plugin’s frontend login URL and no other custom-login plugins.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open login page', 'tso-login-organizer' ) => $login_url,
			),
			'compat'      => array(
				__( 'May conflict with TSO Swiss Knife Login Protect and Kadence Security (lockouts / hide login).', 'tso-login-organizer' ),
				__( 'Hide wp-login.php conflicts with any plugin that renames or protects wp-login.php.', 'tso-login-organizer' ),
			),
			'compat_plugins' => array(
				'tso-swiss-knife-advanced-maintenance-developer-toolkit/tso-swiss-knife-advanced-maintenance-developer-toolkit.php' => 'TSO Swiss Knife',
				'kadence-security/kadence-security.php'         => 'Kadence Security',
				'kadence-security/kadence-security-pro.php'       => 'Kadence Security',
				'wordfence/wordfence.php'                         => 'Wordfence',
				'wps-hide-login/wps-hide-login.php'               => 'WPS Hide Login',
				'hide-login-page/hide-login-page.php'             => 'Hide Login Page',
				'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			),
			'file'        => 'class-tsolo-mod-security.php',
			'class'       => 'TSOLO_Mod_Security',
			'collide'     => true,
		),
		'captcha'       => array(
			'label'       => __( 'reCAPTCHA', 'tso-login-organizer' ),
			'description' => __( 'Protect login and registration forms with Google reCAPTCHA (needs site and secret keys).', 'tso-login-organizer' ),
			'configure'   => __( 'Google reCAPTCHA v2 site and secret keys.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Open https://www.google.com/recaptcha/admin and create a reCAPTCHA v2 "I\'m not a robot" key for your domain.', 'tso-login-organizer' ),
				__( 'Paste Site key and Secret key below, then Save module settings.', 'tso-login-organizer' ),
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
			'compat_plugins' => array(
				'cleantalk-spam-protect/cleantalk.php'            => 'CleanTalk',
				'security-malware-firewall/security-malware-firewall.php' => 'CleanTalk Security',
				'kadence-security/kadence-security.php'           => 'Kadence Security',
				'advanced-google-recaptcha/advanced-google-recaptcha.php' => 'Advanced Google reCAPTCHA',
				'google-captcha/google-captcha.php'                 => 'Google Captcha (BestWebSoft)',
			),
			'file'        => 'class-tsolo-mod-captcha.php',
			'class'       => 'TSOLO_Mod_Captcha',
			'collide'     => true,
		),
		'profiles'      => array(
			'label'       => __( 'Profiles', 'tso-login-organizer' ),
			'description' => __( 'Frontend profile page so users can edit their account without using wp-admin.', 'tso-login-organizer' ),
			'configure'   => __( 'Frontend profile URL (default /profile/) and optional wp-admin block.', 'tso-login-organizer' ),
			'howto'       => array(
				/* translators: %s: profile URL */
				sprintf( __( 'Logged-in users open %s to edit display name, email and password (slug under Core → URL slugs).', 'tso-login-organizer' ), $profile_url ),
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
			'configure'   => __( 'Custom welcome and password-reset email text.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Edit subjects and bodies below, then Save module settings.', 'tso-login-organizer' ),
				__( 'Use placeholders listed under each field (e.g. {user_login}, {reset_url}). Empty fields keep the WordPress default text.', 'tso-login-organizer' ),
				__( 'HTML emails: tick the option only if your body contains HTML; otherwise leave plain text.', 'tso-login-organizer' ),
			),
			'compat'      => array(
				__( 'SMTP / email-template plugins may still wrap or override outgoing mail after this filter runs.', 'tso-login-organizer' ),
			),
			'compat_plugins' => array(
				'wp-mail-smtp/wp_mail_smtp.php'                   => 'WP Mail SMTP',
				'easy-wp-smtp/easy-wp-smtp.php'                   => 'Easy WP SMTP',
				'post-smtp/postman-smtp.php'                      => 'Post SMTP',
			),
			'file'        => 'class-tsolo-mod-notifications.php',
			'class'       => 'TSOLO_Mod_Notifications',
			'collide'     => false,
		),
		'two_factor'    => array(
			'label'       => __( 'Two-Factor Authentication', 'tso-login-organizer' ),
			'description' => __( 'TOTP authenticator codes for selected roles after password login.', 'tso-login-organizer' ),
			'configure'   => __( 'Require TOTP codes for selected roles after password login.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Keep off if another 2FA plugin is already active on this site.', 'tso-login-organizer' ),
				__( 'Tick the roles that must use 2FA, then Save module settings.', 'tso-login-organizer' ),
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
			'compat_plugins' => array(
				'two-factor/two-factor.php'                       => 'Two Factor',
				'google-authenticator/google-authenticator.php'     => 'Google Authenticator',
				'miniorange-2-factor-authentication/miniorange_2_factor_settings.php' => 'miniOrange 2FA',
				'wordfence/wordfence.php'                         => 'Wordfence (2FA)',
				'wp-2fa/wp-2fa.php'                               => 'WP 2FA',
			),
			'file'        => 'class-tsolo-mod-two-factor.php',
			'class'       => 'TSOLO_Mod_Two_Factor',
			'collide'     => true,
		),
		'social'        => array(
			'label'       => __( 'Social Login', 'tso-login-organizer' ),
			'description' => __( 'Sign in with Google, Facebook or X using OAuth app credentials you provide.', 'tso-login-organizer' ),
			'configure'   => __( 'Google, Facebook and X OAuth client IDs and secrets.', 'tso-login-organizer' ),
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
			'compat_plugins' => array(
				'nextend-facebook-connect/nextend-facebook-connect.php' => 'Nextend Social Login',
				'miniorange-login-openid/miniorange_openid_sso_settings.php' => 'miniOrange Social Login',
				'wordpress-social-login/wp-social-login.php'      => 'WordPress Social Login',
				'super-socializer/super_socializer.php'           => 'Super Socializer',
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
				__( 'Requires the Profiles module (Avatars alone has nowhere to upload).', 'tso-login-organizer' ),
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
			'compat_plugins' => array(
				'wp-user-avatar/wp-user-avatar.php'               => 'ProfilePress / WP User Avatar',
				'simple-local-avatars/simple-local-avatars.php'   => 'Simple Local Avatars',
				'basic-user-avatars/basic-user-avatars.php'       => 'Basic User Avatars',
			),
			'requires'    => array( 'profiles' ),
			'file'        => 'class-tsolo-mod-avatars.php',
			'class'       => 'TSOLO_Mod_Avatars',
			'collide'     => false,
		),
		'favorites'     => array(
			'label'       => __( 'Favorites', 'tso-login-organizer' ),
			'description' => __( 'Logged-in users can save favorite posts and view them on a dedicated page.', 'tso-login-organizer' ),
			'configure'   => __( 'Favorites list URL (default /favorites/) and optional buttons on posts.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Default list URL is /favorites/ (change under Core → URL slugs).', 'tso-login-organizer' ),
				__( 'Keep “Show on posts” checked to display Add/Remove on single blog posts for logged-in users.', 'tso-login-organizer' ),
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
			'configure'   => __( 'Mailchimp API key, audience ID and registration checkbox.', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Create an API key at https://admin.mailchimp.com/account/api/ and copy your Audience (list) ID from Audience → Settings.', 'tso-login-organizer' ),
				__( 'Paste API key and List ID below, optionally keep “Ask for newsletter consent” on, then Save module settings.', 'tso-login-organizer' ),
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
 * Whether a plugin bootstrap file is active.
 *
 * @param string $plugin_file Plugin path relative to wp-content/plugins.
 * @return bool
 */
function tsolo_is_plugin_file_active( $plugin_file ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	return is_plugin_active( $plugin_file );
}

/**
 * Detect active plugins from a file => label map.
 *
 * @param array<string,string> $map Plugin bootstrap paths.
 * @return array<int,string> Human-readable plugin names.
 */
function tsolo_detect_plugins_from_map( $map ) {
	$found = array();
	if ( ! is_array( $map ) ) {
		return $found;
	}
	foreach ( $map as $file => $label ) {
		if ( tsolo_is_plugin_file_active( $file ) ) {
			$found[ $label ] = $label;
		}
	}
	return array_values( $found );
}

/**
 * Detect installed plugins that may conflict with one module.
 *
 * @param string $module_key Module key.
 * @return array<int,string>
 */
function tsolo_detect_module_compat_plugins( $module_key ) {
	$defs = tsolo_get_module_definitions();
	if ( ! isset( $defs[ $module_key ] ) || empty( $defs[ $module_key ]['compat_plugins'] ) ) {
		return array();
	}
	return tsolo_detect_plugins_from_map( $defs[ $module_key ]['compat_plugins'] );
}

/**
 * Detect collision plugins (Security / Captcha / 2FA stack).
 *
 * @return array List of human-readable names.
 */
function tsolo_detect_collision_plugins() {
	$map = array();
	foreach ( array( 'security', 'captcha', 'two_factor' ) as $key ) {
		foreach ( tsolo_detect_module_compat_plugins( $key ) as $label ) {
			$map[ $label ] = $label;
		}
	}
	if ( class_exists( 'TSOSK_Mod_Login_Protect', false ) || get_option( 'tsosk_login_protect' ) ) {
		$map['TSO Swiss Knife (Login Protect)'] = 'TSO Swiss Knife (Login Protect)';
	}
	return array_values( $map );
}

/**
 * Pretty permalink status for virtual URLs.
 *
 * @return array{ok:bool,message:string,structure:string,settings_url:string,fix_steps:array<int,string>}
 */
function tsolo_get_pretty_permalink_status() {
	$structure    = (string) get_option( 'permalink_structure' );
	$settings_url = admin_url( 'options-permalink.php' );
	$plain        = '' === $structure;
	if ( $plain ) {
		return array(
			'ok'           => false,
			'structure'    => '',
			'settings_url' => $settings_url,
			'message'      => __( 'Permalinks are set to Plain. Virtual login URLs (/login/, etc.) will not work until you enable pretty permalinks.', 'tso-login-organizer' ),
			'fix_steps'    => array(
				sprintf(
					/* translators: %s: Settings → Permalinks URL */
					__( 'Open Settings → Permalinks: %s', 'tso-login-organizer' ),
					$settings_url
				),
				__( 'Choose “Post name” (recommended) or another structure — not “Plain”.', 'tso-login-organizer' ),
				__( 'Click Save Changes at the bottom (you do not need to edit the custom structure field).', 'tso-login-organizer' ),
				__( 'Return here and open a URL from the table below to confirm the form loads.', 'tso-login-organizer' ),
			),
		);
	}
	$display = $structure ? $structure : __( 'Post name (default)', 'tso-login-organizer' );
	return array(
		'ok'           => true,
		'structure'    => $structure,
		'settings_url' => $settings_url,
		'message'      => sprintf(
			/* translators: %s: permalink structure */
			__( 'Pretty permalinks are enabled (%s). Virtual URLs should resolve correctly.', 'tso-login-organizer' ),
			$display
		),
		'fix_steps'    => array(),
	);
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
