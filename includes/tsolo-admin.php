<?php
/**
 * Admin settings UI.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'tsolo_admin_menu' );
add_action( 'admin_init', 'tsolo_admin_handle_post' );
add_action( 'admin_init', 'tsolo_admin_handle_lang_switch' );
add_filter( 'plugin_action_links_' . plugin_basename( TSOLO_FILE ), 'tsolo_plugin_action_links' );

/**
 * Settings link.
 *
 * @param array $links Links.
 * @return array
 */
function tsolo_plugin_action_links( $links ) {
	$url     = admin_url( 'options-general.php?page=tso-login-organizer' );
	$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'tso-login-organizer' ) . '</a>';
	return $links;
}

/**
 * Phrases that should stand out in admin help (CA / ES / EN).
 *
 * @return string[]
 */
function tsolo_admin_emphasis_phrases() {
	return array(
		'No crees páginas de WordPress',
		'No creeu pàgines de WordPress',
		'Do not create WordPress pages',
		'Guardar configuración del núcleo',
		'Desa la configuració del nucli',
		'Save core settings',
		'No hace falta página de WordPress',
		'No cal pàgina de WordPress',
		'No WordPress page needed',
		'direcciones amigables',
		'adreces amigables',
		'Friendly addresses',
		'páginas de WordPress',
		'pàgines de WordPress',
		'WordPress pages',
		'formulario virtual',
		'formulari virtual',
		'virtual form',
		'slug por defecto',
		'slug per defecte',
		'default "login" slug',
		'se crean virtualmente',
		'es creen virtualment',
		'created virtually',
		'Perfil y Favoritos',
		'Perfil i Preferits',
		'Profile and Favorites',
		'Theme My Login',
		'Anyone can register',
		'Cualquiera puede registrarse',
		'Qualsevol es pot registrar',
		'email confirmation',
		'admin approval',
		'Log users in after registration',
		'generated password',
		'Save modules',
		'Guardar módulos',
		'Desa els mòduls',
		'wp-login.php',
		'CleanTalk',
		'reCAPTCHA',
		'https://',
		'Permalinks',
		'Enlaces permanentes',
		'Enllaços permanents',
		'«login»',
		'«Cualquiera puede registrarse»',
		'«Qualsevol pot registrar-se»',
		'Plain',
		'AJAX',
	);
}

/**
 * Escape help text and wrap the important phrases in strong.
 *
 * @param string $text Plain text.
 * @return string
 */
function tsolo_admin_emphasize( $text ) {
	$text    = (string) $text;
	$phrases = tsolo_admin_emphasis_phrases();
	usort(
		$phrases,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);
	$tokens = array();
	foreach ( $phrases as $i => $phrase ) {
		if ( '' === $phrase || false === strpos( $text, $phrase ) ) {
			continue;
		}
		$token = "\x1E" . $i . "\x1F";
		$text  = str_replace( $phrase, $token, $text );
		$tokens[ $token ] = $phrase;
	}
	$safe = esc_html( $text );
	foreach ( $tokens as $token => $phrase ) {
		$safe = str_replace( esc_html( $token ), '<strong>' . esc_html( $phrase ) . '</strong>', $safe );
	}
	return wp_kses( $safe, array( 'strong' => array() ) );
}

/**
 * Human labels for action slugs.
 *
 * @return array<string,string>
 */
function tsolo_get_slug_labels() {
	return array(
		'login'         => __( 'Login', 'tso-login-organizer' ),
		'logout'        => __( 'Logout', 'tso-login-organizer' ),
		'register'      => __( 'Register', 'tso-login-organizer' ),
		'lostpassword'  => __( 'Lost password', 'tso-login-organizer' ),
		'resetpass'     => __( 'Reset password', 'tso-login-organizer' ),
		'dashboard'     => __( 'Dashboard', 'tso-login-organizer' ),
		'confirmaction' => __( 'Confirm privacy request', 'tso-login-organizer' ),
		'profile'       => __( 'Profile', 'tso-login-organizer' ),
		'favorites'     => __( 'Favorites', 'tso-login-organizer' ),
	);
}

/**
 * Short help text under each slug row.
 *
 * @return array<string,string>
 */
function tsolo_get_slug_helps() {
	return array(
		'login'         => __( 'Virtual sign-in URL (example: yoursite.com/login/). No WordPress page needed.', 'tso-login-organizer' ),
		'logout'        => __( 'Virtual logout URL (menus and links). No WordPress page needed.', 'tso-login-organizer' ),
		'register'      => __( 'Virtual registration URL (only if "Anyone can register" is on).', 'tso-login-organizer' ),
		'lostpassword'  => __( 'Virtual forgot-password URL.', 'tso-login-organizer' ),
		'resetpass'     => __( 'Virtual page opened from the reset link in the email.', 'tso-login-organizer' ),
		'dashboard'     => __( 'Virtual account dashboard for logged-in users.', 'tso-login-organizer' ),
		'confirmaction' => __( 'Virtual confirm URL for personal-data export/erase requests (WordPress privacy tools).', 'tso-login-organizer' ),
		'profile'       => __( 'Virtual frontend profile URL (requires Profiles module).', 'tso-login-organizer' ),
		'favorites'     => __( 'Virtual favorites URL (requires Favorites module).', 'tso-login-organizer' ),
	);
}

/**
 * Admin notice: pages are virtual; list leftover WP pages matching slugs.
 */
function tsolo_admin_render_virtual_pages_notice() {
	$legacy = tsolo_get_wp_pages_matching_slugs();
	?>
	<div class="notice notice-info inline tsolo-virtual-pages-notice">
		<p><strong><?php esc_html_e( 'Virtual pages', 'tso-login-organizer' ); ?></strong>
			<?php echo wp_kses_post( tsolo_admin_emphasize( __( 'TSO Login Organizer creates login, logout, register, lost password, reset password, dashboard, confirm-action, and (when modules are on) profile and favorites as virtual URLs via rewrite rules. You do not create WordPress pages for them, and you do not need to keep Theme My Login pages.', 'tso-login-organizer' ) ) ); ?>
		</p>
		<?php if ( ! empty( $legacy ) ) : ?>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of pages */
						_n(
							'%d WordPress page looks like a Theme My Login / login form page (matching slug, TML option ID, or [theme-my-login] shortcode). You can move it to Trash — the plugin keeps serving the virtual URL.',
							'%d WordPress pages look like Theme My Login / login form pages (matching slug, TML option ID, or [theme-my-login] shortcode). You can move them to Trash — the plugin keeps serving the virtual URLs.',
							count( $legacy ),
							'tso-login-organizer'
						),
						count( $legacy )
					)
				);
				?>
			</p>
			<?php
			$known_slugs = function_exists( 'tsolo_known_tml_page_slugs' ) ? tsolo_known_tml_page_slugs() : array();
			$stored_ids  = function_exists( 'tsolo_get_tml_stored_page_ids' ) ? tsolo_get_tml_stored_page_ids() : array();
			?>
			<ul class="ul-disc">
				<?php foreach ( $legacy as $page ) : ?>
					<?php
					$edit_link = get_edit_post_link( $page->ID, 'raw' );
					if ( ! $edit_link ) {
						$edit_link = admin_url( 'post.php?post=' . (int) $page->ID . '&action=edit' );
					}
					$page_title = get_the_title( $page );
					if ( '' === $page_title ) {
						$page_title = $page->post_name;
					}
					$why = array();
					if ( in_array( $page->post_name, $known_slugs, true ) ) {
						$why[] = __( 'slug', 'tso-login-organizer' );
					}
					if ( in_array( (int) $page->ID, $stored_ids, true ) ) {
						$why[] = __( 'TML option', 'tso-login-organizer' );
					}
					if ( is_string( $page->post_content ) && ( false !== strpos( $page->post_content, '[theme-my-login' ) || false !== strpos( $page->post_content, '[tso-login-organizer' ) ) ) {
						$why[] = __( 'shortcode', 'tso-login-organizer' );
					}
					?>
					<li>
						<a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $page_title ); ?></a>
						<code><?php echo esc_html( '/' . $page->post_name . '/' ); ?></code>
						<span class="description">(<?php echo esc_html( $page->post_status ); ?><?php echo $why ? ' · ' . esc_html( implode( ', ', $why ) ) : ''; ?>)</span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'No leftover login pages found (by slug, TML options, or shortcode). Everything is served virtually.', 'tso-login-organizer' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Friendly-permalink status and the button that refreshes rewrite rules.
 */
