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
add_action( 'admin_init', 'tsolo_admin_boot_ui_locale', 1 );
add_action( 'admin_init', 'tsolo_admin_handle_post' );
add_action( 'admin_init', 'tsolo_admin_handle_lang_switch' );
add_action( 'wp_ajax_tsolo_check_permalinks', 'tsolo_ajax_check_permalinks' );
add_filter( 'plugin_action_links_' . plugin_basename( TSOLO_FILE ), 'tsolo_plugin_action_links' );

/**
 * Admin screen URL under Tools (tools.php).
 *
 * @param array $query Extra query args (e.g. tab).
 * @return string
 */
function tsolo_admin_page_url( $query = array() ) {
	$args = array_merge(
		array(
			'page' => 'tso-login-organizer',
		),
		is_array( $query ) ? $query : array()
	);
	return add_query_arg( $args, admin_url( 'tools.php' ) );
}

/**
 * Settings link.
 *
 * @param array $links Links.
 * @return array
 */
function tsolo_plugin_action_links( $links ) {
	$links[] = '<a href="' . esc_url( tsolo_admin_page_url() ) . '">' . esc_html__( 'Settings', 'tso-login-organizer' ) . '</a>';
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
		'No WordPress page needed.',
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
		'does not delete them',
		'No los borra',
		'No els esborra',
		'Leave it active',
		'Déjalo activo',
		'Deixa’l actiu',
		'Deactivate Theme My Login',
		'Desactiva Theme My Login',
		'Desactiva Theme My Login.',
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
		$token            = "\x1E" . $i . "\x1F";
		$text             = str_replace( $phrase, $token, $text );
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
	echo '</p><p><button type="button" class="button tsolo-check-permalinks">' . esc_html__( 'Check friendly URLs', 'tso-login-organizer' ) . '</button></p></div>';
}

/**
 * AJAX: flush rewrite rules and return updated permalink status HTML (no full page reload).
 */
function tsolo_ajax_check_permalinks() {
	if ( ! current_user_can( 'manage_options' ) || ! tsolo_verify_ajax_nonce() ) {
		wp_send_json_error(
			array(
				'message' => __( 'You are not allowed to do this.', 'tso-login-organizer' ),
			),
			403
		);
	}
	flush_rewrite_rules( false );
	$health = tsolo_permalink_health();
	if ( ! empty( $health['ok'] ) ) {
		$message = __( 'Friendly URL check finished: permalinks are not Plain and the login rewrite rule is present.', 'tso-login-organizer' );
		$type    = 'success';
	} elseif ( empty( $health['using'] ) ) {
		$message = __( 'Friendly URL check finished: WordPress is using Plain permalinks. Set Permalinks to Post name (/%postname%/).', 'tso-login-organizer' );
		$type    = 'warning';
	} else {
		$message = __( 'Friendly URL check finished: rewrite rules were refreshed but the login rule is still missing. Open Settings → Permalinks and click Save Changes.', 'tso-login-organizer' );
		$type    = 'warning';
	}
	ob_start();
	tsolo_admin_render_permalink_status();
	$html = ob_get_clean();
	wp_send_json_success(
		array(
			'html'    => $html,
			'message' => $message,
			'type'    => $type,
			'health'  => $health,
		)
	);
}

/**
 * Apply the chosen UI language before the menu and the page are translated.
 */
function tsolo_admin_boot_ui_locale() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( 'tso-login-organizer' !== tsolo_get_admin_query_arg( 'page' ) ) {
		return;
	}
	tsolo_apply_admin_ui_locale( tsolo_get_ui_lang() );
}

/**
 * Register menu.
 */
