=== TSO Login Organizer ===
Contributors: deadko
Tags: login, registration, frontend login, authentication, 2fa
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend login, registration and account pages with optional modules. Can import free settings from Theme My Login.

== Description ==

TSO Login Organizer replaces the default WordPress login experience with themed frontend pages at your own URLs (default `/login`, `/register`, `/lostpassword`). Optional modules add common account features. If you used Theme My Login, you can import its free settings and keep the `[theme-my-login]` shortcode working.

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

= Is it correct to say which plugin this migrates from? =

Yes. Theme My Login is GPL, so importing its free settings and keeping the `[theme-my-login]` shortcode as an alias is allowed if you keep the GPL license. This plugin is independent: it is not Theme My Login and is not an official product of that project’s authors. Their name is their trademark — use it only to describe migration, not as if this were the same product.

= What does the CA / ES / EN switcher store? =

Only the administrator’s interface language preference (Catalan, Spanish, or English) as user meta (`tso_login_organizer_ui_lang`). Nothing is sent to external servers for that setting. The “Support this plugin” link opens Ko-fi in a new tab.

== Changelog ==

= 1.0.0 =
* Initial release: frontend login forms, Theme My Login free-settings import, optional modules with collision-aware defaults.
* Clearer core and module help, friendly-URL check, installed-plugin incompatibility scan, moderation respects auto-login.
* Mobile-friendly front forms: larger inputs (no iOS zoom), full-width buttons, touch-sized account links.
* Profile and 2FA help strings translated (CA/ES); dashboard links no longer nest a second `.tsolo` block.
* Core: default registration role, custom form texts, logo / max-width / extra CSS class branding.
* Custom Fields module (register + profile); Captcha supports reCAPTCHA v2/v3 and Cloudflare Turnstile.
* Social: link existing accounts by email (default on); connect/disconnect from profile.