function tsolo_admin_render_permalink_status() {
	$health = tsolo_permalink_health();
	$class  = ! empty( $health['ok'] ) ? 'notice-success' : 'notice-warning';
	$link   = admin_url( 'options-permalink.php' );
	echo '<div class="notice inline ' . esc_attr( $class ) . ' tsolo-permalink-status"><p>';
	if ( ! empty( $health['ok'] ) ) {
		echo wp_kses_post(
			tsolo_admin_emphasize(
				sprintf(
					/* translators: %s: permalink structure, e.g. /%postname%/ */
					__( 'Friendly URLs are on (current structure: %s). The login rewrite rule is present, so the preview addresses below can open.', 'tso-login-organizer' ),
					$health['structure']
				)
			)
		);
	} elseif ( empty( $health['using'] ) ) {
		echo wp_kses_post( tsolo_admin_emphasize( __( 'Friendly URLs are off. WordPress is using Plain permalinks, so addresses such as /login/ will not open.', 'tso-login-organizer' ) ) );
		echo ' ';
		echo wp_kses_post( tsolo_admin_emphasize( __( 'Correct setup: Settings → Permalinks → Post name. The structure box should contain /%postname%/. Then click Save Changes on that screen.', 'tso-login-organizer' ) ) );
		echo ' <a href="' . esc_url( $link ) . '">' . esc_html__( 'Open Permalinks settings', 'tso-login-organizer' ) . '</a>';
	} else {
		echo wp_kses_post(
			tsolo_admin_emphasize(
				sprintf(
					/* translators: %s: permalink structure */
					__( 'Permalinks are not Plain (current structure: %s) but the login rewrite rule is missing, so the virtual address may 404.', 'tso-login-organizer' ),
					'' !== $health['structure'] ? $health['structure'] : '/%postname%/'
				)
			)
		);
		echo ' ';
		echo wp_kses_post( tsolo_admin_emphasize( __( 'Click “Check friendly URLs” to refresh rewrite rules. If this warning stays, open Settings → Permalinks, choose Post name (structure /%postname%/), and click Save Changes.', 'tso-login-organizer' ) ) );
		echo ' <a href="' . esc_url( $link ) . '">' . esc_html__( 'Open Permalinks settings', 'tso-login-organizer' ) . '</a>';
	}
	echo '</p><p><button type="submit" class="button" form="tsolo-permalink-check">' . esc_html__( 'Check friendly URLs', 'tso-login-organizer' ) . '</button></p></div>';
}

/**
 * Register menu.
 */
function tsolo_admin_menu() {
	add_options_page(
		__( 'TSO Login Organizer', 'tso-login-organizer' ),
		__( 'TSO Login Organizer', 'tso-login-organizer' ),
		'manage_options',
		'tso-login-organizer',
		'tsolo_admin_render_page'
	);
}

/**
 * Language switch via admin URL (nonce-protected).
 */
function tsolo_admin_handle_lang_switch() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$page = tsolo_get_admin_query_arg( 'page' );
	if ( 'tso-login-organizer' !== $page ) {
		return;
	}
	$lang_arg = tsolo_get_admin_query_arg( TSOLO_ADMIN_QUERY_SET_LANG );
	if ( '' === $lang_arg ) {
		return;
	}
	$nonce = tsolo_get_admin_query_arg( '_wpnonce' );
	if ( ! wp_verify_nonce( $nonce, TSOLO_ADMIN_QUERY_SET_LANG ) ) {
		return;
	}
	$lang = sanitize_key( $lang_arg );
	if ( in_array( $lang, array( 'ca', 'es', 'en' ), true ) ) {
		tsolo_set_ui_lang( $lang );
	}
	$tab          = sanitize_key( tsolo_get_admin_query_arg( 'tab', 'core' ) );
	$redirect_url = admin_url( 'options-general.php?page=tso-login-organizer' );
	if ( $tab ) {
		$redirect_url = add_query_arg( 'tab', $tab, $redirect_url );
	}
	wp_safe_redirect( $redirect_url );
	exit;
}

/**
 * Handle settings POST.
 */
function tsolo_admin_handle_post() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['tsolo_admin_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Presence check before nonce verify.
		return;
	}
	if ( ! tsolo_verify_admin_form_nonce() ) {
		return;
	}
	$action = tsolo_get_admin_post_text( 'tsolo_admin_action' );

	if ( 'import_tml' === $action ) {
		tsolo_import_tml_settings( true );
		flush_rewrite_rules();
		tsolo_admin_set_flash( __( 'Theme My Login settings imported.', 'tso-login-organizer' ), 'success' );
		return;
	}

	if ( 'check_permalinks' === $action ) {
		flush_rewrite_rules( false );
		$health = tsolo_permalink_health();
		if ( ! empty( $health['ok'] ) ) {
			tsolo_admin_set_flash( __( 'Friendly URL check finished: permalinks are not Plain and the login rewrite rule is present.', 'tso-login-organizer' ), 'success' );
		} elseif ( empty( $health['using'] ) ) {
			tsolo_admin_set_flash( __( 'Friendly URL check finished: WordPress is using Plain permalinks. Set Permalinks to Post name (/%postname%/) — see the note in URL slugs.', 'tso-login-organizer' ), 'warning' );
		} else {
			tsolo_admin_set_flash( __( 'Friendly URL check finished: rewrite rules were refreshed but the login rule is still missing. Open Settings → Permalinks and click Save Changes.', 'tso-login-organizer' ), 'warning' );
		}
		wp_safe_redirect( admin_url( 'options-general.php?page=tso-login-organizer&tab=core' ) );
		exit;
	}

	if ( 'save_core' === $action ) {
		$settings = array(
			'ajax'              => tsolo_admin_post_is_set( 'ajax' ) ? 1 : 0,
			'login_type'        => tsolo_get_admin_post_text( 'login_type', 'default' ),
			'registration_type' => tsolo_get_admin_post_text( 'registration_type', 'default' ),
			'user_passwords'    => tsolo_admin_post_is_set( 'user_passwords' ) ? 1 : 0,
			'auto_login'        => tsolo_admin_post_is_set( 'auto_login' ) ? 1 : 0,
			'slugs'             => tsolo_get_admin_post_array( 'slugs' ),
		);
		tsolo_update_settings( $settings );
		if ( tsolo_admin_post_is_set( 'users_can_register' ) ) {
			update_option( 'users_can_register', 1 );
		} elseif ( ! is_multisite() ) {
			update_option( 'users_can_register', 0 );
		}
		flush_rewrite_rules();
		tsolo_admin_set_flash( __( 'Core settings saved.', 'tso-login-organizer' ), 'success' );
		return;
	}

	if ( 'save_modules' === $action ) {
		$prev    = tsolo_get_modules();
		$mods    = tsolo_get_admin_post_array( 'modules' );
		$defs    = tsolo_get_module_definitions();
		$collide = false;
		foreach ( $defs as $key => $def ) {
			if ( empty( $def['collide'] ) ) {
				continue;
			}
			if ( ! empty( $mods[ $key ] ) && empty( $prev[ $key ] ) ) {
				$collide = true;
				break;
			}
		}
		tsolo_update_modules( $mods );
		flush_rewrite_rules();
		if ( $collide ) {
			tsolo_admin_set_flash(
				__( 'Modules updated. You enabled a module that may conflict with Swiss Knife, Kadence Security or CleanTalk — keep overlapping features off elsewhere.', 'tso-login-organizer' ),
				'warning'
			);
		} else {
			tsolo_admin_set_flash( __( 'Modules updated.', 'tso-login-organizer' ), 'success' );
		}
		return;
	}

	if ( 'save_module_settings' === $action ) {
		$module = tsolo_get_admin_post_text( 'module' );
		tsolo_admin_save_module_settings( $module );
		tsolo_admin_set_flash( __( 'Module settings saved.', 'tso-login-organizer' ), 'success' );
	}
}

