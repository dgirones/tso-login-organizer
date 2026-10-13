<?php
/**
 * Detect installed plugins that may clash with a module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Known incompatibility signatures, one list per module.
 *
 * Slugs are the plugin directory (or the main file basename without .php).
 * Needles are matched against the plugin name and description.
 * Hooks are searched in plugin PHP (not vendor) and only count when hook context
 * also appears, so a shared WordPress hook does not flag unrelated plugins.
 *
 * @return array<string,array<string,mixed>>
 */
function tsolo_get_incompatibility_rules() {
	$two_factor_slugs = array(
		'two-factor',
		'wp-2fa',
		'wp-2fa-premium',
		'wordfence-login-security',
		'miniorange-2-factor-authentication',
		'google-authenticator',
		'wp-google-authenticator',
		'two-factor-authentication',
		'wordfence',
	);
	$moderation_slugs = array(
		'new-user-approve',
		'wp-approve-user',
		'unconfirmed',
		'user-moderation',
		'bp-registration-options',
		'confirm-user-registration',
		'wpum-user-verification',
		'wp-user-manager',
		'ultimate-member',
		'profile-builder',
		'profile-builder-pro',
		'buddypress',
		'theme-my-login',
	);

	return array(
		'moderation'    => array(
			'reason'       => __( 'Also blocks login until a registration is approved (wp_authenticate_user). Keep only one approval plugin active.', 'tso-login-organizer' ),
			'slugs'        => $moderation_slugs,
			'exclude'      => $two_factor_slugs,
			'needles'      => array(
				'new user approve',
				'approve user',
				'user approval',
				'registration approval',
				'moderate registration',
				'moderate new',
				'unconfirmed',
			),
			'hooks'        => array( 'wp_authenticate_user' ),
			'hook_context' => array( 'approv', 'moderat', 'pending', 'unconfirmed' ),
		),
		'restrictions'  => array(
			'reason'  => __( 'Also forces login or restricts content. Guests may be redirected twice or locked out of pages.', 'tso-login-organizer' ),
			'slugs'   => array(
				'wp-force-login',
				'force-login',
				'members',
				'restrict-content',
				'restrict-content-pro',
				'paid-memberships-pro',
				'restrict-user-access',
				'members-only',
			),
			'needles' => array(
				'force login',
				'members only',
				'restrict content',
				'private site',
			),
		),
		'security'      => array(
			'reason'  => __( 'Also locks out failed logins or hides wp-login.php. Running both can block legitimate users or hide the login URL twice.', 'tso-login-organizer' ),
			'slugs'   => array(
				'tso-swiss-knife-advanced-maintenance-developer-toolkit',
				'kadence-security',
				'wordfence',
				'wordfence-login-security',
				'all-in-one-wp-security-and-firewall',
				'better-wp-security',
				'solid-security',
				'wps-hide-login',
				'loginizer',
				'limit-login-attempts-reloaded',
				'wp-cerber',
				'security-malware-firewall',
			),
			'needles' => array(
				'hide wp-login',
				'limit login attempts',
				'brute force',
				'rename wp-login',
			),
		),
		'captcha'       => array(
			'reason'       => __( 'Also adds a captcha or anti-spam check on forms. Two checks on the same form often fail verification.', 'tso-login-organizer' ),
			'slugs'        => array(
				'cleantalk-spam-protect',
				'google-captcha',
				'advanced-google-recaptcha',
				'recaptcha-woo',
				'invisible-recaptcha',
				'kadence-blocks',
				'kadence-security',
			),
			'needles'      => array(
				'recaptcha',
				'cleantalk',
				'anti-spam',
				'antispam',
			),
			'hooks'        => array( 'g-recaptcha', 'recaptcha/api', 'cleantalk' ),
			'hook_context' => array( 'recaptcha', 'cleantalk', 'captcha' ),
		),
		'profiles'      => array(
			'reason'  => __( 'Also blocks wp-admin or replaces the profile screen. Subscribers may be unable to reach either profile.', 'tso-login-organizer' ),
			'slugs'   => array(
				'remove-dashboard-access',
				'remove-dashboard-access-for-non-admins',
				'wp-frontend-profile',
				'user-profile-picture',
			),
			'needles' => array(
				'disable wp-admin',
				'block wp-admin',
				'redirect from wp-admin',
				'frontend profile',
			),
		),
		'notifications' => array(
			'reason'  => __( 'Also replaces outgoing email content. The message saved here may be wrapped or discarded.', 'tso-login-organizer' ),
			'slugs'   => array(
				'wp-html-mail',
				'email-templates',
				'kadence-woocommerce-email-designer',
			),
			'needles' => array(
				'email template',
				'html email',
			),
		),
		'two_factor'    => array(
			'reason'       => __( 'Also asks for a second code at login. Two authenticators on the same account block sign-in.', 'tso-login-organizer' ),
			'slugs'        => $two_factor_slugs,
			'exclude'      => $moderation_slugs,
			'needles'      => array(
				'two-factor',
				'two factor',
				'2fa',
				'totp',
				'authenticator',
			),
			'hooks'        => array( 'wp_authenticate_user', 'totp' ),
			'hook_context' => array( 'totp', 'authenticator', 'two-factor', 'two factor', '2fa' ),
		),
		'social'        => array(
			'reason'  => __( 'Also adds social login buttons or OAuth callbacks. Buttons can be duplicated or the return URL can be stolen by the other plugin.', 'tso-login-organizer' ),
			'slugs'   => array(
				'nextend-facebook-connect',
				'super-socializer',
				'wordpress-social-login',
				'miniorange-login-openid',
				'social-login',
				'nextend-social-login',
			),
			'needles' => array(
				'social login',
				'login with google',
				'login with facebook',
				'sign in with google',
			),
		),
		'avatars'       => array(
			'reason'  => __( 'Also replaces the profile photo (get_avatar). One of the two pictures will win and the other upload may look ignored.', 'tso-login-organizer' ),
			'slugs'   => array(
				'simple-local-avatars',
				'one-user-avatar',
				'wp-user-avatar',
				'basic-user-avatars',
				'avatar-manager',
			),
			'needles' => array(
				'local avatar',
				'custom avatar',
				'user avatar',
			),
		),
		'favorites'     => array(
			'reason'  => __( 'Also stores favorite posts. The buttons and the list page may disagree about what is saved.', 'tso-login-organizer' ),
			'slugs'   => array(
				'wp-favorite-posts',
				'simple-favorites',
				'favorites',
			),
			'needles' => array(
				'favorite posts',
				'favourite posts',
			),
		),
		'mailchimp'     => array(
			'reason'  => __( 'Also sends new users to Mailchimp. The same person can be subscribed twice.', 'tso-login-organizer' ),
			'slugs'   => array(
				'mailchimp-for-wp',
				'mailchimp',
				'mc4wp-mailchimp-for-wordpress',
			),
			'needles' => array(
				'mailchimp',
			),
		),
		'redirection'   => array(
			'reason'  => __( 'Also sends users somewhere after login, logout or registration. The last plugin to run decides the final URL.', 'tso-login-organizer' ),
			'slugs'   => array(
				'peters-login-redirect',
				'wps-hide-login',
				'login-redirect',
				'theme-my-login',
			),
			'needles' => array(
				'redirect after login',
				'login redirect',
				'redirect after logout',
			),
		),
	);
}

