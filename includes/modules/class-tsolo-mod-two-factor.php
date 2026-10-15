<?php
/**
 * TOTP two-factor authentication module (opt-in).
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Two_Factor
 */
class TSOLO_Mod_Two_Factor {

	/**
	 * User meta: TOTP secret.
	 *
	 * @var string
	 */
	const META_SECRET = 'tsolo_2fa_secret';

	/**
	 * User meta: backup codes.
	 *
	 * @var string
	 */
	const META_BACKUP = 'tsolo_2fa_backup';

	/**
	 * Instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'authenticate', array( $this, 'intercept_login' ), 40, 3 );
		add_filter( 'login_redirect', array( $this, 'maybe_redirect_to_enroll' ), 30, 3 );
		add_action( 'login_form_tsolo_2fa', array( $this, 'render_2fa_form' ) );
		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
		add_action( 'tsolo_after_form', array( $this, 'maybe_inject_2fa_field' ) );
		add_action( 'admin_notices', array( $this, 'maybe_admin_enroll_notice' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'required_roles' => array(),
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_TWO_FACTOR, self::defaults() ) );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		$roles = isset( $raw['required_roles'] ) && is_array( $raw['required_roles'] ) ? $raw['required_roles'] : array();
		$out   = array();
		foreach ( $roles as $role ) {
			$role = sanitize_key( (string) $role );
			if ( '' !== $role ) {
				$out[] = $role;
			}
		}
		return array(
			'required_roles' => array_values( array_unique( $out ) ),
		);
	}

	/**
	 * Whether a role list requires 2FA for this user.
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	public static function user_role_requires_2fa( $user ) {
		if ( ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
			return false;
		}
		$s = self::get_settings();
		if ( empty( $s['required_roles'] ) ) {
			return false;
		}
		foreach ( (array) $user->roles as $role ) {
			if ( in_array( sanitize_key( (string) $role ), $s['required_roles'], true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether a user must present a TOTP/backup code at login (enrolled secret).
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	public static function user_needs_2fa( $user ) {
		if ( ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
			return false;
		}
		return (bool) get_user_meta( $user->ID, self::META_SECRET, true );
	}

	/**
	 * Whether the user must enroll 2FA (required role, no secret yet).
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	public static function user_must_enroll_2fa( $user ) {
		return self::user_role_requires_2fa( $user ) && ! self::user_needs_2fa( $user );
	}

	/**
	 * Intercept successful password auth for 2FA.
	 *
	 * @param mixed  $user     User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function intercept_login( $user, $username, $password ) {
		unset( $username, $password );
		if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
			return $user;
		}
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $user;
		}
		// Required role but not enrolled: allow password login so they can enable 2FA on profile.
		if ( self::user_must_enroll_2fa( $user ) ) {
			return $user;
		}
		if ( ! self::user_needs_2fa( $user ) ) {
			return $user;
		}
		$secret = get_user_meta( $user->ID, self::META_SECRET, true );
		$code   = tsolo_get_request_text( 'tsolo_2fa_code', 'post' );
		if ( '' === $code ) {
			return new WP_Error( 'tsolo_2fa_required', __( '<strong>Error:</strong> Enter your two-factor authentication code.', 'tso-login-organizer' ) );
		}
		if ( ! self::verify_totp( $secret, $code ) && ! self::verify_backup( $user->ID, $code ) ) {
			return new WP_Error( 'tsolo_2fa_invalid', __( '<strong>Error:</strong> Invalid two-factor code.', 'tso-login-organizer' ) );
		}
		return $user;
	}

	/**
	 * After login, send users who must enroll 2FA to their profile.
	 *
	 * @param string           $redirect_to           Redirect.
	 * @param string           $requested_redirect_to Requested.
	 * @param WP_User|WP_Error $user                  User.
	 * @return string
	 */
	public function maybe_redirect_to_enroll( $redirect_to, $requested_redirect_to, $user ) {
		unset( $requested_redirect_to );
		if ( ! ( $user instanceof WP_User ) || ! self::user_must_enroll_2fa( $user ) ) {
			return $redirect_to;
		}
		$url = admin_url( 'profile.php' );
		if ( function_exists( 'tsolo_get_action_url' ) && function_exists( 'tsolo_action_exists' ) && tsolo_action_exists( 'profile' ) ) {
			$url = tsolo_get_action_url( 'profile' );
		}
		return add_query_arg( 'tsolo_2fa_enroll', '1', $url );
	}