/**
 * Store a one-time admin notice (avoids duplicate settings_errors output).
 *
 * @param string $message Notice text.
 * @param string $type    success|warning|error.
 */
function tsolo_admin_set_flash( $message, $type = 'success' ) {
	set_transient(
		'tsolo_admin_flash_' . get_current_user_id(),
		array(
			'message' => $message,
			'type'    => $type,
		),
		60
	);
}

/**
 * Print and clear flash notice.
 */
function tsolo_admin_print_flash() {
	$key    = 'tsolo_admin_flash_' . get_current_user_id();
	$notice = get_transient( $key );
	if ( ! is_array( $notice ) || empty( $notice['message'] ) ) {
		return;
	}
	delete_transient( $key );
	$type  = isset( $notice['type'] ) ? $notice['type'] : 'success';
	$class = 'notice-success';
	if ( 'warning' === $type ) {
		$class = 'notice-warning';
	} elseif ( 'error' === $type ) {
		$class = 'notice-error';
	}
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $notice['message'] ) . '</p></div>';
}

/**
 * Save per-module settings blobs.
 *
 * @param string $module Module key.
 */
function tsolo_admin_save_module_settings( $module ) {
	$map = array(
		'redirection'   => TSOLO_OPT_REDIRECTION,
		'moderation'    => TSOLO_OPT_MODERATION,
		'restrictions'  => TSOLO_OPT_RESTRICTIONS,
		'security'      => TSOLO_OPT_SECURITY,
		'captcha'       => TSOLO_OPT_CAPTCHA,
		'profiles'      => TSOLO_OPT_PROFILES,
		'notifications' => TSOLO_OPT_NOTIFICATIONS,
		'two_factor'    => TSOLO_OPT_TWO_FACTOR,
		'social'        => TSOLO_OPT_SOCIAL,
		'avatars'       => TSOLO_OPT_AVATARS,
		'favorites'     => TSOLO_OPT_FAVORITES,
		'mailchimp'     => TSOLO_OPT_MAILCHIMP,
	);
	if ( ! isset( $map[ $module ] ) ) {
		return;
	}
	$data      = tsolo_get_admin_post_array( 'mod' );
	$bool_keys = array(
		'security'      => array( 'brute_force', 'hide_wp_login' ),
		'moderation'    => array( 'email_confirm', 'admin_approve' ),
		'restrictions'  => array( 'private_site' ),
		'profiles'      => array( 'block_admin' ),
		'notifications' => array( 'html' ),
		'favorites'     => array( 'show_on_posts' ),
		'mailchimp'     => array( 'checkbox', 'default_optin' ),
	);
	if ( isset( $bool_keys[ $module ] ) ) {
		foreach ( $bool_keys[ $module ] as $bk ) {
			$data[ $bk ] = ! empty( $data[ $bk ] ) ? 1 : 0;
		}
	}
	if ( 'notifications' === $module ) {
		$data['new_user_body'] = tsolo_get_admin_post_textarea( 'mod_new_user_body' );
		$data['reset_body']    = tsolo_get_admin_post_textarea( 'mod_reset_body' );
	}
	if ( 'redirection' === $module ) {
		$data = array(
			'login'    => tsolo_get_admin_post_array( 'redir_login' ),
			'logout'   => tsolo_get_admin_post_array( 'redir_logout' ),
			'register' => tsolo_get_admin_post_array( 'redir_register' ),
		);
	}
	if ( 'two_factor' === $module ) {
		$roles                  = isset( $data['required_roles'] ) && is_array( $data['required_roles'] ) ? $data['required_roles'] : array();
		$data['required_roles'] = array_values( array_map( 'sanitize_key', $roles ) );
	}
	tsolo_update_option_array( $map[ $module ], $data );
}

/**
 * Render admin page.
 */