/**
 * Installed plugins (not this plugin).
 *
 * @return array<int,array<string,mixed>>
 */
function tsolo_compat_plugin_catalog() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$own    = plugin_basename( TSOLO_FILE );
	$active = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$network = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network ) ) {
			$active = array_merge( $active, array_keys( $network ) );
		}
	}
	$catalog = array();
	foreach ( get_plugins() as $file => $data ) {
		if ( $file === $own ) {
			continue;
		}
		$slug = dirname( $file );
		if ( '.' === $slug ) {
			$slug = basename( $file, '.php' );
		}
		$name = isset( $data['Name'] ) ? (string) $data['Name'] : $slug;
		$desc = isset( $data['Description'] ) ? (string) $data['Description'] : '';
		$catalog[] = array(
			'file'   => $file,
			'slug'   => $slug,
			'name'   => $name,
			'active' => in_array( $file, $active, true ),
			'text'   => strtolower( $name . ' ' . $desc ),
		);
	}
	return $catalog;
}

/**
 * Lowercased PHP sources for one plugin, skipping vendor trees.
 *
 * @param string $plugin_file Plugin basename.
 * @return string
 */
function tsolo_compat_plugin_source_blob( $plugin_file ) {
	$main = wp_normalize_path( WP_PLUGIN_DIR . '/' . $plugin_file );
	$dir  = wp_normalize_path( dirname( $main ) );
	$paths = array( $main );
	if ( is_dir( $dir ) && wp_normalize_path( WP_PLUGIN_DIR ) !== $dir ) {
		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
			);
			$count = 0;
			foreach ( $iterator as $fileinfo ) {
				if ( $count >= 40 ) {
					break;
				}
				if ( ! $fileinfo->isFile() ) {
					continue;
				}
				$path = wp_normalize_path( $fileinfo->getPathname() );
				if ( '.php' !== substr( $path, -4 ) ) {
					continue;
				}
				if ( preg_match( '#/(vendor|node_modules|languages|assets|tests|test)/#', $path ) ) {
					continue;
				}
				if ( $fileinfo->getSize() > 250000 ) {
					continue;
				}
				$paths[] = $path;
				++$count;
			}
		} catch ( Exception $e ) {
			unset( $e );
		}
	}
	$blob = '';
	foreach ( array_unique( $paths ) as $path ) {
		if ( ! is_readable( $path ) ) {
			continue;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read-only local scan of installed plugin PHP to detect hook clashes.
		$chunk = file_get_contents( $path );
		if ( is_string( $chunk ) ) {
			$blob .= "\n" . $chunk;
		}
		if ( strlen( $blob ) > 400000 ) {
			break;
		}
	}
	return strtolower( $blob );
}

