<?php
/**
 * Detect installed plugins that may clash with a module.
 *
 * A plugin is listed only when it actually performs the same job (known slug,
 * or a real hook/API call). Mentioning another product in a cleanup map, a
 * readme, or a detection list is not a clash.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugins that catalog or maintain other plugins and must never be flagged.
 *
 * @return string[]
 */
function tsolo_compat_ignored_slugs() {
	return array(
		'tso-options-tables-cleaner',
		'tso-image-master',
		'tso-link-inspector',
		'tso-stack-inspector',
		'tso-admin-notices',
		'tso-tabs-widget',
		'tso-tabla-liga',
	);
}

/**
 * Known incompatibility signatures, one list per module.
 *
 * Slugs are the plugin directory. Patterns are matched against PHP that is not
 * a map, changelog, or translation file, and only as real registrations or API URLs.
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
	);

	return array(
		'moderation'    => array(
			'reason'       => __( 'Also blocks login until a registration is approved (wp_authenticate_user). Keep only one approval plugin active.', 'tso-login-organizer' ),
			'slugs'        => $moderation_slugs,
			'exclude'      => $two_factor_slugs,
			'patterns'     => array( '/add_filter\s*\(\s*[\'"]wp_authenticate_user[\'"]/' ),
			'pattern_also' => array( '/pending_admin|awaiting approval|approve_user|user_approval|moderate_registration|unconfirmed/i' ),
		),
		'restrictions'  => array(
			'reason'   => __( 'Also forces login or restricts content. Guests may be redirected twice or locked out of pages.', 'tso-login-organizer' ),
			'slugs'    => array(
				'wp-force-login',
				'force-login',
				'members',
				'restrict-content',
				'restrict-content-pro',
				'paid-memberships-pro',
				'restrict-user-access',
			),
			'patterns' => array( '/add_action\s*\(\s*[\'"]template_redirect[\'"][^;]{0,180}force.?login/is' ),
		),
		'security'      => array(
			'reason' => __( 'Also locks out failed logins or hides wp-login.php. Running both can block legitimate users or hide the login URL twice.', 'tso-login-organizer' ),
			'slugs'  => array(
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
		),
		'captcha'       => array(
			'reason'   => __( 'Also adds a captcha or anti-spam check on forms. Two checks on the same form often fail verification.', 'tso-login-organizer' ),
			'slugs'    => array(
				'cleantalk-spam-protect',
				'google-captcha',
				'advanced-google-recaptcha',
				'recaptcha-woo',
				'invisible-recaptcha',
				'really-simple-captcha',
			),
			'patterns' => array(
				'/google\.com\/recaptcha\/api/',
				'/[\'"]g-recaptcha[\'"]/',
				'/grecaptcha\s*\./',
			),
		),
		'profiles'      => array(
			'reason' => __( 'Also blocks wp-admin or replaces the profile screen. Subscribers may be unable to reach either profile.', 'tso-login-organizer' ),
			'slugs'  => array(
				'remove-dashboard-access',
				'remove-dashboard-access-for-non-admins',
				'wp-frontend-profile',
			),
		),
		'notifications' => array(
			'reason' => __( 'Also replaces outgoing email content. The message saved here may be wrapped or discarded.', 'tso-login-organizer' ),
			'slugs'  => array(
				'wp-html-mail',
				'email-templates',
				'kadence-woocommerce-email-designer',
			),
		),
		'two_factor'    => array(
			'reason'       => __( 'Also asks for a second code at login. Two authenticators on the same account block sign-in.', 'tso-login-organizer' ),
			'slugs'        => $two_factor_slugs,
			'exclude'      => $moderation_slugs,
			'patterns'     => array( '/add_filter\s*\(\s*[\'"]wp_authenticate_user[\'"]/' ),
			'pattern_also' => array( '/\bTOTP\b|totp_secret|google_authenticator|two_factor_code|verify_totp/i' ),
		),
		'social'        => array(
			'reason' => __( 'Also adds social login buttons or OAuth callbacks. Buttons can be duplicated or the return URL can be stolen by the other plugin.', 'tso-login-organizer' ),
			'slugs'  => array(
				'nextend-facebook-connect',
				'super-socializer',
				'wordpress-social-login',
				'miniorange-login-openid',
				'nextend-social-login',
			),
		),
		'avatars'       => array(
			'reason' => __( 'Also replaces the profile photo (get_avatar). One of the two pictures will win and the other upload may look ignored.', 'tso-login-organizer' ),
			'slugs'  => array(
				'simple-local-avatars',
				'one-user-avatar',
				'wp-user-avatar',
				'basic-user-avatars',
			),
		),
		'favorites'     => array(
			'reason' => __( 'Also stores favorite posts. The buttons and the list page may disagree about what is saved.', 'tso-login-organizer' ),
			'slugs'  => array(
				'wp-favorite-posts',
				'simple-favorites',
			),
		),
		'mailchimp'     => array(
			'reason' => __( 'Also sends new users to Mailchimp. The same person can be subscribed twice.', 'tso-login-organizer' ),
			'slugs'  => array(
				'mailchimp-for-wp',
				'mc4wp-mailchimp-for-wordpress',
			),
		),
		'redirection'   => array(
			'reason' => __( 'Also sends users somewhere after login, logout or registration. The last plugin to run decides the final URL.', 'tso-login-organizer' ),
			'slugs'  => array(
				'peters-login-redirect',
				'wps-hide-login',
				'theme-my-login',
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
		$name      = isset( $data['Name'] ) ? (string) $data['Name'] : $slug;
		$catalog[] = array(
			'file'   => $file,
			'slug'   => $slug,
			'name'   => $name,
			'active' => in_array( $file, $active, true ),
		);
	}
	return $catalog;
}

/**
 * Whether a path is a catalog, translation, or doc rather than runtime code.
 *
 * @param string $path Absolute path.
 * @return bool
 */
