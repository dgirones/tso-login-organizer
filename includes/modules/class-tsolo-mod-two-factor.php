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
		add_action( 'login_form_tsolo_2fa', array( $this, 'render_2fa_form' ) );
		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
		add_action( 'tsolo_after_form', array( $this, 'maybe_inject_2fa_field' ) );
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
		return tsolo_get_option_array( TSOLO_OPT_TWO_FACTOR, self::defaults() );
	}

	/**
	 * Intercept successful password auth for 2FA.
	 *
	 * @param mixed  $user User.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return mixed
	 */
	public function intercept_login( $user, $username, $password ) {
		unset( $username, $password );
		if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
			return $user;
		}
		$secret = get_user_meta( $user->ID, 'tsolo_2fa_secret', true );
		$s      = self::get_settings();
		$need   = (bool) $secret;
		if ( ! $need && ! empty( $s['required_roles'] ) && is_array( $s['required_roles'] ) ) {
			foreach ( (array) $user->roles as $role ) {
				if ( in_array( $role, $s['required_roles'], true ) ) {
					$need = true;
					break;
				}
			}
		}
		if ( ! $need || ! $secret ) {
			return $user;
		}

		$code = tsolo_get_request_text( 'tsolo_2fa_code', 'post' );
		if ( '' === $code ) {
			return new WP_Error( 'tsolo_2fa_required', __( '<strong>Error:</strong> Enter your two-factor authentication code.', 'tso-login-organizer' ) );
		}
		if ( ! $this->verify_totp( $secret, $code ) && ! $this->verify_backup( $user->ID, $code ) ) {
			return new WP_Error( 'tsolo_2fa_invalid', __( '<strong>Error:</strong> Invalid two-factor code.', 'tso-login-organizer' ) );
		}
		return $user;
	}

	/**
	 * Inject 2FA field on login form.
	 *
	 * @param string $form Form name.
	 */
	public function maybe_inject_2fa_field( $form ) {
		if ( 'login' !== $form ) {
			return;
		}
		echo '<p class="tsolo-field tsolo-field-2fa"><label for="tsolo_2fa_code">' . esc_html__( 'Authentication code (if enabled)', 'tso-login-organizer' ) . '</label>';
		echo '<input type="text" name="tsolo_2fa_code" id="tsolo_2fa_code" class="input" autocomplete="one-time-code" /></p>';
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
		$secret  = get_user_meta( $user->ID, 'tsolo_2fa_secret', true );
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
	 */
	private static function apply_enable_flag( $user_id, $enable ) {
		if ( $enable ) {
			$secret = get_user_meta( $user_id, 'tsolo_2fa_secret', true );
			if ( ! $secret ) {
				$instance = self::get_instance();
				$secret   = $instance->generate_secret();
				update_user_meta( $user_id, 'tsolo_2fa_secret', $secret );
				$backups = array();
				for ( $i = 0; $i < 8; $i++ ) {
					$backups[] = strtolower( wp_generate_password( 8, false ) );
				}
				update_user_meta( $user_id, 'tsolo_2fa_backup', $backups );
			}
		} else {
			delete_user_meta( $user_id, 'tsolo_2fa_secret' );
			delete_user_meta( $user_id, 'tsolo_2fa_backup' );
		}
	}

	/**
	 * Generate base32-ish secret.
	 *
	 * @return string
	 */
	public function generate_secret() {
		$chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret = '';
		for ( $i = 0; $i < 16; $i++ ) {
			$secret .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		return $secret;
	}

	/**
	 * Verify TOTP code.
	 *
	 * @param string $secret Secret.
	 * @param string $code   Code.
	 * @return bool
	 */
	private function verify_totp( $secret, $code ) {
		$code = preg_replace( '/\s+/', '', $code );
		if ( ! preg_match( '/^\d{6}$/', $code ) ) {
			return false;
		}
		$time = floor( time() / 30 );
		for ( $i = -1; $i <= 1; $i++ ) {
			if ( hash_equals( $this->totp( $secret, $time + $i ), $code ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Compute TOTP.
	 *
	 * @param string $secret Secret.
	 * @param int    $time_slice Slice.
	 * @return string
	 */
	private function totp( $secret, $time_slice ) {
		$key       = $this->base32_decode( $secret );
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
	private function base32_decode( $secret ) {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret   = strtoupper( $secret );
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
	 * Verify backup code.
	 *
	 * @param int    $user_id User ID.
	 * @param string $code    Code.
	 * @return bool
	 */
	private function verify_backup( $user_id, $code ) {
		$codes = get_user_meta( $user_id, 'tsolo_2fa_backup', true );
		if ( ! is_array( $codes ) ) {
			return false;
		}
		$code = strtolower( trim( $code ) );
		$key  = array_search( $code, $codes, true );
		if ( false === $key ) {
			return false;
		}
		unset( $codes[ $key ] );
		update_user_meta( $user_id, 'tsolo_2fa_backup', array_values( $codes ) );
		return true;
	}
}