/**
 * Whether one catalog row matches one rule.
 *
 * @param array  $plugin Plugin row.
 * @param array  $rule   Rule.
 * @param string $blob   Optional lowercased sources.
 * @return bool
 */
function tsolo_compat_plugin_matches_rule( $plugin, $rule, $blob ) {
	$slug    = isset( $plugin['slug'] ) ? (string) $plugin['slug'] : '';
	$exclude = isset( $rule['exclude'] ) && is_array( $rule['exclude'] ) ? $rule['exclude'] : array();
	if ( in_array( $slug, $exclude, true ) ) {
		return false;
	}
	$slugs = isset( $rule['slugs'] ) && is_array( $rule['slugs'] ) ? $rule['slugs'] : array();
	if ( in_array( $slug, $slugs, true ) ) {
		return true;
	}
	$text    = isset( $plugin['text'] ) ? (string) $plugin['text'] : '';
	$needles = isset( $rule['needles'] ) && is_array( $rule['needles'] ) ? $rule['needles'] : array();
	foreach ( $needles as $needle ) {
		$needle = strtolower( (string) $needle );
		if ( '' !== $needle && false !== strpos( $text, $needle ) ) {
			return true;
		}
	}
	$hooks = isset( $rule['hooks'] ) && is_array( $rule['hooks'] ) ? $rule['hooks'] : array();
	if ( ! $hooks || '' === $blob ) {
		return false;
	}
	$context = isset( $rule['hook_context'] ) && is_array( $rule['hook_context'] ) ? $rule['hook_context'] : array();
	foreach ( $hooks as $hook ) {
		$hook = strtolower( (string) $hook );
		if ( '' === $hook || false === strpos( $blob, $hook ) ) {
			continue;
		}
		if ( ! $context ) {
			return true;
		}
		foreach ( $context as $ctx ) {
			$ctx = strtolower( (string) $ctx );
			if ( '' !== $ctx && ( false !== strpos( $blob, $ctx ) || false !== strpos( $text, $ctx ) ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Cached map of module key => matching installed plugins.
 *
 * @return array<string,array<int,array<string,mixed>>>
 */
function tsolo_get_incompatibility_report() {
	$plugins = tsolo_compat_plugin_catalog();
	$rules   = tsolo_get_incompatibility_rules();
	$files   = array();
	foreach ( $plugins as $plugin ) {
		$files[] = $plugin['file'];
	}
	$key    = 'tsolo_incompat_' . md5( (string) filemtime( __FILE__ ) . '|' . wp_json_encode( $files ) );
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$needs_source = false;
	foreach ( $rules as $rule ) {
		if ( ! empty( $rule['hooks'] ) ) {
			$needs_source = true;
			break;
		}
	}
	$blobs = array();
	if ( $needs_source ) {
		foreach ( $plugins as $plugin ) {
			$blobs[ $plugin['file'] ] = tsolo_compat_plugin_source_blob( $plugin['file'] );
		}
	}

	$report = array();
	foreach ( $rules as $module => $rule ) {
		$report[ $module ] = array();
		foreach ( $plugins as $plugin ) {
			$blob = isset( $blobs[ $plugin['file'] ] ) ? $blobs[ $plugin['file'] ] : '';
			if ( ! tsolo_compat_plugin_matches_rule( $plugin, $rule, $blob ) ) {
				continue;
			}
			$report[ $module ][] = array(
				'name'   => $plugin['name'],
				'active' => ! empty( $plugin['active'] ),
				'file'   => $plugin['file'],
			);
		}
	}
	$swiss_known = false;
	if ( ! empty( $report['security'] ) ) {
		foreach ( $report['security'] as $row ) {
			if ( ! empty( $row['file'] ) && false !== strpos( (string) $row['file'], 'tso-swiss-knife' ) ) {
				$swiss_known = true;
				break;
			}
		}
	}
	if ( ! $swiss_known && ( class_exists( 'TSOSK_Mod_Login_Protect', false ) || get_option( 'tsosk_login_protect' ) ) ) {
		$report['security'][] = array(
			'name'   => 'TSO Swiss Knife (Login Protect)',
			'active' => true,
			'file'   => 'tso-swiss-knife-advanced-maintenance-developer-toolkit/tso-swiss-knife-advanced-maintenance-developer-toolkit.php',
		);
	}
	set_transient( $key, $report, 12 * HOUR_IN_SECONDS );
	return $report;
}

/**
 * Human list for the top-of-page warning (active clashes on security-like modules).
 *
 * @return array<int,string>
 */
function tsolo_detect_collision_plugins() {
	$report = tsolo_get_incompatibility_report();
	$names  = array();
	foreach ( array( 'security', 'captcha', 'two_factor' ) as $module ) {
		if ( empty( $report[ $module ] ) || ! is_array( $report[ $module ] ) ) {
			continue;
		}
		foreach ( $report[ $module ] as $row ) {
			if ( empty( $row['active'] ) || empty( $row['name'] ) ) {
				continue;
			}
			$names[ $row['name'] ] = $row['name'];
		}
	}
	return array_values( $names );
}
