<?php
/**
 * Registration moderation module.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Moderation
 */
class TSOLO_Mod_Moderation {

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
		add_action( 'user_register', array( $this, 'on_register' ), 5 );
		add_filter( 'wp_authenticate_user', array( $this, 'block_unapproved' ), 10, 2 );
		add_action( 'init', array( $this, 'maybe_confirm_email' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_approval' ) );
		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );
		add_filter( 'manage_users_columns', array( $this, 'users_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'users_column_content' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'admin_approved_notice' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'email_confirm' => 0,
			'admin_approve' => 0,
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$s                  = tsolo_get_option_array( TSOLO_OPT_MODERATION, self::defaults() );
		$s['email_confirm'] = empty( $s['email_confirm'] ) ? 0 : 1;
		$s['admin_approve'] = empty( $s['admin_approve'] ) ? 0 : 1;
		return $s;
	}

	/**
	 * Whether a new account must not be signed in yet.
	 *
	 * @return bool
	 */
	public static function blocks_auto_login() {
		if ( ! tsolo_is_module_enabled( 'moderation' ) ) {
			return false;
		}
		$settings = self::get_settings();
		return ! empty( $settings['email_confirm'] ) || ! empty( $settings['admin_approve'] );
	}

	/**
	 * Initial moderation status after registration.
	 *
	 * Email confirmation runs first when both options are on.
	 *
	 * @param array $settings Settings.
	 * @return string ''|pending_email|pending_admin
	 */
	public static function initial_status( $settings ) {
		if ( ! empty( $settings['email_confirm'] ) ) {
			return 'pending_email';
		}
		if ( ! empty( $settings['admin_approve'] ) ) {
			return 'pending_admin';
		}
		return '';
	}

	/**
	 * Status after a valid email confirmation click.
	 *
	 * @param array $settings Settings.
	 * @return string ''|pending_admin
	 */
	public static function status_after_email_confirm( $settings ) {
		return ! empty( $settings['admin_approve'] ) ? 'pending_admin' : '';
	}

	/**
	 * Whether login should be blocked for this moderation state.
	 *
	 * @param string $status          Moderation status meta.
	 * @param string $email_key       Remaining email confirm key (if any).
	 * @return string ''|pending_email|pending_admin Block reason code.
	 */
	public static function block_reason( $status, $email_key = '' ) {
		if ( '' !== (string) $email_key || 'pending_email' === $status ) {
			return 'pending_email';
		}
		if ( 'pending_admin' === $status ) {
			return 'pending_admin';
		}
		return '';
	}

	/**
	 * On register: set pending flags and optionally mail the confirm link.
	 *
	 * @param int $user_id User ID.
	 */
	public function on_register( $user_id ) {
		$user_id = absint( $user_id );
		if ( $user_id < 1 ) {
			return;
		}
		$s      = self::get_settings();
		$status = self::initial_status( $s );
		if ( '' === $status ) {
			return;
		}

		if ( ! empty( $s['email_confirm'] ) ) {
			$key = wp_generate_password( 20, false );
			update_user_meta( $user_id, 'tsolo_email_confirm_key', $key );
			$user = get_userdata( $user_id );
			if ( $user && ! empty( $user->user_email ) ) {
				$url = add_query_arg(
					array(
						'tsolo_confirm' => rawurlencode( $key ),
						'user'          => $user_id,
					),
					home_url( '/' )
				);
				wp_mail(
					$user->user_email,
					__( 'Confirm your email', 'tso-login-organizer' ),
					sprintf(
						/* translators: %s: confirmation URL */
						__( "Please confirm your email by visiting:\n\n%s", 'tso-login-organizer' ),
						$url
					)
				);
			}
		}

		update_user_meta( $user_id, 'tsolo_moderation_status', $status );
	}

	/**
	 * Block unapproved / unconfirmed users at login.
	 *
	 * @param WP_User|WP_Error $user     User.
	 * @param string           $password Password.
	 * @return WP_User|WP_Error
	 */
	public function block_unapproved( $user, $password ) {
		unset( $password );
		if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) || empty( $user->ID ) ) {
			return $user;
		}
		$status = (string) get_user_meta( $user->ID, 'tsolo_moderation_status', true );
		$key    = (string) get_user_meta( $user->ID, 'tsolo_email_confirm_key', true );
		$reason = self::block_reason( $status, $key );
		if ( 'pending_email' === $reason ) {
			return new WP_Error( 'tsolo_pending_email', __( '<strong>Error:</strong> Please confirm your email before logging in.', 'tso-login-organizer' ) );
		}
		if ( 'pending_admin' === $reason ) {
			return new WP_Error( 'tsolo_pending_admin', __( '<strong>Error:</strong> Your account is awaiting admin approval.', 'tso-login-organizer' ) );
		}
		return $user;
	}