function tsolo_compat_skip_source_path( $path ) {
	$path = wp_normalize_path( (string) $path );
	return (bool) preg_match( '~/(languages|assets|vendor|node_modules|tests|test)/|(maps?|detection|regression|changelog|readme)~i', $path );
}

/**
 * PHP files worth scanning for hook registrations.
 *
 * @param string $plugin_file Plugin basename.
 * @return string[]
 */
function tsolo_compat_plugin_php_files( $plugin_file ) {
	$main  = wp_normalize_path( WP_PLUGIN_DIR . '/' . $plugin_file );
	$dir   = wp_normalize_path( dirname( $main ) );
	$paths = array();
	if ( is_readable( $main ) && ! tsolo_compat_skip_source_path( $main ) ) {
		$paths[] = $main;
	}
	if ( ! is_dir( $dir ) || wp_normalize_path( WP_PLUGIN_DIR ) === $dir ) {
		return $paths;
	}
	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
		);
		$count    = 0;
		foreach ( $iterator as $fileinfo ) {
			if ( $count >= 40 ) {
				break;
			}
			if ( ! $fileinfo->isFile() ) {
				continue;
			}
			$path = wp_normalize_path( $fileinfo->getPathname() );
			if ( '.php' !== substr( $path, -4 ) || tsolo_compat_skip_source_path( $path ) ) {
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
	return array_values( array_unique( $paths ) );
}

/**
 * Whether one plugin's code registers the conflicting behavior.
 *
 * @param string $plugin_file Plugin basename.
 * @param array  $rule        Rule.
 * @return bool
 */
function tsolo_compat_plugin_code_matches( $plugin_file, $rule ) {
	$patterns = isset( $rule['patterns'] ) && is_array( $rule['patterns'] ) ? $rule['patterns'] : array();
	if ( ! $patterns ) {
		return false;
	}
	$also = isset( $rule['pattern_also'] ) && is_array( $rule['pattern_also'] ) ? $rule['pattern_also'] : array();
	foreach ( tsolo_compat_plugin_php_files( $plugin_file ) as $path ) {
		if ( ! is_readable( $path ) ) {
			continue;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read-only local scan of installed plugin PHP for hook registrations.
		$chunk = file_get_contents( $path );
		if ( ! is_string( $chunk ) || '' === $chunk ) {
			continue;
		}
		$hit = false;
		foreach ( $patterns as $pattern ) {
			if ( is_string( $pattern ) && '' !== $pattern && preg_match( $pattern, $chunk ) ) {
				$hit = true;
				break;
			}
		}
		if ( ! $hit ) {
			continue;
		}
		if ( ! $also ) {
			return true;
		}
		foreach ( $also as $pattern ) {
			if ( is_string( $pattern ) && '' !== $pattern && preg_match( $pattern, $chunk ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Whether one catalog row matches one rule.
 *
 * @param array $plugin Plugin row.
 * @param array $rule   Rule.
 * @return bool
 */
function tsolo_compat_plugin_matches_rule( $plugin, $rule ) {
	$slug = isset( $plugin['slug'] ) ? (string) $plugin['slug'] : '';
	if ( '' === $slug || in_array( $slug, tsolo_compat_ignored_slugs(), true ) ) {
		return false;
	}
	if ( 'tso-swiss-knife-advanced-maintenance-developer-toolkit' === $slug ) {
		return false;
	}
	$exclude = isset( $rule['exclude'] ) && is_array( $rule['exclude'] ) ? $rule['exclude'] : array();
	if ( in_array( $slug, $exclude, true ) ) {
		return false;
	}
	$slugs = isset( $rule['slugs'] ) && is_array( $rule['slugs'] ) ? $rule['slugs'] : array();
	if ( in_array( $slug, $slugs, true ) ) {
		return true;
	}
	$file = isset( $plugin['file'] ) ? (string) $plugin['file'] : '';
	return '' !== $file && tsolo_compat_plugin_code_matches( $file, $rule );
}

/**
 * Swiss Knife clashes only when Login Protect features are actually on.
 *
 * @param array $catalog Plugin catalog.
 * @return array<string,array<int,array<string,mixed>>>
 */
function tsolo_compat_swiss_knife_rows( $catalog ) {
	$rows  = array(
		'security'   => array(),
		'two_factor' => array(),
	);
	$file  = 'tso-swiss-knife-advanced-maintenance-developer-toolkit/tso-swiss-knife-advanced-maintenance-developer-toolkit.php';
	$found = null;
	foreach ( $catalog as $plugin ) {
		if ( isset( $plugin['slug'] ) && 'tso-swiss-knife-advanced-maintenance-developer-toolkit' === $plugin['slug'] ) {
			$found = $plugin;
			break;
		}
	}
	if ( ! $found ) {
		return $rows;
	}
	$settings = get_option( 'tsosk_login_protect', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$base  = array(
		'name'   => isset( $found['name'] ) ? (string) $found['name'] : 'TSO Swiss Knife',
		'active' => ! empty( $found['active'] ),
		'file'   => isset( $found['file'] ) ? (string) $found['file'] : $file,
	);
	$locks = ! empty( $settings['custom_url'] ) || ! empty( $settings['brute_force'] ) || ! empty( $settings['login_maintenance'] );
	if ( $locks ) {
		$row                = $base;
		$row['reason']      = __( 'Login Protect is hiding wp-login.php or locking failed logins. Leave hide-login and lockouts off in this plugin while that is on.', 'tso-login-organizer' );
		$rows['security'][] = $row;
	}
	if ( ! empty( $settings['email_2fa'] ) ) {
		$row                  = $base;
		$row['reason']        = __( 'Login Protect asks for an email code after the password. Do not also require an authenticator code on the same accounts.', 'tso-login-organizer' );
		$rows['two_factor'][] = $row;
	}
	return $rows;
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

	$report = array();
	foreach ( $rules as $module => $rule ) {
		$report[ $module ] = array();
		foreach ( $plugins as $plugin ) {
			if ( ! tsolo_compat_plugin_matches_rule( $plugin, $rule ) ) {
				continue;
			}
			$report[ $module ][] = array(
				'name'   => $plugin['name'],
				'active' => ! empty( $plugin['active'] ),
				'file'   => $plugin['file'],
				'reason' => '',
			);
		}
	}
	$swiss = tsolo_compat_swiss_knife_rows( $plugins );
	foreach ( $swiss as $module => $rows ) {
		if ( empty( $report[ $module ] ) || ! is_array( $report[ $module ] ) ) {
			$report[ $module ] = array();
		}
		foreach ( $rows as $row ) {
			$report[ $module ][] = $row;
		}
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