function tsolo_admin_menu() {
	add_management_page(
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
	$tab = sanitize_key( tsolo_get_admin_query_arg( 'tab', 'core' ) );
	wp_safe_redirect( tsolo_admin_page_url( $tab ? array( 'tab' => $tab ) : array() ) );
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
		$imported = tsolo_import_tml_settings( true );
		if ( 'imported' === $imported ) {
			tsolo_request_rewrite_flush();
			tsolo_admin_set_flash( __( 'Theme My Login settings imported.', 'tso-login-organizer' ), 'success' );
		} else {
			tsolo_admin_set_flash( __( 'No Theme My Login settings were found, so nothing was changed.', 'tso-login-organizer' ), 'warning' );
		}
		wp_safe_redirect( tsolo_admin_page_url( array( 'tab' => 'migrate' ) ) );
		exit;
	}

	if ( 'save_core' === $action ) {
		$settings = array(
			'ajax'              => tsolo_admin_post_is_set( 'ajax' ) ? 1 : 0,
			'login_type'        => tsolo_get_admin_post_text( 'login_type', 'default' ),
			'registration_type' => tsolo_get_admin_post_text( 'registration_type', 'default' ),
			'user_passwords'    => tsolo_admin_post_is_set( 'user_passwords' ) ? 1 : 0,
			'auto_login'        => tsolo_admin_post_is_set( 'auto_login' ) ? 1 : 0,
			'texts'             => tsolo_get_admin_post_array( 'texts' ),
			'branding'          => array(
				'logo_url'       => tsolo_get_admin_post_text( 'branding_logo_url' ),
				'form_max_width' => absint( tsolo_get_admin_post_text( 'branding_form_max_width', '0' ) ),
				'extra_class'    => tsolo_get_admin_post_text( 'branding_extra_class' ),
			),
			'slugs'             => tsolo_get_admin_post_array( 'slugs' ),
		);
		tsolo_update_settings( $settings );
		if ( tsolo_admin_post_is_set( 'users_can_register' ) ) {
			update_option( 'users_can_register', 1 );
		} elseif ( ! is_multisite() ) {
			update_option( 'users_can_register', 0 );
		}
		$role = sanitize_key( tsolo_get_admin_post_text( 'default_role', get_option( 'default_role', 'subscriber' ) ) );
		if ( $role && get_role( $role ) ) {
			update_option( 'default_role', $role );
		}
		tsolo_request_rewrite_flush();
		tsolo_admin_set_flash( __( 'Core settings saved.', 'tso-login-organizer' ), 'success' );
		wp_safe_redirect( tsolo_admin_page_url( array( 'tab' => 'core' ) ) );
		exit;
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
		tsolo_request_rewrite_flush();
		if ( $collide ) {
			tsolo_admin_set_flash(
				__( 'Modules updated. You enabled a module that may conflict with Swiss Knife, Kadence Security or CleanTalk — keep overlapping features off elsewhere.', 'tso-login-organizer' ),
				'warning'
			);
		} else {
			tsolo_admin_set_flash( __( 'Modules updated.', 'tso-login-organizer' ), 'success' );
		}
		wp_safe_redirect( tsolo_admin_page_url( array( 'tab' => 'modules' ) ) );
		exit;
	}

	if ( 'save_module_settings' === $action ) {
		$module = tsolo_get_admin_post_text( 'module' );
		tsolo_admin_save_module_settings( $module );
		tsolo_admin_set_flash( __( 'Module settings saved.', 'tso-login-organizer' ), 'success' );
		$tab = $module ? $module : 'modules';
		wp_safe_redirect( tsolo_admin_page_url( array( 'tab' => $tab ) ) );
		exit;
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
	// inline + data-tsoan-keep: stay visible with TSO Admin Notices (non-inline "untagged" notices are hidden after F5).
	echo '<div class="notice inline ' . esc_attr( $class ) . ' is-dismissible tsolo-admin-flash" data-tsoan-keep="1"><p>' . esc_html( $notice['message'] ) . '</p></div>';
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
		'fields'        => TSOLO_OPT_FIELDS,
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
		'social'        => array( 'link_existing' ),
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
	if ( 'redirection' === $module && class_exists( 'TSOLO_Mod_Redirection' ) ) {
		$data = TSOLO_Mod_Redirection::sanitize_settings(
			array(
				'login'    => tsolo_get_admin_post_array( 'redir_login' ),
				'logout'   => tsolo_get_admin_post_array( 'redir_logout' ),
				'register' => tsolo_get_admin_post_array( 'redir_register' ),
			)
		);
	}
	if ( 'two_factor' === $module ) {
		$roles                  = isset( $data['required_roles'] ) && is_array( $data['required_roles'] ) ? $data['required_roles'] : array();
		$data['required_roles'] = array_values( array_map( 'sanitize_key', $roles ) );
	}
	if ( 'mailchimp' === $module && class_exists( 'TSOLO_Mod_Mailchimp' ) ) {
		$data = TSOLO_Mod_Mailchimp::sanitize_settings( $data );
	}
	if ( 'security' === $module && class_exists( 'TSOLO_Mod_Security' ) ) {
		$data = TSOLO_Mod_Security::sanitize_settings( $data );
	}
	if ( 'restrictions' === $module && class_exists( 'TSOLO_Mod_Restrictions' ) ) {
		$data = TSOLO_Mod_Restrictions::sanitize_settings( $data );
	}
	if ( 'captcha' === $module && class_exists( 'TSOLO_Mod_Captcha' ) ) {
		$data = TSOLO_Mod_Captcha::sanitize_settings( $data );
	}
	if ( 'fields' === $module && class_exists( 'TSOLO_Mod_Fields' ) ) {
		$data = TSOLO_Mod_Fields::sanitize_settings(
			array(
				'fields' => tsolo_get_admin_post_array( 'mod_fields' ),
			)
		);
	}
	if ( 'profiles' === $module && class_exists( 'TSOLO_Mod_Profiles' ) ) {
		$data = TSOLO_Mod_Profiles::sanitize_settings( $data );
	}
	if ( 'notifications' === $module && class_exists( 'TSOLO_Mod_Notifications' ) ) {
		$data = TSOLO_Mod_Notifications::sanitize_settings( $data );
	}
	if ( 'two_factor' === $module && class_exists( 'TSOLO_Mod_Two_Factor' ) ) {
		$data = TSOLO_Mod_Two_Factor::sanitize_settings( $data );
	}
	if ( 'social' === $module && class_exists( 'TSOLO_Mod_Social' ) ) {
		$data = TSOLO_Mod_Social::sanitize_settings( $data );
	}
	if ( 'avatars' === $module && class_exists( 'TSOLO_Mod_Avatars' ) ) {
		$data = TSOLO_Mod_Avatars::sanitize_settings( $data );
	}
	if ( 'favorites' === $module && class_exists( 'TSOLO_Mod_Favorites' ) ) {
		$data = TSOLO_Mod_Favorites::sanitize_settings( $data );
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

	$lang_base    = tsolo_admin_page_url(
		array(
			'tab' => $tab,
		)
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
		<p class="description"><?php esc_html_e( 'Frontend login, registration and account pages. Optional modules add redirection, moderation, security and more.', 'tso-login-organizer' ); ?></p>
		<nav class="nav-tab-wrapper tsolo-nav-tabs tsolo-nav-primary" style="display:flex;flex-wrap:nowrap;clear:both;width:100%;float:none;" aria-label="<?php esc_attr_e( 'Plugin sections', 'tso-login-organizer' ); ?>">
			<a class="nav-tab <?php echo 'core' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( tsolo_admin_page_url( array( 'tab' => 'core' ) ) ); ?>" data-tsolo-tab="core"><?php esc_html_e( 'Core', 'tso-login-organizer' ); ?></a>
			<a class="nav-tab <?php echo 'modules' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( tsolo_admin_page_url( array( 'tab' => 'modules' ) ) ); ?>" data-tsolo-tab="modules"><?php esc_html_e( 'Modules', 'tso-login-organizer' ); ?></a>
			<a class="nav-tab <?php echo 'migrate' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( tsolo_admin_page_url( array( 'tab' => 'migrate' ) ) ); ?>" data-tsolo-tab="migrate"><?php esc_html_e( 'Migration', 'tso-login-organizer' ); ?></a>
		</nav>
		<?php
		$tsolo_enabled_modules = array();
		foreach ( $defs as $key => $def ) {
			if ( ! empty( $modules[ $key ] ) ) {
				$tsolo_enabled_modules[ $key ] = $def;
			}
		}
		if ( $tsolo_enabled_modules ) :
			?>
		<nav class="nav-tab-wrapper tsolo-nav-tabs tsolo-nav-modules" style="display:flex;flex-wrap:wrap;clear:both;width:100%;float:none;" aria-label="<?php esc_attr_e( 'Enabled modules', 'tso-login-organizer' ); ?>">
			<?php foreach ( $tsolo_enabled_modules as $key => $def ) : ?>
				<a class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( tsolo_admin_page_url( array( 'tab' => $key ) ) ); ?>" data-tsolo-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
			<?php endif; ?>
		<?php tsolo_admin_print_flash(); ?>
		<?php if ( $collisions ) : ?>
			<div class="notice inline notice-warning tsolo-collision-notice" data-tsoan-keep="1"><p>
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

		<div class="tsolo-tab-panels" style="clear:both;width:100%;">
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
					<tr>
						<th scope="row"><?php esc_html_e( 'Default role', 'tso-login-organizer' ); ?></th>
						<td>
							<?php
							$current_role = (string) get_option( 'default_role', 'subscriber' );
							$roles        = function_exists( 'get_editable_roles' ) ? get_editable_roles() : wp_roles()->roles;
							echo '<select name="default_role">';
							foreach ( $roles as $role_key => $role_obj ) {
								$label = is_array( $role_obj ) && isset( $role_obj['name'] ) ? translate_user_role( $role_obj['name'] ) : $role_key;
								echo '<option value="' . esc_attr( $role_key ) . '" ' . selected( $current_role, $role_key, false ) . '>' . esc_html( $label ) . '</option>';
							}
							echo '</select>';
							?>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Role assigned to new accounts on registration. Same as Settings → General → New User Default Role.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
					<?php
					$texts    = isset( $settings['texts'] ) && is_array( $settings['texts'] ) ? $settings['texts'] : array();
					$branding = isset( $settings['branding'] ) && is_array( $settings['branding'] ) ? $settings['branding'] : array();
					?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Form texts', 'tso-login-organizer' ); ?></th>
						<td>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Leave a field empty to keep the built-in label. These strings only change this plugin’s frontend forms.', 'tso-login-organizer' ) ) ); ?></p>
							<p><label for="tsolo-text-login-heading"><?php esc_html_e( 'Login heading', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-login-heading" class="regular-text" type="text" name="texts[login_heading]" value="<?php echo esc_attr( isset( $texts['login_heading'] ) ? $texts['login_heading'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-login-button"><?php esc_html_e( 'Login button', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-login-button" class="regular-text" type="text" name="texts[login_button]" value="<?php echo esc_attr( isset( $texts['login_button'] ) ? $texts['login_button'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-register-heading"><?php esc_html_e( 'Register heading', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-register-heading" class="regular-text" type="text" name="texts[register_heading]" value="<?php echo esc_attr( isset( $texts['register_heading'] ) ? $texts['register_heading'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-register-button"><?php esc_html_e( 'Register button', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-register-button" class="regular-text" type="text" name="texts[register_button]" value="<?php echo esc_attr( isset( $texts['register_button'] ) ? $texts['register_button'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-lost-heading"><?php esc_html_e( 'Lost password heading', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-lost-heading" class="regular-text" type="text" name="texts[lostpassword_heading]" value="<?php echo esc_attr( isset( $texts['lostpassword_heading'] ) ? $texts['lostpassword_heading'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-lost-button"><?php esc_html_e( 'Lost password button', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-lost-button" class="regular-text" type="text" name="texts[lostpassword_button]" value="<?php echo esc_attr( isset( $texts['lostpassword_button'] ) ? $texts['lostpassword_button'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-reset-button"><?php esc_html_e( 'Reset password button', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-reset-button" class="regular-text" type="text" name="texts[resetpass_button]" value="<?php echo esc_attr( isset( $texts['resetpass_button'] ) ? $texts['resetpass_button'] : '' ); ?>" /></p>
							<p><label for="tsolo-text-passmail"><?php esc_html_e( 'Registration “password by email” note', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-text-passmail" class="large-text" type="text" name="texts[reg_passmail]" value="<?php echo esc_attr( isset( $texts['reg_passmail'] ) ? $texts['reg_passmail'] : '' ); ?>" /></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Form branding', 'tso-login-organizer' ); ?></th>
						<td>
							<p><label for="tsolo-brand-logo"><?php esc_html_e( 'Logo URL', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-brand-logo" class="large-text" type="url" name="branding_logo_url" value="<?php echo esc_attr( isset( $branding['logo_url'] ) ? $branding['logo_url'] : '' ); ?>" placeholder="https://" /></p>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Optional image shown above login, register and password forms. Use a full https:// URL (Media Library → copy file URL).', 'tso-login-organizer' ) ) ); ?></p>
							<p><label for="tsolo-brand-width"><?php esc_html_e( 'Form max width (px)', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-brand-width" type="number" name="branding_form_max_width" value="<?php echo esc_attr( isset( $branding['form_max_width'] ) ? (string) (int) $branding['form_max_width'] : '0' ); ?>" min="0" max="1200" step="1" /></p>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( '0 keeps the default (~448px). Allowed range 200–1200.', 'tso-login-organizer' ) ) ); ?></p>
							<p><label for="tsolo-brand-class"><?php esc_html_e( 'Extra CSS class', 'tso-login-organizer' ); ?></label><br />
							<input id="tsolo-brand-class" class="regular-text" type="text" name="branding_extra_class" value="<?php echo esc_attr( isset( $branding['extra_class'] ) ? $branding['extra_class'] : '' ); ?>" /></p>
							<p class="description tsolo-option-help"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Added to the outer .tsolo wrapper so your theme or custom CSS can target these forms. Up to five class names.', 'tso-login-organizer' ) ) ); ?></p>
						</td>
					</tr>
				</table>
				<div class="tsolo-slugs-panel">
					<h2 class="tsolo-slugs-heading"><?php esc_html_e( 'URL slugs', 'tso-login-organizer' ); ?></h2>
					<p class="description"><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'These values are the path after your site URL for each virtual form. With the default "login" slug, the form is at:', 'tso-login-organizer' ) ) ); ?> <code><?php echo esc_html( home_url( '/login/' ) ); ?></code></p>
					<ol class="description tsolo-slugs-howto">
						<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Friendly addresses work only when Permalinks is not Plain. The check is on the Migration tab.', 'tso-login-organizer' ) ) ); ?></li>
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
									<p class="description tsolo-option-help"><a href="<?php echo esc_url( tsolo_admin_page_url( array( 'tab' => $key ) ) ); ?>" data-tsolo-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( sprintf( /* translators: %s: module name */ __( 'Open "%s" settings tab', 'tso-login-organizer' ), $def['label'] ) ); ?></a></p>
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
			<div class="tsolo-migrate-guide">
				<p><strong><?php esc_html_e( 'Your old Theme My Login setup is safe.', 'tso-login-organizer' ); ?></strong>
					<?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Import only copies the free settings into this plugin. It does not delete them, it does not change Theme My Login, and it does not turn that plugin off. The original options stay in the database.', 'tso-login-organizer' ) ) ); ?></p>
				<h3><?php esc_html_e( 'What is copied', 'tso-login-organizer' ); ?></h3>
				<ul class="ul-disc">
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Whether login, registration and password forms update without reloading the page (AJAX).', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'What visitors may type to log in: username, email, or both.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Whether registration asks for username and email, or email only.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Whether new users choose their own password, or WordPress emails a generated one.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Whether a new account is signed in immediately after registration.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'The address slugs for login, logout, register, lost password, reset password, dashboard and confirm-action.', 'tso-login-organizer' ) ) ); ?></li>
				</ul>
				<p><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Extensions, email designs, widgets, menus and the WordPress pages Theme My Login created are not copied. Those pages can be moved to the trash; the addresses keep working here as virtual pages. The shortcode [theme-my-login] already works with this plugin.', 'tso-login-organizer' ) ) ); ?></p>
				<h3><?php esc_html_e( 'When to deactivate Theme My Login', 'tso-login-organizer' ); ?></h3>
				<p><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Either moment works for the copy. The saved options remain if Theme My Login is still active and also if you already deactivated it. Leave it active until you have opened the new addresses and they look right, then deactivate it. If both plugins stay active they can fight over the same login addresses.', 'tso-login-organizer' ) ) ); ?></p>
				<h3><?php esc_html_e( 'After the import', 'tso-login-organizer' ); ?></h3>
				<ol>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Keep friendly permalinks (not Plain). Use the check below.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Move the old Theme My Login pages to the trash if they are listed below. This plugin already serves those addresses.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Open each virtual URL from Core → URL slugs and confirm the form loads.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Update menus and links that still point at the old pages.', 'tso-login-organizer' ) ) ); ?></li>
					<li><?php echo wp_kses_post( tsolo_admin_emphasize( __( 'Deactivate Theme My Login.', 'tso-login-organizer' ) ) ); ?></li>
				</ol>
			</div>
			<p><?php echo tsolo_tml_options_present() ? esc_html__( 'TML options detected on this site.', 'tso-login-organizer' ) : esc_html__( 'No TML options detected (you can still use this plugin with its own defaults).', 'tso-login-organizer' ); ?></p>
			<?php tsolo_admin_render_virtual_pages_notice(); ?>
			<?php tsolo_admin_render_permalink_status(); ?>
			<form method="post">
				<?php wp_nonce_field( TSOLO_NONCE_FORM ); ?>
				<input type="hidden" name="tsolo_admin_action" value="import_tml" />
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
			echo ' <a href="' . esc_url( tsolo_admin_page_url( array( 'tab' => 'modules' ) ) ) . '">' . esc_html__( 'Open Modules', 'tso-login-organizer' ) . '</a>';
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
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Brute force', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[brute_force]" value="1" ' . checked( ! empty( $s['brute_force'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __( 'Enable lockouts', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Temporarily block logins from an IP after too many failed attempts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Max attempts', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[max_attempts]" value="' . esc_attr( $s['max_attempts'] ) . '" min="1" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Failed tries allowed before a lockout starts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Lockout minutes', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[lockout_minutes]" value="' . esc_attr( $s['lockout_minutes'] ) . '" min="1" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'How long the lockout lasts.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Min password length', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[password_min_length]" value="' . esc_attr( $s['password_min_length'] ) . '" min="0" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Minimum characters when users choose a password (0 = no extra rule).', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Hide wp-login.php', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[hide_wp_login]" value="1" ' . checked( ! empty( $s['hide_wp_login'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __( 'Return 404 on wp-login.php', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Do not combine with Swiss Knife custom login URL or other plugins that rename wp-login.php.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'captcha' === $module ) {
		$s = TSOLO_Mod_Captcha::get_settings();
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Pick a provider, create keys for your domain, then paste both values here.', 'tso-login-organizer' ) ) ) . ' ';
		echo tsolo_admin_blank_link( 'https://www.google.com/recaptcha/admin', __( 'Google reCAPTCHA', 'tso-login-organizer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo ' · ';
		echo tsolo_admin_blank_link( 'https://dash.cloudflare.com/?to=/:account/turnstile', __( 'Cloudflare Turnstile', 'tso-login-organizer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
		echo '</p>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . esc_html__( 'Provider', 'tso-login-organizer' ) . '</th><td>';
		foreach ( array(
			'v2'        => __( 'Google reCAPTCHA v2 (checkbox)', 'tso-login-organizer' ),
			'v3'        => __( 'Google reCAPTCHA v3 (invisible score)', 'tso-login-organizer' ),
			'turnstile' => __( 'Cloudflare Turnstile', 'tso-login-organizer' ),
		) as $val => $label ) {
			echo '<label style="display:block;"><input type="radio" name="mod[provider]" value="' . esc_attr( $val ) . '" ' . checked( $s['provider'], $val, false ) . ' /> ' . esc_html( $label ) . '</label>';
		}
		echo '</td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Site key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[site_key]" value="' . esc_attr( $s['site_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Public key from the provider console.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Secret key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[secret_key]" value="' . esc_attr( $s['secret_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Private key used server-side to verify tokens. Leave empty and keep the module off if another captcha plugin already protects forms.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'reCAPTCHA v3 min score', 'tso-login-organizer' ) . '</th><td><input type="number" name="mod[score]" value="' . esc_attr( (string) $s['score'] ) . '" min="0" max="1" step="0.05" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Only used with v3. 0.0 is permissive; 1.0 is strict. Default 0.5.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'fields' === $module ) {
		tsolo_admin_render_fields_settings();
	} elseif ( 'moderation' === $module ) {
		$s = TSOLO_Mod_Moderation::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . esc_html__( 'Email confirmation', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[email_confirm]" value="1" ' . checked( ! empty( $s['email_confirm'] ), true, false ) . ' /> ' . esc_html__( 'Require users to confirm their email before they can log in', 'tso-login-organizer' ) . '</label>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'This overrides Core → “Log users in after registration”. The new person stays logged out until they open the link in the email, even if that Core box is checked.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Admin approval', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[admin_approve]" value="1" ' . checked( ! empty( $s['admin_approve'] ), true, false ) . ' /> ' . esc_html__( 'New accounts stay pending until an administrator approves them', 'tso-login-organizer' ) . '</label>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'This also overrides Core → “Log users in after registration”. The new person stays logged out until an administrator approves them on the Users screen, even if that Core box is checked. If both moderation boxes are off, the Core option signs them in as usual.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'restrictions' === $module ) {
		$s = TSOLO_Mod_Restrictions::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Private site', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[private_site]" value="1" ' . checked( ! empty( $s['private_site'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __( 'Require login for all pages', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Guests are redirected to the login page. Login, register and password URLs stay public.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
		echo '<h3>' . esc_html__( 'Shortcodes', 'tso-login-organizer' ) . '</h3>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Paste a shortcode into the content of a post or a page. In the block editor, add a Shortcode block and paste the whole snippet, including the closing tag. In the classic editor, paste it in the text. The words between the opening tag and the closing tag are the part that is shown or hidden. Text outside the shortcode stays visible to everyone. These tags do nothing in a menu or in a widget unless that area runs shortcodes.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __( 'Example: a note only members see', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-logged-in]\n" . __( 'Welcome back. Your downloads are on this page.', 'tso-login-organizer' ) . "\n[/tsolo-logged-in]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Guests see nothing from that block. Logged-in users see the sentence.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __( 'Example: a login prompt only guests see', 'tso-login-organizer' ) ) ) . '</strong></p>';
		$login_example = tsolo_get_action_url( 'login' );
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-logged-out]\n" . sprintf( /* translators: %s: login URL */ __( 'Please log in at %s to read the rest of this page.', 'tso-login-organizer' ), $login_example ) . "\n[/tsolo-logged-out]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Logged-in users see nothing from that block. Guests see the sentence.', 'tso-login-organizer' ) ) ) . '</p>';
		echo '<p class="description tsolo-option-help"><strong>' . wp_kses_post( tsolo_admin_emphasize( __( 'Example: one role only', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<code class="tsolo-shortcode-example">' . esc_html( "[tsolo-role role=\"subscriber\"]\n" . __( 'Subscriber offer: use the code WELCOME.', 'tso-login-organizer' ) . "\n[/tsolo-role]" ) . '</code>';
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Only users with the Subscriber role see that sentence. Use the role slug (subscriber, customer, editor), not the translated name. Several roles: role="subscriber,customer". Everyone else, including guests, sees nothing from that block.', 'tso-login-organizer' ) ) ) . '</p>';
	} elseif ( 'profiles' === $module ) {
		$s = TSOLO_Mod_Profiles::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Block wp-admin', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[block_admin]" value="1" ' . checked( ! empty( $s['block_admin'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __( 'Redirect users without edit_posts to the profile page', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Subscribers and similar roles cannot open /wp-admin/; they use the frontend profile instead.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'notifications' === $module ) {
		$s = TSOLO_Mod_Notifications::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . esc_html__( 'HTML emails', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[html]" value="1" ' . checked( ! empty( $s['html'] ), true, false ) . ' /> ' . esc_html__( 'Send notification emails as HTML', 'tso-login-organizer' ) . '</label></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'New user subject', 'tso-login-organizer' ) ) ) . '</th><td><input class="large-text" type="text" name="mod[new_user_subject]" value="' . esc_attr( $s['new_user_subject'] ) . '" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Subject line for the welcome / new-account email.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
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
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'API key', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[api_key]" value="' . esc_attr( $s['api_key'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Mailchimp API key ending with your data center (example: …-us21). Nothing is sent while the key or list ID is empty.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'List ID', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[list_id]" value="' . esc_attr( $s['list_id'] ) . '" autocomplete="off" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Audience / list ID where new subscribers are added.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Show checkbox', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[checkbox]" value="1" ' . checked( ! empty( $s['checkbox'] ), true, false ) . ' /> ' . esc_html__( 'Ask for newsletter consent on the registration form', 'tso-login-organizer' ) . '</label><p class="description tsolo-option-help">' . esc_html__( 'When this is off, every new registrant is subscribed automatically (only if API key and list ID are set).', 'tso-login-organizer' ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Checkbox checked by default', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[default_optin]" value="1" ' . checked( ! empty( $s['default_optin'] ), true, false ) . ' /> ' . esc_html__( 'Pre-check the newsletter box (only when the checkbox is shown)', 'tso-login-organizer' ) . '</label></td></tr>';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Checkbox label', 'tso-login-organizer' ) ) ) . '</th><td><input class="regular-text" type="text" name="mod[label]" value="' . esc_attr( $s['label'] ) . '" /><p class="description tsolo-option-help">' . esc_html__( 'Text shown next to the newsletter checkbox on the registration form.', 'tso-login-organizer' ) . '</p></td></tr>';
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
		echo '<div class="tsolo-howto"><p><strong>' . wp_kses_post( tsolo_admin_emphasize( __( 'What to type in the boxes', 'tso-login-organizer' ) ) ) . '</strong></p>';
		echo '<p>' . wp_kses_post( tsolo_admin_emphasize( __( 'Type a full address, including https:// and your domain. A path alone (/shop/) is rejected by the browser. Leave a box empty to keep the WordPress default for that case: usually the dashboard after login, the home page after logout, and the login page after registration.', 'tso-login-organizer' ) ) ) . '</p>';
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
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Max size (KB)', 'tso-login-organizer' ) ) ) . '</th><td><input type="number" name="mod[max_kb]" value="' . esc_attr( $s['max_kb'] ) . '" min="50" max="8192" /><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Maximum upload size for profile pictures stored under uploads/tso-login-organizer/ (50–8192 KB). Replacing a photo deletes the previous file in that folder.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'favorites' === $module ) {
		$s = TSOLO_Mod_Favorites::get_settings();
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">' . wp_kses_post( tsolo_admin_emphasize( __( 'Show on posts', 'tso-login-organizer' ) ) ) . '</th><td><label><input type="checkbox" name="mod[show_on_posts]" value="1" ' . checked( ! empty( $s['show_on_posts'] ), true, false ) . ' /> ' . wp_kses_post( tsolo_admin_emphasize( __( 'Show add/remove favorite controls on single posts', 'tso-login-organizer' ) ) ) . '</label><p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Logged-in users see a button at the end of each single post. The list page uses the Favorites URL slug from Core settings.', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
		echo '</table>';
	} elseif ( 'two_factor' === $module ) {
		$s     = TSOLO_Mod_Two_Factor::get_settings();
		$roles = wp_roles()->get_names();
		echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Tick roles that must use two-factor. Users without a key can still sign in once with the password and are sent to their profile to enable the authenticator. After enrollment, every login needs the password plus the app code (or a backup code).', 'tso-login-organizer' ) ) ) . '</p>';
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
/**
 * Custom Fields module settings UI.
 */
function tsolo_admin_render_fields_settings() {
	$s      = TSOLO_Mod_Fields::get_settings();
	$fields = isset( $s['fields'] ) && is_array( $s['fields'] ) ? $s['fields'] : array();
	$rows   = count( $fields );
	while ( $rows < 3 ) {
		$fields[] = array(
			'key'      => '',
			'label'    => '',
			'type'     => 'text',
			'required' => 0,
			'register' => 1,
			'profile'  => 1,
			'map'      => '',
		);
		++$rows;
	}
	echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Empty key rows are ignored. Use map to write into WordPress user fields; leave map empty for custom meta.', 'tso-login-organizer' ) ) ) . '</p>';
	echo '<table class="widefat striped tsolo-fields-table"><thead><tr>';
	echo '<th class="tsolo-fields-col-key">' . esc_html__( 'Key', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-label">' . esc_html__( 'Label', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-type">' . esc_html__( 'Type', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-map">' . esc_html__( 'Map', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-check">' . esc_html__( 'Required', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-check">' . esc_html__( 'Register', 'tso-login-organizer' ) . '</th>';
	echo '<th class="tsolo-fields-col-check">' . esc_html__( 'Profile', 'tso-login-organizer' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $fields as $i => $field ) {
		echo '<tr>';
		echo '<td class="tsolo-fields-col-key"><input class="regular-text" type="text" name="mod_fields[' . esc_attr( (string) $i ) . '][key]" value="' . esc_attr( $field['key'] ) . '" placeholder="phone" /></td>';
		echo '<td class="tsolo-fields-col-label"><input class="regular-text" type="text" name="mod_fields[' . esc_attr( (string) $i ) . '][label]" value="' . esc_attr( $field['label'] ) . '" /></td>';
		echo '<td class="tsolo-fields-col-type"><select name="mod_fields[' . esc_attr( (string) $i ) . '][type]">';
		foreach ( TSOLO_Mod_Fields::allowed_types() as $type ) {
			echo '<option value="' . esc_attr( $type ) . '" ' . selected( $field['type'], $type, false ) . '>' . esc_html( $type ) . '</option>';
		}
		echo '</select></td>';
		echo '<td class="tsolo-fields-col-map"><select name="mod_fields[' . esc_attr( (string) $i ) . '][map]">';
		$maps = array(
			''            => __( '— Custom meta —', 'tso-login-organizer' ),
			'first_name'  => __( 'First name', 'tso-login-organizer' ),
			'last_name'   => __( 'Last name', 'tso-login-organizer' ),
			'user_url'    => __( 'Website', 'tso-login-organizer' ),
			'description' => __( 'Biographical info', 'tso-login-organizer' ),
		);
		foreach ( $maps as $map => $label ) {
			echo '<option value="' . esc_attr( $map ) . '" ' . selected( $field['map'], $map, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></td>';
		echo '<td class="tsolo-fields-col-check"><input type="checkbox" name="mod_fields[' . esc_attr( (string) $i ) . '][required]" value="1" ' . checked( ! empty( $field['required'] ), true, false ) . ' /></td>';
		echo '<td class="tsolo-fields-col-check"><input type="checkbox" name="mod_fields[' . esc_attr( (string) $i ) . '][register]" value="1" ' . checked( ! empty( $field['register'] ), true, false ) . ' /></td>';
		echo '<td class="tsolo-fields-col-check"><input type="checkbox" name="mod_fields[' . esc_attr( (string) $i ) . '][profile]" value="1" ' . checked( ! empty( $field['profile'] ), true, false ) . ' /></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'To add more fields, fill the empty rows and save, then reopen this tab for new blank rows.', 'tso-login-organizer' ) . '</p>';
}

/**
 * Social Login settings + provider setup instructions.
 */
function tsolo_admin_render_social_settings() {
	$s = TSOLO_Mod_Social::get_settings();

	echo '<table class="form-table" role="presentation">';
	echo '<tr><th scope="row">' . esc_html__( 'Link existing accounts', 'tso-login-organizer' ) . '</th><td><label><input type="checkbox" name="mod[link_existing]" value="1" ' . checked( ! empty( $s['link_existing'] ), true, false ) . ' /> ' . esc_html__( 'If the social email matches an existing user, sign into that account (do not create a duplicate)', 'tso-login-organizer' ) . '</label>';
	echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'When off, matching emails show an error asking the person to log in first and connect the provider from their profile. Google linking also requires a verified email from Google. New social sign-ups still respect “Anyone can register”. Accounts with two-factor enabled cannot complete social login (password + app code required).', 'tso-login-organizer' ) ) ) . '</p></td></tr>';
	echo '</table>';

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
				__( 'Create a Project and App with OAuth 2.0 (confidential client) enabled. PKCE is handled automatically.', 'tso-login-organizer' ),
				__( 'Add the X callback URL below as a Callback URI / Redirect URL.', 'tso-login-organizer' ),
				__( 'Copy Client ID and Client Secret into the fields below, then Save.', 'tso-login-organizer' ),
				__( 'X usually does not return an email: a site-local account is created from the X user id and reused on later logins.', 'tso-login-organizer' ),
			),
			'id'            => 'x_client_id',
			'secret'        => 'x_client_secret',
			'id_label'      => __( 'X (Twitter) client ID', 'tso-login-organizer' ),
			'secret_label'  => __( 'X (Twitter) client secret', 'tso-login-organizer' ),
		),
	);

	echo '<p class="description tsolo-option-help">' . wp_kses_post( tsolo_admin_emphasize( __( 'Leave a provider empty to hide its button. After saving, test from the login page.', 'tso-login-organizer' ) ) ) . '</p>';

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