	/**
	 * Admin notice when redirected to enroll 2FA.
	 */
	public function maybe_admin_enroll_notice() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'profile' !== $screen->id ) {
			return;
		}
		if ( '1' !== tsolo_get_request_text( 'tsolo_2fa_enroll', 'get' ) ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! self::user_must_enroll_2fa( $user ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Two-factor authentication is required for your role. Enable it below before continuing.', 'tso-login-organizer' ) . '</p></div>';
	}

	/**
	 * Inject 2FA field on login form only when a code is required.
	 *
	 * @param string $form Form name.
	 */
	public function maybe_inject_2fa_field( $form ) {
		if ( 'login' !== $form ) {
			return;
		}
		$errors = function_exists( 'tsolo_get_errors' ) ? tsolo_get_errors() : null;
		$codes  = ( $errors instanceof WP_Error ) ? $errors->get_error_codes() : array();
		$need   = in_array( 'tsolo_2fa_required', $codes, true ) || in_array( 'tsolo_2fa_invalid', $codes, true );
		if ( ! $need && tsolo_request_post_is_set( 'tsolo_2fa_code' ) ) {
			$need = true;
		}
		if ( ! $need ) {
			return;
		}
		echo '<p class="tsolo-field tsolo-field-2fa"><label for="tsolo_2fa_code">' . esc_html__( 'Authentication code', 'tso-login-organizer' ) . '</label>';
		echo '<input type="text" name="tsolo_2fa_code" id="tsolo_2fa_code" class="input" autocomplete="one-time-code" inputmode="numeric" /></p>';
	}

	/**
	 * Placeholder for dedicated 2FA form action.
	 */
	public function render_2fa_form() {
		// Handled via login form field.
	}

	/**
	 * Profile fields for secret + backup codes.
	 *
	 * @param WP_User $user User.
	 */
	public function profile_fields( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}
		$secret  = get_user_meta( $user->ID, self::META_SECRET, true );
		$backups = get_user_meta( $user->ID, self::META_BACKUP, true );
		$enabled = (bool) $secret;
		?>
		<h2><?php esc_html_e( 'TSO Login Organizer 2FA', 'tso-login-organizer' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="tsolo_2fa_enable"><?php esc_html_e( 'Authenticator', 'tso-login-organizer' ); ?></label></th>
				<td>
					<label><input type="checkbox" name="tsolo_2fa_enable" id="tsolo_2fa_enable" value="1" <?php checked( $enabled ); ?> /> <?php esc_html_e( 'Require an app code at login (two-factor)', 'tso-login-organizer' ); ?></label>
					<?php if ( $secret ) : ?>
						<p class="description"><?php esc_html_e( 'Two-factor is already on. Saving with the box checked keeps this same key. Uncheck and update the profile to turn it off and delete the key.', 'tso-login-organizer' ); ?></p>
						<p class="description"><?php esc_html_e( 'Key to type into the app (Google Authenticator or similar):', 'tso-login-organizer' ); ?> <code><?php echo esc_html( $secret ); ?></code></p>
						<?php if ( is_array( $backups ) && $backups ) : ?>
							<p class="description"><?php esc_html_e( 'One-time backup codes (each works once):', 'tso-login-organizer' ); ?> <code><?php echo esc_html( implode( ' ', $backups ) ); ?></code></p>
						<?php endif; ?>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Check the box and update the profile to turn two-factor on. A key is created only then. The user must add that key to an authenticator app. The next login will ask for the password and for the 6-digit code from the app. Leaving the box unchecked changes nothing.', 'tso-login-organizer' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save 2FA profile fields (wp-admin).
	 *
	 * @param int $user_id User ID.
	 */
	public function save_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		check_admin_referer( 'update-user_' . $user_id );
		$enable = isset( $_POST['tsolo_2fa_enable'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified via check_admin_referer above.
		self::apply_enable_flag( $user_id, (bool) $enable );
	}

	/**
	 * Save 2FA from frontend profile (nonce already verified by Profiles).
	 *
	 * @param int $user_id User ID.
	 */
	public static function save_front_profile( $user_id ) {
		$enable = tsolo_request_post_is_set( 'tsolo_2fa_enable' );
		self::apply_enable_flag( (int) $user_id, $enable );
	}

	/**
	 * Enable or disable TOTP for a user.
	 *
	 * @param int  $user_id User ID.
	 * @param bool $enable  Enable.
	 * @return array{secret:string,backups:string[]} Current secret/backups after change.
	 */
	public static function apply_enable_flag( $user_id, $enable ) {
		$user_id = absint( $user_id );
		$secret  = '';
		$backups = array();
		if ( $enable ) {
			$secret = get_user_meta( $user_id, self::META_SECRET, true );
			if ( ! $secret ) {
				$secret  = self::generate_secret();
				$backups = self::generate_backup_codes( 8 );
				update_user_meta( $user_id, self::META_SECRET, $secret );
				update_user_meta( $user_id, self::META_BACKUP, $backups );
			} else {
				$backups = get_user_meta( $user_id, self::META_BACKUP, true );
				$backups = is_array( $backups ) ? $backups : array();
			}
		} else {
			delete_user_meta( $user_id, self::META_SECRET );
			delete_user_meta( $user_id, self::META_BACKUP );
		}
		return array(
			'secret'  => (string) $secret,
			'backups' => is_array( $backups ) ? $backups : array(),
		);
	}

	/**
	 * Generate base32 secret.
	 *
	 * @return string
	 */
	public static function generate_secret() {
		$chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret = '';
		for ( $i = 0; $i < 16; $i++ ) {
			$secret .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		return $secret;
	}

	/**
	 * Generate backup codes.
	 *
	 * @param int $count Count.
	 * @return string[]
	 */
	public static function generate_backup_codes( $count = 8 ) {
		$count   = max( 1, absint( $count ) );
		$backups = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$backups[] = strtolower( wp_generate_password( 8, false ) );
		}
		return $backups;
	}

	/**
	 * Verify TOTP code.
	 *
	 * @param string   $secret Secret.
	 * @param string   $code   Code.
	 * @param int|null $now    Unix time (tests).
	 * @return bool
	 */
	public static function verify_totp( $secret, $code, $now = null ) {
		$code = preg_replace( '/\s+/', '', (string) $code );
		if ( ! preg_match( '/^\d{6}$/', $code ) ) {
			return false;
		}
		$now  = null === $now ? time() : (int) $now;
		$time = (int) floor( $now / 30 );
		for ( $i = -1; $i <= 1; $i++ ) {
			if ( hash_equals( self::totp( $secret, $time + $i ), $code ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Compute TOTP.
	 *
	 * @param string $secret     Secret.
	 * @param int    $time_slice Slice.
	 * @return string
	 */
	public static function totp( $secret, $time_slice ) {
		$key       = self::base32_decode( $secret );
		$time      = pack( 'N*', 0, $time_slice );
		$hash      = hash_hmac( 'sha1', $time, $key, true );
		$offset    = ord( substr( $hash, -1 ) ) & 0x0F;
		$truncated = (
			( ( ord( $hash[ $offset ] ) & 0x7F ) << 24 ) |
			( ( ord( $hash[ $offset + 1 ] ) & 0xFF ) << 16 ) |
			( ( ord( $hash[ $offset + 2 ] ) & 0xFF ) << 8 ) |
			( ord( $hash[ $offset + 3 ] ) & 0xFF )
		) % 1000000;
		return str_pad( (string) $truncated, 6, '0', STR_PAD_LEFT );
	}

	/**
	 * Base32 decode.
	 *
	 * @param string $secret Secret.
	 * @return string
	 */
	public static function base32_decode( $secret ) {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret   = strtoupper( (string) $secret );
		$buffer   = 0;
		$bits     = 0;
		$out      = '';
		for ( $i = 0, $len = strlen( $secret ); $i < $len; $i++ ) {
			$val = strpos( $alphabet, $secret[ $i ] );
			if ( false === $val ) {
				continue;
			}
			$buffer = ( $buffer << 5 ) | $val;
			$bits  += 5;
			if ( $bits >= 8 ) {
				$bits -= 8;
				$out  .= chr( ( $buffer >> $bits ) & 0xFF );
			}
		}
		return $out;
	}

	/**
	 * Verify backup code (consumes it).
	 *
	 * @param int    $user_id User ID.
	 * @param string $code    Code.
	 * @return bool
	 */
	public static function verify_backup( $user_id, $code ) {
		$codes = get_user_meta( $user_id, self::META_BACKUP, true );
		if ( ! is_array( $codes ) ) {
			return false;
		}
		$code = strtolower( trim( (string) $code ) );
		$key  = array_search( $code, $codes, true );
		if ( false === $key ) {
			return false;
		}
		unset( $codes[ $key ] );
		update_user_meta( $user_id, self::META_BACKUP, array_values( $codes ) );
		return true;
	}
}
