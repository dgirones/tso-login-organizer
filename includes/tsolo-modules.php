<?php
/**
 * Feature modules loader and collision detection.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TSOLO_PATH . 'includes/tsolo-compat-scan.php';

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
			'howto'       => array(
				__( 'Each box wants a full address that starts with https:// (a path such as /shop/ is not enough). Empty means keep the WordPress default for that case.', 'tso-login-organizer' ),
				__( 'Default applies to every role that does not have its own address. A role box overrides Default only for people with that role.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Email confirmation sends a link. The new person cannot log in until they open it.', 'tso-login-organizer' ),
				__( 'Admin approval leaves the account pending on the Users screen until an administrator approves it.', 'tso-login-organizer' ),
				__( 'If either box is on, Core → “Log users in after registration” is ignored for that signup. The visitor stays logged out until the pending step is finished. If both boxes are off, the Core option applies as usual.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Private site is optional and separate from shortcodes: guests are sent to the login page, while login and register stay public.', 'tso-login-organizer' ),
				__( 'Shortcodes go inside a post or page (Shortcode block, or the text area in the classic editor). They hide only the text written between the opening tag and the closing tag. Examples are under Settings below.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Turn on lockouts, then set how many failures are allowed and how many minutes the lock lasts. Minimum password length 0 means no extra rule.', 'tso-login-organizer' ),
				__( 'Hide wp-login.php returns 404 for the default login file. Use it only together with this plugin’s frontend login address.', 'tso-login-organizer' ),
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
			'label'       => __( 'Captcha', 'tso-login-organizer' ),
			'description' => __( 'Protect login and registration forms with Google reCAPTCHA v2/v3 or Cloudflare Turnstile (needs site and secret keys).', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Choose a provider below. For Google, open https://www.google.com/recaptcha/admin and create a v2 checkbox or v3 key. For Turnstile, open the Cloudflare Turnstile dashboard.', 'tso-login-organizer' ),
				__( 'Paste the site key and the secret key, then save. Empty keys mean the check stays off. Do not enable more than one captcha plugin on the same forms.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Google reCAPTCHA admin', 'tso-login-organizer' ) => 'https://www.google.com/recaptcha/admin',
				__( 'Cloudflare Turnstile', 'tso-login-organizer' )   => 'https://dash.cloudflare.com/?to=/:account/turnstile',
				__( 'Open login page', 'tso-login-organizer' )        => $login_url,
			),
			'compat'      => array(
				__( 'Do not enable together with CleanTalk, Kadence captcha, or another captcha on the same forms (double challenges / failed verifies).', 'tso-login-organizer' ),
				__( 'Sends visitor IP and token to Google or Cloudflare for verification when forms are submitted (see readme External services).', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-captcha.php',
			'class'       => 'TSOLO_Mod_Captcha',
			'collide'     => true,
		),
		'fields'        => array(
			'label'       => __( 'Custom Fields', 'tso-login-organizer' ),
			'description' => __( 'Add extra fields to registration and/or the frontend profile (name, phone, URL, custom meta).', 'tso-login-organizer' ),
			'howto'       => array(
				__( 'Add a row per field: a machine key (letters/numbers), a label, type, and whether it appears on register and/or profile.', 'tso-login-organizer' ),
				__( 'Map can store into WordPress first name, last name, biographical info or website. Leave map empty to store as user meta (tsolo_cf_…).', 'tso-login-organizer' ),
				__( 'Profile fields need the Profiles module enabled. Registration fields work on their own.', 'tso-login-organizer' ),
			),
			'test_links'  => array(
				__( 'Open register page', 'tso-login-organizer' ) => $register_url,
				__( 'Open profile page', 'tso-login-organizer' )  => $profile_url,
			),
			'compat'      => array(
				__( 'Other registration-form plugins that also inject fields may show duplicates.', 'tso-login-organizer' ),
			),
			'file'        => 'class-tsolo-mod-fields.php',
			'class'       => 'TSOLO_Mod_Fields',
			'collide'     => false,
		),
		'profiles'      => array(
			'label'       => __( 'Profiles', 'tso-login-organizer' ),
			'description' => __( 'Frontend profile page so users can edit their account without using wp-admin.', 'tso-login-organizer' ),
			'howto'       => array(
				/* translators: %s: profile URL */
				sprintf( __( 'Logged-in users open %s to edit display name, email and password. The path is the Profile slug under Core.', 'tso-login-organizer' ), $profile_url ),
				__( 'Block wp-admin sends users who cannot edit posts away from /wp-admin/ to that profile page and hides the admin bar.', 'tso-login-organizer' ),
				__( 'Turn on Avatars as well if the profile page should accept a photo upload.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Use the placeholders listed under each field (for example {user_login}, {reset_url}). An empty field keeps the WordPress default text.', 'tso-login-organizer' ),
				__( 'Tick HTML emails only when the body contains HTML tags. Otherwise leave it off and write plain text.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Tick the roles that must use a second code, then save. People in other roles keep password-only login.', 'tso-login-organizer' ),
				__( 'Each person turns it on from their own profile page (Profiles) or from Users → Profile in wp-admin. Checking the box and saving creates a key the first time. They type that key into an app such as Google Authenticator.', 'tso-login-organizer' ),
				__( 'After that, login asks for the password and for the 6-digit code shown in the app.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Follow the numbered steps on each provider card. Copy the redirect URI shown there into that provider’s console.', 'tso-login-organizer' ),
				__( 'Paste the client ID (or app ID) and the secret for each provider you want. Leave a provider’s fields empty to hide its button.', 'tso-login-organizer' ),
				__( 'With “Link existing accounts” on (default), a returning social login that shares an email with an existing user signs into that account instead of creating a second one. Logged-in users can also connect or disconnect providers from their profile page.', 'tso-login-organizer' ),
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
			'howto'       => array(
				__( 'Avatars needs Profiles. The upload field is on the profile page, not on this screen. Max size (KB) below is the only setting here.', 'tso-login-organizer' ),
				/* translators: %s: profile URL */
				sprintf( __( 'While logged in, open %s. The page says whether a photo is already saved. Choose a JPG, PNG, GIF or WebP to replace it. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user.', 'tso-login-organizer' ), $profile_url ),
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
			'howto'       => array(
				__( 'Keep “Show on posts” checked to put Add/Remove at the end of each single post for logged-in users.', 'tso-login-organizer' ),
				/* translators: %s: favorites URL */
				sprintf( __( 'The saved list is the Favorites slug from Core, currently %s.', 'tso-login-organizer' ), $favorites_url ),
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
			'howto'       => array(
				__( 'Create an API key at https://admin.mailchimp.com/account/api/ and copy the Audience (list) ID from Audience → Settings.', 'tso-login-organizer' ),
				__( 'Paste the API key (it must end with your data center, e.g. -us21) and the list ID below. With “Ask for newsletter consent” on, only people who tick the box are added; you can also pre-check that box. When the checkbox is off, every new registrant is subscribed automatically. Nothing is sent while the key or the list ID is empty, and registration never waits on Mailchimp errors.', 'tso-login-organizer' ),
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