	/**
	 * Apply a valid email confirmation (no redirect).
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Confirm key from the link.
	 * @return bool True when the key matched and meta was updated.
	 */
	public static function apply_email_confirm( $user_id, $key ) {
		$user_id = absint( $user_id );
		$key     = (string) $key;
		if ( $user_id < 1 || '' === $key ) {
			return false;
		}
		$stored = (string) get_user_meta( $user_id, 'tsolo_email_confirm_key', true );
		if ( '' === $stored || ! hash_equals( $stored, $key ) ) {
			return false;
		}
		delete_user_meta( $user_id, 'tsolo_email_confirm_key' );
		$next = self::status_after_email_confirm( self::get_settings() );
		if ( '' === $next ) {
			delete_user_meta( $user_id, 'tsolo_moderation_status' );
		} else {
			update_user_meta( $user_id, 'tsolo_moderation_status', $next );
		}
		return true;
	}

	/**
	 * Confirm email from link.
	 */
	public function maybe_confirm_email() {
		$key = tsolo_get_request_text( 'tsolo_confirm', 'get' );
		if ( '' === $key ) {
			return;
		}
		$user_id = absint( tsolo_get_request_text( 'user', 'get' ) );
		if ( ! self::apply_email_confirm( $user_id, $key ) ) {
			return;
		}
		wp_safe_redirect( add_query_arg( 'checkemail', 'confirm', tsolo_get_action_url( 'login' ) ) );
		exit;
	}

	/**
	 * Clear moderation gates for a user (admin override).
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function approve_user( $user_id ) {
		$user_id = absint( $user_id );
		if ( $user_id < 1 ) {
			return false;
		}
		delete_user_meta( $user_id, 'tsolo_moderation_status' );
		delete_user_meta( $user_id, 'tsolo_email_confirm_key' );
		return true;
	}

	/**
	 * Approve user from Users list action.
	 */
	public function handle_admin_approval() {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		$action  = tsolo_get_request_text( 'tsolo_mod_action', 'get' );
		$user_id = absint( tsolo_get_request_text( 'user_id', 'get' ) );
		$nonce   = tsolo_get_request_text( '_wpnonce', 'get' );
		if ( 'approve' !== $action || $user_id < 1 || ! wp_verify_nonce( $nonce, 'tsolo_approve_' . $user_id ) ) {
			return;
		}
		self::approve_user( $user_id );
		wp_safe_redirect( admin_url( 'users.php?tsolo_approved=1' ) );
		exit;
	}

	/**
	 * Add Approve link for pending users.
	 *
	 * @param array   $actions Actions.
	 * @param WP_User $user    User.
	 * @return array
	 */
	public function user_row_actions( $actions, $user ) {
		if ( ! current_user_can( 'edit_users' ) || ! ( $user instanceof WP_User ) ) {
			return $actions;
		}
		$status = (string) get_user_meta( $user->ID, 'tsolo_moderation_status', true );
		$key    = (string) get_user_meta( $user->ID, 'tsolo_email_confirm_key', true );
		if ( '' === self::block_reason( $status, $key ) ) {
			return $actions;
		}
		$url                      = wp_nonce_url(
			add_query_arg(
				array(
					'tsolo_mod_action' => 'approve',
					'user_id'          => (int) $user->ID,
				),
				admin_url( 'users.php' )
			),
			'tsolo_approve_' . $user->ID
		);
		$actions['tsolo_approve'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Approve', 'tso-login-organizer' ) . '</a>';
		return $actions;
	}

	/**
	 * Users list column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function users_column( $columns ) {
		$columns['tsolo_moderation'] = __( 'Moderation', 'tso-login-organizer' );
		return $columns;
	}

	/**
	 * Users list column content.
	 *
	 * @param string $output      Output.
	 * @param string $column_name Column.
	 * @param int    $user_id     User ID.
	 * @return string
	 */
	public function users_column_content( $output, $column_name, $user_id ) {
		if ( 'tsolo_moderation' !== $column_name ) {
			return $output;
		}
		$status = (string) get_user_meta( $user_id, 'tsolo_moderation_status', true );
		$key    = (string) get_user_meta( $user_id, 'tsolo_email_confirm_key', true );
		$reason = self::block_reason( $status, $key );
		if ( 'pending_email' === $reason ) {
			return esc_html__( 'Pending email', 'tso-login-organizer' );
		}
		if ( 'pending_admin' === $reason ) {
			return esc_html__( 'Pending approval', 'tso-login-organizer' );
		}
		return '—';
	}

	/**
	 * Notice after approving a user.
	 */
	public function admin_approved_notice() {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		if ( '1' !== tsolo_get_admin_query_arg( 'tsolo_approved' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && isset( $screen->id ) && 'users' !== $screen->id ) {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'User approved. They can log in now.', 'tso-login-organizer' ) . '</p></div>';
	}
}