function tsolo_admin_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$lang = tsolo_get_ui_lang();
	tsolo_apply_admin_ui_locale( $lang );

	$tab         = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'core'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab navigation.
	$settings    = tsolo_get_settings();
	$modules     = tsolo_get_modules();
	$defs        = tsolo_get_module_definitions();
	$collisions  = tsolo_detect_collision_plugins();
	$incompat    = tsolo_get_incompatibility_report();
	$slug_labels = tsolo_get_slug_labels();

	$lang_base    = add_query_arg(
		array(
			'page' => 'tso-login-organizer',
			'tab'  => $tab,
		),
		admin_url( 'options-general.php' )
	);
	$lang_url_ca  = wp_nonce_url( add_query_arg( TSOLO_ADMIN_QUERY_SET_LANG, 'ca', $lang_base ), TSOLO_ADMIN_QUERY_SET_LANG );
	$lang_url_es  = wp_nonce_url( add_query_arg( TSOLO_ADMIN_QUERY_SET_LANG, 'es', $lang_base ), TSOLO_ADMIN_QUERY_SET_LANG );
	$lang_url_en  = wp_nonce_url( add_query_arg( TSOLO_ADMIN_QUERY_SET_LANG, 'en', $lang_base ), TSOLO_ADMIN_QUERY_SET_LANG );
	$donate_label = tsolo_ui_triple_text(
		$lang,
		'☕ Dona suport al plugin',
		'☕ Apoya este plugin',
		'☕ Support this plugin'
	);
	?>
	<div class="wrap tsolo-admin-wrap" id="tsolo-wrap" data-theme="day" data-theme-pref="auto">
		<form id="tsolo-permalink-check" method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=core' ) ); ?>">
			<?php wp_nonce_field( TSOLO_NONCE_FORM ); ?>
			<input type="hidden" name="tsolo_admin_action" value="check_permalinks" />
		</form>
		<div class="tsolo-nav-top">
			<h1 class="tsolo-page-title">
				<?php esc_html_e( 'TSO Login Organizer', 'tso-login-organizer' ); ?>
				<span class="tsolo-plugin-version">v<?php echo esc_html( TSOLO_VERSION ); ?></span>
			</h1>
			<div class="tsolo-nav-meta">
				<?php tsolo_render_theme_toggle( $lang ); ?>
				<a class="tsolo-donate-btn" href="<?php echo esc_url( tsolo_get_kofi_donate_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $donate_label ); ?></a>
				<div class="tsolo-lang-switch" aria-label="<?php echo esc_attr( tsolo_ui_triple_text( $lang, 'Idioma de la interfície', 'Idioma de la interfaz', 'Interface language' ) ); ?>">
					<span class="tsolo-lang-globe" aria-hidden="true">🌐</span>
					<a href="<?php echo esc_url( $lang_url_ca ); ?>" class="tsolo-lang-btn<?php echo 'ca' === $lang ? ' is-active' : ''; ?>">CA</a>
					<a href="<?php echo esc_url( $lang_url_es ); ?>" class="tsolo-lang-btn<?php echo 'es' === $lang ? ' is-active' : ''; ?>">ES</a>
					<a href="<?php echo esc_url( $lang_url_en ); ?>" class="tsolo-lang-btn<?php echo 'en' === $lang ? ' is-active' : ''; ?>">EN</a>
				</div>
			</div>
		</div>
		<?php tsolo_admin_print_flash(); ?>
		<p class="description"><?php esc_html_e( 'Frontend login, registration and account pages. Optional modules add redirection, moderation, security and more.', 'tso-login-organizer' ); ?></p>
		<?php if ( $collisions ) : ?>
			<div class="notice notice-warning"><p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: plugin names */
						__( 'Possible collisions detected: %s. Keep Security, Captcha and 2FA off unless you disable overlapping features elsewhere.', 'tso-login-organizer' ),
						implode( ', ', $collisions )
					)
				);
				?>
			</p></div>
		<?php endif; ?>
		<nav class="nav-tab-wrapper tsolo-nav-tabs" aria-label="<?php esc_attr_e( 'Plugin sections', 'tso-login-organizer' ); ?>">
			<a class="nav-tab <?php echo 'core' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=core' ) ); ?>" data-tsolo-tab="core"><?php esc_html_e( 'Core', 'tso-login-organizer' ); ?></a>
			<a class="nav-tab <?php echo 'modules' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=modules' ) ); ?>" data-tsolo-tab="modules"><?php esc_html_e( 'Modules', 'tso-login-organizer' ); ?></a>
			<a class="nav-tab <?php echo 'migrate' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=migrate' ) ); ?>" data-tsolo-tab="migrate"><?php esc_html_e( 'Migration', 'tso-login-organizer' ); ?></a>
			<?php foreach ( $defs as $key => $def ) : ?>
				<?php
				if ( empty( $modules[ $key ] ) ) {
					continue;
				}
				?>
				<a class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=' . rawurlencode( $key ) ) ); ?>" data-tsolo-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="tsolo-tab-panels">
			<section class="tsolo-tab-panel<?php echo 'core' === $tab ? ' is-active' : ''; ?>" data-tsolo-panel="core"<?php echo 'core' === $tab ? '' : ' hidden'; ?><?php echo 'core' === $tab ? '' : ' style="display:none"'; ?>>
			<form method="post">
				<?php wp_nonce_field( TSOLO_NONCE_FORM ); ?>
				<input type="hidden" name="tsolo_admin_action" value="save_core" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Forms', 'tso-login-organizer' ); ?></th>
						<td>
							<label><input type="checkbox" name="ajax" value="1" <?php checked( ! empty( $settings['ajax'] ) ); ?> /> <?php esc_html_e( 'Send login, registration and password forms without reloading the whole page', 'tso-login-organizer' ); ?></label>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'With this on, the visitor stays on the same screen and only the form updates (this is called AJAX). With this off, the browser loads the page again after each submit. Turn it off if another plugin already updates these forms in place.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<?php if ( ! is_multisite() ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Membership', 'tso-login-organizer' ); ?></th>
						<td>
							<label><input type="checkbox" name="users_can_register" value="1" <?php checked( get_option( 'users_can_register' ) ); ?> /> <?php esc_html_e( 'Anyone can register', 'tso-login-organizer' ); ?></label>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'WordPress setting: show the registration form and allow new accounts. Same as Settings > General.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Login type', 'tso-login-organizer' ); ?></th>
						<td>
							<?php
							foreach ( array(
								'default'  => __( 'Username or email', 'tso-login-organizer' ),
								'username' => __( 'Username only', 'tso-login-organizer' ),
								'email'    => __( 'Email only', 'tso-login-organizer' ),
							) as $val => $label ) :
								?>
								<label style="display:block;"><input type="radio" name="login_type" value="<?php echo esc_attr( $val ); ?>" <?php checked( $settings['login_type'], $val ); ?> /> <?php echo esc_html( $label ); ?></label>
							<?php endforeach; ?>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'What visitors may type in the login field: either username, email, or both.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Registration type', 'tso-login-organizer' ); ?></th>
						<td>
							<label style="display:block;"><input type="radio" name="registration_type" value="default" <?php checked( $settings['registration_type'], 'default' ); ?> /> <?php esc_html_e( 'Username + email', 'tso-login-organizer' ); ?></label>
							<label style="display:block;"><input type="radio" name="registration_type" value="email" <?php checked( $settings['registration_type'], 'email' ); ?> /> <?php esc_html_e( 'Email only', 'tso-login-organizer' ); ?></label>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Email only creates a username from the email address. Username + email asks for both fields.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Passwords', 'tso-login-organizer' ); ?></th>
						<td>
							<label><input type="checkbox" name="user_passwords" value="1" <?php checked( ! empty( $settings['user_passwords'] ) ); ?> /> <?php esc_html_e( 'Allow users to set a password at registration', 'tso-login-organizer' ); ?></label>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'If off, WordPress emails a generated password. If on, the register form includes password fields.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Auto login', 'tso-login-organizer' ); ?></th>
						<td>
							<label><input type="checkbox" name="auto_login" value="1" <?php checked( ! empty( $settings['auto_login'] ) ); ?> /> <?php esc_html_e( 'Log users in after registration', 'tso-login-organizer' ); ?></label>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'After a successful registration the new person is signed in immediately. If Moderation has email confirmation or admin approval turned on, this box is ignored and they stay logged out until that step is finished.', 'tso-login-organizer' ) ) ); ?></p>
							<?php if ( class_exists( 'TSOLO_Mod_Moderation', false ) && TSOLO_Mod_Moderation::blocks_auto_login() ) : ?>
								<p class="description tsolo-option-help"><?php esc_html_e( 'Moderation is currently holding new accounts, so this option is not applied.', 'tso-login-organizer' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>
				<div class="tsolo-slugs-panel">
					<h2 class="tsolo-slugs-heading"><?php esc_html_e( 'URL slugs', 'tso-login-organizer' ); ?></h2>
					<?php tsolo_admin_render_virtual_pages_notice(); ?>
					<?php tsolo_admin_render_permalink_status(); ?>
					<p class="description"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'These values are the path after your site URL for each virtual form. With the default "login" slug, the form is at:', 'tso-login-organizer' ) ) ); ?> <code><?php echo esc_html( home_url( '/login/' ) ); ?></code></p>
					<ol class="description tsolo-slugs-howto">
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Use the check above. Friendly addresses work only when Permalinks is not Plain.', 'tso-login-organizer' ) ) ); ?></li>
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Edit a slug below (letters, numbers, hyphens only), then click Save core settings (rewrites are flushed automatically).', 'tso-login-organizer' ) ) ); ?></li>
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Open the new URL in the browser (e.g. /accedir/ if you changed login to accedir). Update menus/links that pointed to the old path.', 'tso-login-organizer' ) ) ); ?></li>
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Do not create WordPress pages for these slugs — they are created virtually. You can delete Theme My Login pages with the same names. Profile and Favorites rewrite only when those modules are enabled.', 'tso-login-organizer' ) ) ); ?></li>
					</ol>
					<?php
					$slug_helps  = tsolo_get_slug_helps();
					$col_action  = __( 'Action', 'tso-login-organizer' );
					$col_slug    = __( 'Slug', 'tso-login-organizer' );
					$col_deliver = __( 'Delivery', 'tso-login-organizer' );
					$col_preview = __( 'URL preview', 'tso-login-organizer' );
					?>
					<table class="widefat striped tsolo-slugs-table">
						<thead>
							<tr>
								<th scope="col"><?php echo esc_html( $col_action ); ?></th>
								<th scope="col"><?php echo esc_html( $col_slug ); ?></th>
								<th scope="col"><?php echo esc_html( $col_deliver ); ?></th>
								<th scope="col"><?php echo esc_html( $col_preview ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $settings['slugs'] as $key => $slug ) : ?>
							<?php
							$label       = isset( $slug_labels[ $key ] ) ? $slug_labels[ $key ] : $key;
							$help        = isset( $slug_helps[ $key ] ) ? $slug_helps[ $key ] : '';
							$preview     = '/' . trim( (string) $slug, '/' ) . '/';
							$preview_url = home_url( $preview );
							$action_live = tsolo_action_exists( $key );
							?>
							<tr>
								<td class="tsolo-slug-label" data-label="<?php echo esc_attr( $col_action ); ?>">
									<strong><?php echo esc_html( $label ); ?></strong>
									<?php if ( $help ) : ?>
										<span class="description"><?php echo wp_kses_post( tsolo_admin_emphasize( $help ) ); ?></span>
									<?php endif; ?>
								</td>
								<td class="tsolo-slug-input" data-label="<?php echo esc_attr( $col_slug ); ?>">
									<input type="text" name="slugs[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $slug ); ?>" class="regular-text code" autocomplete="off" data-tsolo-slug-key="<?php echo esc_attr( $key ); ?>" />
								</td>
								<td class="tsolo-slug-delivery" data-label="<?php echo esc_attr( $col_deliver ); ?>">
									<?php if ( $action_live ) : ?>
										<span class="description"><?php esc_html_e( 'Virtual (active)', 'tso-login-organizer' ); ?></span>
									<?php else : ?>
										<span class="description"><?php esc_html_e( 'Virtual (enable module)', 'tso-login-organizer' ); ?></span>
									<?php endif; ?>
								</td>
								<td class="tsolo-slug-preview" data-label="<?php echo esc_attr( $col_preview ); ?>">
									<?php if ( $action_live ) : ?>
										<a class="tsolo-ext-link" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Open in new tab', 'tso-login-organizer' ); ?>"><code><?php echo esc_html( $preview_url ); ?></code></a>
									<?php else : ?>
										<code><?php echo esc_html( $preview_url ); ?></code>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php submit_button( __( 'Save core settings', 'tso-login-organizer' ) ); ?>
			</form>
			</section>

			<section class="tsolo-tab-panel<?php echo 'modules' === $tab ? ' is-active' : ''; ?>" data-tsolo-panel="modules"<?php echo 'modules' === $tab ? '' : ' hidden'; ?><?php echo 'modules' === $tab ? '' : ' style="display:none"'; ?>>
			<form method="post">
				<?php wp_nonce_field( TSOLO_NONCE_FORM ); ?>
				<input type="hidden" name="tsolo_admin_action" value="save_modules" />
				<div class="tsolo-option-help" style="max-width:42rem;">
					<p class="description"><strong><?php esc_html_e( 'How to use modules', 'tso-login-organizer' ); ?></strong></p>
					<ol class="description" style="margin-left:1.25rem;">
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Tick Enable on the features you want, then click Save modules.', 'tso-login-organizer' ) ) ); ?></li>
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'A new tab with the module name appears at the top of this page. Open it to configure options (keys, URLs, checkboxes, etc.).', 'tso-login-organizer' ) ) ); ?></li>
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Until a module is enabled, it does nothing and has no settings tab. Disable it again anytime to turn that feature off.', 'tso-login-organizer' ) ) ); ?></li>
					</ol>
				</div>
				<table class="form-table tsolo-module-list" role="presentation">
					<?php foreach ( $defs as $key => $def ) : ?>
						<tr class="tsolo-module-row">
							<th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
							<td>
								<label><input type="checkbox" name="modules[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $modules[ $key ] ) ); ?> class="<?php echo ! empty( $def['collide'] ) ? 'tsolo-collide-module' : ''; ?>" /> <?php esc_html_e( 'Enable', 'tso-login-organizer' ); ?></label>
								<?php if ( ! empty( $modules[ $key ] ) ) : ?>
									<p class="description tsolo-option-help"><a href="<?php echo esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=' . rawurlencode( $key ) ) ); ?>" data-tsolo-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( sprintf( /* translators: %s: module name */ __( 'Open "%s" settings tab', 'tso-login-organizer' ), $def['label'] ) ); ?></a></p>
								<?php endif; ?>
								<?php if ( ! empty( $def['description'] ) ) : ?>
									<p class="description tsolo-option-help"><?php echo esc_html( $def['description'] ); ?></p>
								<?php endif; ?>
								<?php
								$module_hits = isset( $incompat[ $key ] ) && is_array( $incompat[ $key ] ) ? $incompat[ $key ] : array();
								$active_hits = array();
								foreach ( $module_hits as $hit ) {
									if ( ! empty( $hit['active'] ) && ! empty( $hit['name'] ) ) {
										$active_hits[] = $hit['name'];
									}
								}
								if ( $active_hits ) :
									?>
									<p class="description tsolo-option-help"><strong><?php esc_html_e( 'Installed plugins that may clash:', 'tso-login-organizer' ); ?></strong> <?php echo esc_html( implode( ', ', $active_hits ) ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button( __( 'Save modules', 'tso-login-organizer' ) ); ?>
			</form>
			</section>

			<section class="tsolo-tab-panel<?php echo 'migrate' === $tab ? ' is-active' : ''; ?>" data-tsolo-panel="migrate"<?php echo 'migrate' === $tab ? '' : ' hidden'; ?><?php echo 'migrate' === $tab ? '' : ' style="display:none"'; ?>>
			<form method="post">
				<?php wp_nonce_field( TSOLO_NONCE_FORM ); ?>
				<input type="hidden" name="tsolo_admin_action" value="import_tml" />
				<p><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Import Theme My Login free settings (AJAX, login type, registration, passwords, auto-login, slugs). Shortcode [theme-my-login] is already aliased. Deactivate Theme My Login after verifying URLs.', 'tso-login-organizer' ) ) ); ?></p>
				<p><?php echo tsolo_tml_options_present() ? esc_html__( 'TML options detected on this site.', 'tso-login-organizer' ) : esc_html__( 'No TML options detected (you can still use this plugin with its own defaults).', 'tso-login-organizer' ); ?></p>
				<?php tsolo_admin_render_virtual_pages_notice(); ?>
				<p class="description"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'After import: trash the old Theme My Login WordPress pages, keep pretty permalinks, then open each virtual URL from Core → URL slugs to confirm forms load.', 'tso-login-organizer' ) ) ); ?></p>
				<?php submit_button( __( 'Import Theme My Login settings', 'tso-login-organizer' ) ); ?>
			</form>
			</section>

			<?php foreach ( $defs as $key => $def ) : ?>
				<?php
				if ( empty( $modules[ $key ] ) ) {
					continue;
				}
				?>
				<section class="tsolo-tab-panel<?php echo $tab === $key ? ' is-active' : ''; ?>" data-tsolo-panel="<?php echo esc_attr( $key ); ?>"<?php echo $tab === $key ? '' : ' hidden'; ?><?php echo $tab === $key ? '' : ' style="display:none"'; ?>>
					<?php tsolo_admin_render_module_tab( $key, $def ); ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Escape text and turn http(s) URLs into new-tab links (for testing).
 *
 * @param string $text Plain text that may contain URLs.
 * @return string Safe HTML.
 */
