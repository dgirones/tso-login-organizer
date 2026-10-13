<?php
/**
 * Login widget.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TSO Login Organizer widget.
 */
class TSOLO_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'tsolo_login_organizer',
			__( 'TSO Login Organizer', 'tso-login-organizer' ),
			array(
				'description' => __( 'Login, register or account links.', 'tso-login-organizer' ),
			)
		);
	}

	/**
	 * Register widget.
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}

	/**
	 * Front output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		if ( tsolo_is_action() && ! is_user_logged_in() ) {
			return;
		}
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$action = ! empty( $instance['action'] ) ? sanitize_key( $instance['action'] ) : 'login';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme widget wrapper.
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme title wrappers.
		}

		if ( is_user_logged_in() ) {
			echo tsolo_render_dashboard(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in renderer.
		} else {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
			echo tsolo_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in shortcode.
				array(
					'action'      => $action,
					'show_links'  => ! empty( $instance['show_links'] ) ? '1' : '0',
					'redirect_to' => $request_uri,
				)
			);
		}
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme widget wrapper.
	}

	/**
	 * Form.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : '';
		$action     = isset( $instance['action'] ) ? $instance['action'] : 'login';
		$show_links = isset( $instance['show_links'] ) ? (bool) $instance['show_links'] : true;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'tso-login-organizer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'action' ) ); ?>"><?php esc_html_e( 'Default form:', 'tso-login-organizer' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'action' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'action' ) ); ?>">
				<?php foreach ( tsolo_get_actions() as $obj ) : ?>
					<?php
					if ( ! $obj->show_in_widget ) {
						continue; }
					?>
					<option value="<?php echo esc_attr( $obj->get_name() ); ?>" <?php selected( $action, $obj->get_name() ); ?>><?php echo esc_html( $obj->get_title() ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_links' ) ); ?>" value="1" <?php checked( $show_links ); ?> /> <?php esc_html_e( 'Show form links', 'tso-login-organizer' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New.
	 * @param array $old_instance Old.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'      => isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '',
			'action'     => isset( $new_instance['action'] ) ? sanitize_key( $new_instance['action'] ) : 'login',
			'show_links' => ! empty( $new_instance['show_links'] ) ? 1 : 0,
		);
	}
}
