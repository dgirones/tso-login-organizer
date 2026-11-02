=== TSO Login Organizer ===
Contributors: deadko
Donate link: https://ko-fi.com/deadko_cat
Tags: login, registration, frontend login, authentication, 2fa
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Your own login, registration and password-reset pages inside your site, instead of wp-login.php, plus optional 2FA, security and social login.

== Description ==

TSO Login Organizer gives your site its own login, registration and password-reset pages, at addresses you choose (by default `/login`, `/register` and `/lostpassword`), so visitors never see the standard WordPress login screen (`wp-login.php`). The pages match your theme and work with a shortcode, a widget or menu items.

On top of that you can switch on optional modules: redirect users by role after login, approve new registrations, restrict content by role, lock out brute-force attempts, add two-factor authentication (TOTP), let users edit their profile and avatar on the site, sign in with Google, Facebook or X, add favorites, and subscribe new users to Mailchimp. Every module is off until you turn it on.

Coming from Theme My Login? You can import its free settings and keep the `[theme-my-login]` shortcode working.

= Core =

* Login, register, lost password, reset password, dashboard and privacy confirmation pages
* Custom slugs, shortcode `[tso-login-organizer]` and Theme My Login alias `[theme-my-login]`
* Login with username, email, or either; email-only registration; user passwords; auto-login; AJAX
* Widget and navigation menu items
* Import settings from Theme My Login
* Bundled translations: Catalan (ca), Spanish (es_ES); English is the source language

= Optional modules (all off by default) =

* Redirection by role
* Moderation (email confirm / admin approval)
* Restrictions (private site / per-post roles)
* Security (brute-force lockouts, password length, hide wp-login.php) — may collide with TSO Swiss Knife Login Protect or Kadence Security
* reCAPTCHA — may collide with CleanTalk or Kadence
* Frontend profiles (optional wp-admin block)
* Custom HTML notifications
* TOTP 2FA — may collide with other 2FA plugins
* Social login (Google, Facebook, X)
* Avatar uploads under `uploads/tso-login-organizer/`
* Favorites
* Mailchimp opt-in

== External services ==

This plugin can connect to third-party services **only when the related module is enabled and credentials are saved by an administrator**:

1. **Google reCAPTCHA** (`https://www.google.com/recaptcha/api.js` and `https://www.google.com/recaptcha/api/siteverify`) — when the Captcha module is enabled with Google v2/v3 site/secret keys. Used to verify that form submissions are human. [Terms](https://policies.google.com/terms) · [Privacy](https://policies.google.com/privacy)
2. **Cloudflare Turnstile** (`https://challenges.cloudflare.com/turnstile/v0/api.js` and `https://challenges.cloudflare.com/turnstile/v0/siteverify`) — when the Captcha module provider is Turnstile with site/secret keys. Used to verify that form submissions are human. [Terms](https://www.cloudflare.com/website-terms/) · [Privacy](https://www.cloudflare.com/privacypolicy/)
3. **Google OAuth** (`https://accounts.google.com/` and `https://oauth2.googleapis.com/` / `https://www.googleapis.com/`) — when Social login Google keys are configured. Used to authenticate users. [Terms](https://policies.google.com/terms) · [Privacy](https://policies.google.com/privacy)
4. **Facebook Login** (`https://www.facebook.com/` and `https://graph.facebook.com/`) — when Social login Facebook keys are configured. [Terms](https://www.facebook.com/terms.php) · [Privacy](https://www.facebook.com/privacy/explanation)
5. **X (Twitter) OAuth** (`https://twitter.com/` and `https://api.twitter.com/`) — when Social login X keys are configured. [Terms](https://twitter.com/tos) · [Privacy](https://twitter.com/privacy)
6. **Mailchimp API** (`https://*.api.mailchimp.com/`) — when the Mailchimp module has an API key and list ID and the user opts in (or auto-subscribe is configured). Sends the registrant email/name to your audience. [Terms](https://mailchimp.com/legal/terms/) · [Privacy](https://www.intuit.com/privacy/statement/)

No remote calls run while those modules stay disabled or keys are empty.

== Installation ==

1. Upload the `tso-login-organizer` folder to `/wp-content/plugins/`
2. Activate the plugin
3. Open Tools → TSO Login Organizer
4. Optionally import Theme My Login settings, then deactivate Theme My Login
5. Enable only the modules you need (keep Security/Captcha/2FA off if Swiss Knife, Kadence Security or CleanTalk already cover them)

== Frequently Asked Questions ==

= Will this conflict with TSO Swiss Knife? =

Swiss Knife Login Protect can hide `wp-login.php` and lock out IPs. TSO Login Organizer Security does similar work and stays **off** by default. Use only one stack for hide-login / brute-force.

= How do I keep Theme My Login shortcodes working? =

`[theme-my-login]` is registered as an alias of `[tso-login-organizer]`.

= What happens when I import Theme My Login settings? =

Import copies AJAX, login/registration type, passwords, auto-login, and **all configured address slugs** (including profile and favorites when present) into TSO Login Organizer with the **same names**. If Theme My Login only stored page IDs, the page slug (`post_name`) is used. Optional modules are **not** turned on automatically — if profile or favorites URLs were used, enable **Profiles** / **Favorites** under Modules after import so those addresses work. Then deactivate Theme My Login once you have verified the URLs.

= Is it correct to say which plugin this migrates from? =

Yes. Theme My Login is GPL, so importing its free settings and keeping the `[theme-my-login]` shortcode as an alias is allowed if you keep the GPL license. This plugin is independent: it is not Theme My Login and is not an official product of that project’s authors. Their name is their trademark — use it only to describe migration, not as if this were the same product.

= I turned on "Hide wp-login.php" and cannot log in. =

The plugin does not hide wp-login.php while the friendly login URL is not working (Permalinks set to Plain or the login rule missing). If you are still locked out, add `define( 'TSOLO_ALLOW_WP_LOGIN', true );` to wp-config.php, log in, fix the settings and remove the line.

= What does the CA / ES / EN switcher store? =

Only the administrator’s interface language preference (Catalan, Spanish, or English) as user meta (`tso_login_organizer_ui_lang`). Nothing is sent to external servers for that setting. The “Support this plugin” link opens Ko-fi in a new tab.

== Screenshots ==

1. Core settings: AJAX forms, who can register, login and registration type, user-chosen passwords, auto-login and default role.
2. Migration tab: import the free settings of Theme My Login, with a checklist and a friendly URL check.
3. Social login module: sign in with Google, Facebook or X using your own OAuth credentials, with step-by-step setup and the callback URL to copy.
4. Frontend profile page: users edit their name, email, password and avatar on the site and can turn on two-factor authentication.
5. Captcha module: Google reCAPTCHA v2/v3 or Cloudflare Turnstile, with compatibility warnings for other plugins.
6. Modules tab: every optional module (redirection, moderation, restrictions, security, captcha, custom fields, profiles and more) can be turned on or off.

== Changelog ==

= 1.0.1 =
* Two-factor: wrong codes are limited, a code cannot be reused, XML-RPC needs an application password and the code field also shows on wp-login.php.
* Security: old lockout attempts are purged, and Hide wp-login.php no longer blocks when the plugin login page is not reachable.
* Profile: the current password is needed to change email, password or turn two-factor off.
* Password reset: an empty password now shows an error.
* Social login and email confirmation: clear message when cancelled or when the link is invalid.
* Module settings keep the line breaks of email texts.
* Avatars use random file names and are removed when the user is deleted.
* Secret keys are masked in the settings screen.
* Default role can only be set to a role the admin may assign.
* Uninstall also removes the new two-factor and restriction data.
* Uninstall keeps your data unless "Delete all plugin data" is ticked in Core settings.
* Security: choose the visitor IP source (Cloudflare, X-Forwarded-For, X-Real-IP) and list IPs that are never locked out.
* Two-factor: users of a role that requires it are kept on their profile until they enable it.
* Restrictions: the role box is available on every public post type and restricted content is blocked completely (login redirect or 403).
* Email confirmation links expire after 48 hours and a new link is sent automatically; approved users get an email.
* Social login: administrator accounts are never linked by email and the requested redirect is kept.
* Captcha: new filter tsolo_captcha_fail_open for provider outages.
* Mailchimp subscriptions run in the background so registration is not delayed.
* Avatars are resized to 256 px.
* Reserved page slugs are refused and page lookups are cached.
* Favorites: the button recovers by itself when a cached page has an expired security token.

= 1.0.0 =
* First plugin version.