function tsolo_admin_linkify_text( $text ) {
	$parts = preg_split( '#(https?://[^\s<>"\']+)#', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( ! is_array( $parts ) ) {
		return esc_html( (string) $text );
	}
	$out = '';
	foreach ( $parts as $i => $part ) {
		if ( 0 === ( $i % 2 ) ) {
			$out .= tsolo_admin_emphasize( $part );
			continue;
		}
		$url  = rtrim( $part, '.,);]' );
		$out .= '<a class="tsolo-ext-link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a>';
		if ( $url !== $part ) {
			$out .= esc_html( substr( $part, strlen( $url ) ) );
		}
	}
	return $out;
}

/**
 * Render a test / docs link that opens in a new tab.
 *
 * @param string $url       URL.
 * @param string $label     Label.
 * @param string $css_class Extra CSS class.
 * @return string
 */
function tsolo_admin_blank_link( $url, $label, $css_class = 'button button-small' ) {
	return '<a class="' . esc_attr( $css_class ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
}

/**
 * How-to / compatibility box for a module tab.
 *
 * @param string $module Module key.
 * @param array  $def    Definition.
 */
function tsolo_admin_render_module_intro( $module, $def ) {
	$modules = tsolo_get_modules();
	echo '<div class="tsolo-module-intro">';
	if ( ! empty( $def['description'] ) ) {
		echo '<p class="description">' . wp_kses_post( tsolo_admin_emphasize( $def['description'] ) ) . '</p>';
	}
	if ( ! empty( $def['requires'] ) && is_array( $def['requires'] ) ) {
		$missing = array();
		$all     = tsolo_get_module_definitions();
		foreach ( $def['requires'] as $req ) {
			if ( empty( $modules[ $req ] ) ) {
				$missing[] = isset( $all[ $req ]['label'] ) ? $all[ $req ]['label'] : $req;
			}
		}
		if ( $missing ) {
			echo '<div class="notice notice-warning inline"><p>';
			echo esc_html(
				sprintf(
					/* translators: %s: module names */
					__( 'This module needs these modules enabled first: %s.', 'tso-login-organizer' ),
					implode( ', ', $missing )
				)
			);
			echo ' <a href="' . esc_url( admin_url( 'options-general.php?page=tso-login-organizer&tab=modules' ) ) . '">' . esc_html__( 'Open Modules', 'tso-login-organizer' ) . '</a>';
			echo '</p></div>';
		}
	}
	if ( ! empty( $def['test_links'] ) && is_array( $def['test_links'] ) ) {
		echo '<p class="tsolo-test-links">';
		foreach ( $def['test_links'] as $label => $url ) {
			if ( ! is_string( $url ) || '' === $url ) {
				continue;
			}
			echo tsolo_admin_blank_link( $url, $label ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		}
		echo '</p>';
	}
	if ( ! empty( $def['howto'] ) && is_array( $def['howto'] ) ) {
		echo '<div class="tsolo-howto">';
		echo '<p><strong>' . esc_html__( 'How to use', 'tso-login-organizer' ) . '</strong></p>';
		echo '<ol>';
		foreach ( $def['howto'] as $step ) {
			echo '<li>' . tsolo_admin_linkify_text( $step ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped/linkified in helper.
		}
		echo '</ol></div>';
	}
	$rules    = function_exists( 'tsolo_get_incompatibility_rules' ) ? tsolo_get_incompatibility_rules() : array();
	$has_scan = isset( $rules[ $module ] );
	if ( ( ! empty( $def['compat'] ) && is_array( $def['compat'] ) ) || $has_scan ) {
		echo '<div class="tsolo-compat">';
		echo '<p><strong>' . esc_html__( 'Compatibility', 'tso-login-organizer' ) . '</strong></p>';
		echo '<ul>';
		if ( ! empty( $def['compat'] ) && is_array( $def['compat'] ) ) {
			foreach ( $def['compat'] as $line ) {
				echo '<li>' . tsolo_admin_linkify_text( $line ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped/linkified in helper.
			}
		}
		if ( $has_scan ) {
			$report = tsolo_get_incompatibility_report();
			$rows   = isset( $report[ $module ] ) && is_array( $report[ $module ] ) ? $report[ $module ] : array();
			if ( ! $rows ) {
				echo '<li>' . esc_html__( 'Installed plugins check: none of the plugins on this site match the known incompatibilities for this module.', 'tso-login-organizer' ) . '</li>';
			} else {
				$reason = isset( $rules[ $module ]['reason'] ) ? (string) $rules[ $module ]['reason'] : '';
				foreach ( $rows as $row ) {
					if ( empty( $row['name'] ) ) {
						continue;
					}
					$state       = ! empty( $row['active'] )
						? __( 'active — likely clash', 'tso-login-organizer' )
						: __( 'installed, not active', 'tso-login-organizer' );
					$line_reason = ! empty( $row['reason'] ) ? (string) $row['reason'] : $reason;
					echo '<li><strong>' . esc_html( $row['name'] ) . '</strong> (' . esc_html( $state ) . ')';
					if ( '' !== $line_reason ) {
						echo ' — ' . esc_html( $line_reason );
					}
					echo '</li>';
				}
			}
		}
		echo '</ul></div>';
	}
	unset( $module );
	echo '</div>';
}

/**
 * Render module settings tab.
 *
 * @param string $module Module key.
 * @param array  $def    Module definition.
 */
function tsolo_admin_render_module_tab( $module, $def = array() ) {
	if ( empty( $def ) ) {
		$defs = tsolo_get_module_definitions();
		$def  = isset( $defs[ $module ] ) ? $defs[ $module ] : array();
	}
	tsolo_admin_render_module_intro( $module, $def );
	echo '<form method="post">';
	wp_nonce_field( TSOLO_NONCE_FORM );
	echo '<input type="hidden" name="tsolo_admin_action" value="save_module_settings" />';
	echo '<input type="hidden" name="module" value="' . esc_attr( $module ) . '" />';
	echo '<h2 class="title">' . esc_html__( 'Settings', 'tso-login-organizer' ) . '</h2>';

	if ( 'security' === $module ) {
		$s = TSOLO_Mod_Security::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Brute force', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[brute_force]" value="1" ' . checked( ! empty( $s['brute_force'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __(  'Enable lockouts', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Temporarily block logins from an IP after too many failed attempts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Max attempts', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[max_attempts]" value="' . esc_attr( $s['max_attempts'] ) . '" min="1" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Failed tries allowed before a lockout starts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Lockout minutes', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[lockout_minutes]" value="' . esc_attr( $s['lockout_minutes'] ) . '" min="1" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'How long the lockout lasts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Min password length', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[password_min_length]" value="' . esc_attr( $s['password_min_length'] ) . '" min="0" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Minimum characters when users choose a password (0 = no extra rule).', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Hide wp-login.php', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[hide_wp_login]" value="1" ' . checked( ! empty( $s['hide_wp_login'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __(  'Return 404 on wp-login.php', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Do not combine with Swiss Knife custom login URL or other plugins that rename wp-login.php.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'captcha' === $module ) {
		$s = TSOLO_Mod_Captcha::get_settings();
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Create a v2 "I\'m not a robot" key, then paste both values here.', 'tso-login-organizer' ) ) ) . ' ';
		echo tsolo_admin_blank_link( 'https://www.google.com/recaptcha/admin', __( 'Open Google reCAPTCHA admin', 'tso-login-organizer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo '</p>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Site key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[site_key]" value="' . esc_attr( $s['site_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Public key from the Google reCAPTCHA admin console.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Secret key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[secret_key]" value="' . esc_attr( $s['secret_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Private key used server-side to verify tokens. Leave empty and keep the module off if CleanTalk (or similar) already protects forms.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'moderation' === $module ) {
		$s = TSOLO_Mod_Moderation::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . esc_html__( 'Email confirmation', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[email_confirm]" value="1" ' . checked( ! empty( $s['email_confirm'] ), true, false ) . ' /> ' . esc_html__( 'Require users to confirm their email before they can log in', 'tso-login-organizer' ) . '</label>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'This overrides Core → “Log users in after registration”. The new person stays logged out until they open the link in the email, even if that Core box is checked.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Admin approval', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[admin_approve]" value="1" ' . checked( ! empty( $s['admin_approve'] ), true, false ) . ' /> ' . esc_html__( 'New accounts stay pending until an administrator approves them', 'tso-login-organizer' ) . '</label>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'This also overrides Core → “Log users in after registration”. The new person stays logged out until an administrator approves them on the Users screen, even if that Core box is checked. If both moderation boxes are off, the Core option signs them in as usual.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'restrictions' === $module ) {
		$s = TSOLO_Mod_Restrictions::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Private site', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[private_site]" value="1" ' . checked( ! empty( $s['private_site'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __(  'Require login for all pages', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Guests are redirected to the login page. Login, register and password URLs stay public.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
		echo '<h3>' . esc_html__( 'Shortcodes', 'tso-login-organizer' ) . '</h3>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Paste a shortcode into the content of a post or a page. In the block editor, add a Shortcode block and paste the whole snippet, including the closing tag. In the classic editor, paste it in the text. The words between the opening tag and the closing tag are the part that is shown or hidden. Text outside the shortcode stays visible to everyone. These tags do nothing in a menu or in a widget unless that area runs shortcodes.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __(  'Example: a note only members see', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-logged-in]\n" . __( 'Welcome back. Your downloads are on this page.', 'tso-login-organizer' ) . "\n[/tsolo-logged-in]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Guests see nothing from that block. Logged-in users see the sentence.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __(  'Example: a login prompt only guests see', 'tso-login-organizer' ) ) ) . '</strong></p>';
		$login_example = tsolo_get_action_url( 'login' );
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-logged-out]\n" . sprintf( /* translators: %s: login URL */ __( 'Please log in at %s to read the rest of this page.', 'tso-login-organizer' ), $login_example ) . "\n[/tsolo-logged-out]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Logged-in users see nothing from that block. Guests see the sentence.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __(  'Example: one role only', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-role role=\"subscriber\"]\n" . __( 'Subscriber offer: use the code WELCOME.', 'tso-login-organizer' ) . "\n[/tsolo-role]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Only users with the Subscriber role see that sentence. Use the role slug (subscriber, customer, editor), not the translated name. Several roles: role="subscriber,customer". Everyone else, including guests, sees nothing from that block.', 'tso-login-organizer' ) ) ) . '</p>';
	} elseif ( 'profiles' === $module ) {
		$s = TSOLO_Mod_Profiles::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Block wp-admin', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[block_admin]" value="1" ' . checked( ! empty( $s['block_admin'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __(  'Redirect users without edit_posts to the profile page', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Subscribers and similar roles cannot open /wp-admin/; they use the frontend profile instead.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'notifications' === $module ) {
		$s = TSOLO_Mod_Notifications::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . esc_html__( 'HTML emails', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[html]" value="1" ' . checked( ! empty( $s['html'] ), true, false ) . ' /> ' . esc_html__( 'Send notification emails as HTML', 'tso-login-organizer' ) . '</label></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'New user subject', 'tso-login-organizer' ) ) ) . '</th><td><input class="large-text" type="text" name="mod[new_user_subject]" value="' . esc_attr( $s['new_user_subject'] ) . '" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Subject line for the welcome / new-account email.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'New user body', 'tso-login-organizer' ) . '</th><td><textarea class="large-text" rows="6" name="mod_new_user_body">' . esc_textarea( $s['new_user_body'] ) . '</textarea><p class="description">{site_name} {user_login} {user_email} {login_url}</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Reset body', 'tso-login-organizer' ) . '</th><td><textarea class="large-text" rows="6" name="mod_reset_body">' . esc_textarea( $s['reset_body'] ) . '</textarea><p class="description">{user_login} {reset_url} {site_name}</p></td></tr>';
		echo '</table>';
	} elseif ( 'social' === $module ) {
		tsolo_admin_render_social_settings();
	} elseif ( 'mailchimp' === $module ) {
		$s = TSOLO_Mod_Mailchimp::get_settings();
		echo '<p class="description tsolo-option-help">';
		echo tsolo_admin_blank_link( 'https://admin.mailchimp.com/account/api/', __( 'Open Mailchimp API keys', 'tso-login-organizer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo ' · ';
		echo tsolo_admin_blank_link( 'https://mailchimp.com/help/find-audience-id/', __( 'How to find Audience ID', 'tso-login-organizer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo '</p>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'API key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[api_key]" value="' . esc_attr( $s['api_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Mailchimp API key from your account. Data is sent only when this module is enabled and a list is set.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'List ID', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[list_id]" value="' . esc_attr( $s['list_id'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Audience / list ID where new subscribers are added.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Show checkbox', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[checkbox]" value="1" ' . checked( ! empty( $s['checkbox'] ), true, false ) . ' /> ' . esc_html__( 'Ask for newsletter consent on the registration form', 'tso-login-organizer' ) . '</label></td></tr>';
		echo '</table>';
	} elseif ( 'redirection' === $module ) {
		$s        = TSOLO_Mod_Redirection::get_settings();
		$roles    = wp_roles()->get_names();
		$types    = array(
			'login'    => __( 'After login', 'tso-login-organizer' ),
			'logout'   => __( 'After logout', 'tso-login-organizer' ),
			'register' => __( 'After registration', 'tso-login-organizer' ),
		);
		$ex_home  = home_url( '/' );
		$ex_shop  = home_url( '/shop/' );
		$ex_admin = admin_url();
		$ex_hello = home_url( '/welcome/' );
		echo '<div class="tsolo-howto"><p><strong>' . wp_kses_post( tsolo_admin_emphasize( __(  'What to type in the boxes', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<p>' . wp_kses_post( tsolo_admin_emphasize( __(  'Type a full address, including https:// and your domain. A path alone (/shop/) is rejected by the browser. Leave a box empty to keep the WordPress default for that case: usually the dashboard after login, the home page after logout, and the login page after registration.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p>' . esc_html(
			sprintf(
				/* translators: 1: home URL, 2: shop URL, 3: admin URL, 4: welcome URL */
				__( 'Example. After login → Default: %1$s (everyone). Subscriber: %2$s (only subscribers; they ignore Default). Administrator: %3$s. After logout → Default: %1$s. After registration → Default: %4$s. Leave Administrator empty under After login if those users should follow Default.', 'tso-login-organizer' ),
				$ex_home,
				$ex_shop,
				$ex_admin,
				$ex_hello
			)
		) . '</p></div>';
		$placeholders = array(
			'login'    => $ex_shop,
			'logout'   => $ex_home,
			'register' => $ex_hello,
		);
		foreach ( $types as $type => $heading ) {
			echo '<h2>' . esc_html( $heading ) . '</h2>';
			echo '<div class="tsolo-redir-grid">';
			$placeholder = isset( $placeholders[ $type ] ) ? $placeholders[ $type ] : $ex_home;
			echo '<p><label>' . esc_html__( 'Default (all roles)', 'tso-login-organizer' ) . '</label><input class="regular-text" type="url" name="redir_' . esc_attr( $type ) . '[_default]" value="' . esc_attr( isset( $s[ $type ]['_default'] ) ? $s[ $type ]['_default'] : '' ) . '" placeholder="' . esc_attr( $placeholder ) . '" /></p>';
			foreach ( $roles as $role => $label ) {
				$val = isset( $s[ $type ][ $role ] ) ? $s[ $type ][ $role ] : '';
				echo '<p><label>' . esc_html( $label ) . '</label><input class="regular-text" type="url" name="redir_' . esc_attr( $type ) . '[' . esc_attr( $role ) . ']" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr( $placeholder ) . '" /></p>';
			}
			echo '</div>';
		}
	} elseif ( 'avatars' === $module ) {
		$s = TSOLO_Mod_Avatars::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Max size (KB)', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[max_kb]" value="' . esc_attr( $s['max_kb'] ) . '" min="50" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Maximum upload size for profile pictures stored under uploads/tso-login-organizer/.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'favorites' === $module ) {
		$s = TSOLO_Mod_Favorites::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __(  'Show on posts', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[show_on_posts]" value="1" ' . checked( ! empty( $s['show_on_posts'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __(  'Show add/remove favorite controls on single posts', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Logged-in users see a button at the end of each single post. The list page uses the Favorites URL slug from Core settings.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'two_factor' === $module ) {
		$s     = TSOLO_Mod_Two_Factor::get_settings();
		$roles = wp_roles()->get_names();
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Tick roles that must enter a TOTP code after the password. Users enable the authenticator on the frontend profile (Profiles) or in wp-admin → Profile.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Required roles', 'tso-login-organizer' ) . '</legend>';
		foreach ( $roles as $role => $label ) {
			$checked = ! empty( $s['required_roles'] ) && in_array( $role, (array) $s['required_roles'], true );
			echo '<label style="display:block;"><input type="checkbox" name="mod[required_roles][]" value="' . esc_attr( $role ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $label ) . '</label>';
		}
		echo '</fieldset>';
	} else {
		echo '<p>' . esc_html__( 'No extra settings for this module.', 'tso-login-organizer' ) . '</p>';
	}

	submit_button( __( 'Save module settings', 'tso-login-organizer' ) );
	echo '</form>';
}

/**
 * Social Login settings + provider setup instructions.
 */
function tsolo_admin_render_social_settings() {
	$s = TSOLO_Mod_Social::get_settings();

	$providers = array(
		'google'   => array(
			'title'         => __( 'Google', 'tso-login-organizer' ),
			'console'       => 'https://console.cloud.google.com/apis/credentials',
			'console_label' => __( 'Open Google Cloud → Credentials', 'tso-login-organizer' ),
			'steps'         => array(
				__( 'Open Google Cloud Credentials and create (or select) a project.', 'tso-login-organizer' ),
				__( 'Configure the OAuth consent screen (External or Internal) and add your test users if needed.', 'tso-login-organizer' ),
				__( 'Create credentials → OAuth client ID → Application type: Web application.', 'tso-login-organizer' ),
				__( 'Under Authorized redirect URIs, paste the Google callback URL shown below.', 'tso-login-organizer' ),
				__( 'Copy Client ID and Client secret into the fields below, then Save.', 'tso-login-organizer' ),
			),
			'id'            => 'google_client_id',
			'secret'        => 'google_client_secret',
			'id_label'      => __( 'Google client ID', 'tso-login-organizer' ),
			'secret_label'  => __( 'Google client secret', 'tso-login-organizer' ),
		),
		'facebook' => array(
			'title'         => __( 'Facebook', 'tso-login-organizer' ),
			'console'       => 'https://developers.facebook.com/apps/',
			'console_label' => __( 'Open Facebook Developers → Apps', 'tso-login-organizer' ),
			'steps'         => array(
				__( 'Create an app (type Consumer / Authenticate and request data from users with Facebook Login).', 'tso-login-organizer' ),
				__( 'Add the Facebook Login product to the app.', 'tso-login-organizer' ),
				__( 'In Facebook Login → Settings, paste the Facebook callback URL below into Valid OAuth Redirect URIs.', 'tso-login-organizer' ),
				__( 'In Settings → Basic, copy App ID and App Secret into the fields below, then Save.', 'tso-login-organizer' ),
			),
			'id'            => 'facebook_app_id',
			'secret'        => 'facebook_app_secret',
			'id_label'      => __( 'Facebook app ID', 'tso-login-organizer' ),
			'secret_label'  => __( 'Facebook app secret', 'tso-login-organizer' ),
		),
		'x'        => array(
			'title'         => __( 'X (Twitter)', 'tso-login-organizer' ),
			'console'       => 'https://developer.x.com/en/portal/dashboard',
			'console_label' => __( 'Open X Developer Portal', 'tso-login-organizer' ),
			'steps'         => array(
				__( 'Create a Project and App with OAuth 2.0 enabled.', 'tso-login-organizer' ),
				__( 'Set App permissions to include users email if you want email on new accounts.', 'tso-login-organizer' ),
				__( 'Add the X callback URL below as a Callback URI / Redirect URL.', 'tso-login-organizer' ),
				__( 'Copy Client ID and Client Secret into the fields below, then Save.', 'tso-login-organizer' ),
			),
			'id'            => 'x_client_id',
			'secret'        => 'x_client_secret',
			'id_label'      => __( 'X (Twitter) client ID', 'tso-login-organizer' ),
			'secret_label'  => __( 'X (Twitter) client secret', 'tso-login-organizer' ),
		),
	);

	echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __(  'Leave a provider empty to hide its button. After saving, test from the login page.', 'tso-login-organizer' ) ) ) . '</p>';

	foreach ( $providers as $key => $p ) {
		$cb = add_query_arg( 'tsolo_social', $key, home_url( '/' ) );
		echo '<div class="tsolo-provider-card">';
		echo '<h3>' . esc_html( $p['title'] ) . '</h3>';
		echo '<p>' . tsolo_admin_blank_link( $p['console'], $p['console_label'] ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo '<ol class="tsolo-provider-steps">';
		foreach ( $p['steps'] as $step ) {
			echo '<li>' . esc_html( $step ) . '</li>';
		}
		echo '</ol>';
		echo '<p><strong>' . esc_html__( 'Redirect / callback URI (copy into the provider console):', 'tso-login-organizer' ) . '</strong><br />';
		echo '<code class="tsolo-callback-uri">' . esc_html( $cb ) . '</code> ';
		echo tsolo_admin_blank_link( $cb, __( 'Open callback URL', 'tso-login-organizer' ), 'button button-small' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo '</p>';
		echo '<div class="tsolo-social-grid">';
		echo '<p><label for="tsolo-' . esc_attr( $p['id'] ) . '">' . esc_html( $p['id_label'] ) . '</label>';
		echo '<input id="tsolo-' . esc_attr( $p['id'] ) . '" class="regular-text" type="text" name="mod[' . esc_attr( $p['id'] ) . ']" value="' . esc_attr( $s[ $p['id'] ] ) . '" autocomplete="off" /></p>';
		echo '<p><label for="tsolo-' . esc_attr( $p['secret'] ) . '">' . esc_html( $p['secret_label'] ) . '</label>';
		echo '<input id="tsolo-' . esc_attr( $p['secret'] ) . '" class="regular-text" type="text" name="mod[' . esc_attr( $p['secret'] ) . ']" value="' . esc_attr( $s[ $p['secret'] ] ) . '" autocomplete="off" /></p>';
		echo '</div>';
		echo '</div>';
	}
}
