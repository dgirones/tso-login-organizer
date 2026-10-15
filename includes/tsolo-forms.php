<?php
/**
 * Form registry and rendering.
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default forms.
 */
function tsolo_register_default_forms() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	tsolo_register_login_form();
	tsolo_register_registration_form();
	tsolo_register_lost_password_form();
	tsolo_register_password_reset_form();
}

/**
 * Forms store.
 *
 * @param array|null $set Optional full registry.
 * @return array
 */
function tsolo_forms_store( $set = null ) {
	static $forms = array();
	if ( null !== $set ) {
		$forms = $set;
	}
	return $forms;
}

/**
 * Get forms registry.
 *
 * @return array
 */
function tsolo_get_forms() {
	return tsolo_forms_store();
}

/**
 * Register a form.
 *
 * @param string $name Form name.
 * @param array  $args Args.
 */
function tsolo_register_form( $name, $args = array() ) {
	$name           = sanitize_key( $name );
	$args           = wp_parse_args(
		$args,
		array(
			'action'      => '',
			'method'      => 'post',
			'attributes'  => array(),
			'fields'      => array(),
			'render_args' => array(),
		)
	);
	$forms          = tsolo_forms_store();
	$forms[ $name ] = $args;
	tsolo_forms_store( $forms );
}

/**
 * Get a form.
 *
 * @param string $name Form name.
 * @return array|false
 */
function tsolo_get_form( $name = '' ) {
	if ( '' === $name ) {
		$action = tsolo_get_action();
		$name   = $action ? $action->get_name() : 'login';
	}
	$forms = tsolo_get_forms();
	return isset( $forms[ $name ] ) ? $forms[ $name ] : false;
}

/**
 * Add a form field.
 *
 * @param string $form  Form name.
 * @param string $field Field name.
 * @param array  $args  Field args.
 */
function tsolo_add_form_field( $form, $field, $args = array() ) {
	$form  = sanitize_key( $form );
	$field = sanitize_key( $field );
	$forms = tsolo_forms_store();
	if ( ! isset( $forms[ $form ] ) ) {
		return;
	}
	$args                               = wp_parse_args(
		$args,
		array(
			'type'       => 'text',
			'label'      => '',
			'value'      => '',
			'id'         => $field,
			'attributes' => array(),
			'priority'   => 10,
			'content'    => '',
		)
	);
	$forms[ $form ]['fields'][ $field ] = $args;
	tsolo_forms_store( $forms );
}

/**
 * Username label based on login type.
 *
 * @param string $context Context.
 * @return string
 */
function tsolo_get_username_label( $context = 'login' ) {
	unset( $context );
	$settings = tsolo_get_settings();
	if ( 'email' === $settings['login_type'] ) {
		return __( 'Email Address', 'tso-login-organizer' );
	}
	if ( 'username' === $settings['login_type'] ) {
		return __( 'Username', 'tso-login-organizer' );
	}
	return __( 'Username or Email Address', 'tso-login-organizer' );
}

/**
 * Register login form.
 */
function tsolo_register_login_form() {
	$settings = tsolo_get_settings();
	tsolo_register_form(
		'login',
		array(
			'action'      => tsolo_get_action_url( 'login' ),
			'attributes'  => array_filter(
				array(
					'data-ajax' => ! empty( $settings['ajax'] ) ? '1' : '',
				)
			),
			'render_args' => tsolo_get_request_text( 'checkemail', 'get' ) ? array(
				'show_form'  => false,
				'show_links' => false,
			) : array(),
		)
	);

	tsolo_add_form_field(
		'login',
		'log',
		array(
			'type'       => 'text',
			'label'      => tsolo_get_username_label( 'login' ),
			'value'      => tsolo_get_request_text( 'log', 'post' ),
			'id'         => 'user_login',
			'attributes' => array( 'autocapitalize' => 'off' ),
			'priority'   => 10,
		)
	);
	tsolo_add_form_field(
		'login',
		'pwd',
		array(
			'type'     => 'password',
			'label'    => __( 'Password', 'tso-login-organizer' ),
			'id'       => 'user_pass',
			'priority' => 15,
		)
	);
	tsolo_add_form_field(
		'login',
		'login_form',
		array(
			'type'     => 'action',
			'priority' => 20,
		)
	);
	tsolo_add_form_field(
		'login',
		'rememberme',
		array(
			'type'     => 'checkbox',
			'label'    => __( 'Remember Me', 'tso-login-organizer' ),
			'value'    => 'forever',
			'id'       => 'rememberme',
			'priority' => 25,
		)
	);
	tsolo_add_form_field(
		'login',
		'submit',
		array(
			'type'     => 'submit',
			'value'    => tsolo_get_form_text( 'login_button', __( 'Log In', 'tso-login-organizer' ) ),
			'priority' => 30,
		)
	);
	$redirect = tsolo_get_request_text( 'redirect_to' );
	tsolo_add_form_field(
		'login',
		'redirect_to',
		array(
			'type'     => 'hidden',
			'value'    => '' !== $redirect ? $redirect : admin_url(),
			'priority' => 30,
		)
	);
	tsolo_add_form_field(
		'login',
		'testcookie',
		array(
			'type'     => 'hidden',
			'value'    => '1',
			'priority' => 30,
		)
	);
}

/**
 * Register registration form.
 */
function tsolo_register_registration_form() {
	$settings = tsolo_get_settings();
	tsolo_register_form(
		'register',
		array(
			'action'     => tsolo_get_action_url( 'register' ),
			'attributes' => array_filter(
				array(
					'novalidate' => 'novalidate',
					'data-ajax'  => ! empty( $settings['ajax'] ) ? '1' : '',
				)
			),
		)
	);

	if ( 'email' === $settings['registration_type'] ) {
		tsolo_add_form_field(
			'register',
			'user_login',
			array(
				'type'     => 'hidden',
				'value'    => 'user' . md5( microtime() ),
				'id'       => 'user_login',
				'priority' => 10,
			)
		);
	} else {
		tsolo_add_form_field(
			'register',
			'user_login',
			array(
				'type'       => 'text',
				'label'      => __( 'Username', 'tso-login-organizer' ),
				'value'      => tsolo_get_request_text( 'user_login', 'post' ),
				'id'         => 'user_login',
				'attributes' => array( 'autocapitalize' => 'off' ),
				'priority'   => 10,
			)
		);
	}

	tsolo_add_form_field(
		'register',
		'user_email',
		array(
			'type'     => 'email',
			'label'    => __( 'Email', 'tso-login-organizer' ),
			'value'    => tsolo_get_request_text( 'user_email', 'post' ),
			'id'       => 'user_email',
			'priority' => 15,
		)
	);

	if ( ! empty( $settings['user_passwords'] ) ) {
		tsolo_add_form_field(
			'register',
			'user_pass1',
			array(
				'type'       => 'password',
				'label'      => __( 'Password', 'tso-login-organizer' ),
				'id'         => 'pass1',
				'attributes' => array(
					'autocomplete' => 'off',
					'name'         => 'pass1',
				),
				'priority'   => 20,
			)
		);
		tsolo_add_form_field(
			'register',
			'user_pass2',
			array(
				'type'       => 'password',
				'label'      => __( 'Confirm Password', 'tso-login-organizer' ),
				'id'         => 'pass2',
				'attributes' => array(
					'autocomplete' => 'off',
					'name'         => 'pass2',
				),
				'priority'   => 20,
			)
		);
		tsolo_add_form_field(
			'register',
			'indicator',
			array(
				'type'     => 'custom',
				'content'  => '<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite">' . esc_html__( 'Strength indicator', 'tso-login-organizer' ) . '</div>',
				'priority' => 20,
			)
		);
	} else {
		$passmail = tsolo_get_form_text( 'reg_passmail', __( 'Registration confirmation will be emailed to you.', 'tso-login-organizer' ) );
		tsolo_add_form_field(
			'register',
			'reg_passmail',
			array(
				'type'     => 'custom',
				'content'  => '<p class="tsolo-reg-passmail">' . esc_html( $passmail ) . '</p>',
				'priority' => 20,
			)
		);
	}

	tsolo_add_form_field(
		'register',
		'register_form',
		array(
			'type'     => 'action',
			'priority' => 25,
		)
	);
	tsolo_add_form_field(
		'register',
		'submit',
		array(
			'type'     => 'submit',
			'value'    => tsolo_get_form_text( 'register_button', __( 'Register', 'tso-login-organizer' ) ),
			'priority' => 30,
		)
	);
	$redirect = tsolo_get_request_text( 'redirect_to' );
	tsolo_add_form_field(
		'register',
		'redirect_to',
		array(
			'type'     => 'hidden',
			'value'    => $redirect,
			'priority' => 30,
		)
	);
}

/**
 * Lost password form.
 */
function tsolo_register_lost_password_form() {
	$settings = tsolo_get_settings();
	tsolo_register_form(
		'lostpassword',
		array(
			'action'     => tsolo_get_action_url( 'lostpassword' ),
			'attributes' => array_filter(
				array(
					'data-ajax' => ! empty( $settings['ajax'] ) ? '1' : '',
				)
			),
		)
	);
	tsolo_add_form_field(
		'lostpassword',
		'user_login',
		array(
			'type'     => 'text',
			'label'    => __( 'Username or Email Address', 'tso-login-organizer' ),
			'value'    => tsolo_get_request_text( 'user_login', 'post' ),
			'id'       => 'user_login',
			'priority' => 10,
		)
	);
	tsolo_add_form_field(
		'lostpassword',
		'lostpassword_form',
		array(
			'type'     => 'action',
			'priority' => 20,
		)
	);
	tsolo_add_form_field(
		'lostpassword',
		'submit',
		array(
			'type'     => 'submit',
			'value'    => tsolo_get_form_text( 'lostpassword_button', __( 'Get New Password', 'tso-login-organizer' ) ),
			'priority' => 30,
		)
	);
}

/**
 * Reset password form.
 */
function tsolo_register_password_reset_form() {
	tsolo_register_form(
		'resetpass',
		array(
			'action' => tsolo_get_action_url( 'resetpass' ),
		)
	);
	tsolo_add_form_field(
		'resetpass',
		'pass1',
		array(
			'type'       => 'password',
			'label'      => __( 'New Password', 'tso-login-organizer' ),
			'id'         => 'pass1',
			'attributes' => array( 'autocomplete' => 'new-password' ),
			'priority'   => 10,
		)
	);
	tsolo_add_form_field(
		'resetpass',
		'pass2',
		array(
			'type'       => 'password',
			'label'      => __( 'Confirm Password', 'tso-login-organizer' ),
			'id'         => 'pass2',
			'attributes' => array( 'autocomplete' => 'new-password' ),
			'priority'   => 15,
		)
	);
	tsolo_add_form_field(
		'resetpass',
		'indicator',
		array(
			'type'     => 'custom',
			'content'  => '<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite">' . esc_html__( 'Strength indicator', 'tso-login-organizer' ) . '</div>',
			'priority' => 20,
		)
	);
	tsolo_add_form_field(
		'resetpass',
		'submit',
		array(
			'type'     => 'submit',
			'value'    => tsolo_get_form_text( 'resetpass_button', __( 'Save Password', 'tso-login-organizer' ) ),
			'priority' => 30,
		)
	);
}

/**
 * Render a form by name.
 *
 * @param string $name Form name.
 * @param array  $args Render args.
 * @return string
 */
function tsolo_render_form( $name, $args = array() ) {
	$form = tsolo_get_form( $name );
	if ( ! $form ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		wp_parse_args(
			$form['render_args'],
			array(
				'show_form'  => true,
				'show_links' => true,
			)
		)
	);

	$settings    = tsolo_get_settings();
	$branding    = isset( $settings['branding'] ) && is_array( $settings['branding'] ) ? $settings['branding'] : array();
	$extra       = ! empty( $branding['extra_class'] ) ? ' ' . $branding['extra_class'] : '';
	$heading_key = $name . '_heading';
	$heading     = tsolo_get_form_text( $heading_key, '' );

	ob_start();
	echo '<div class="tsolo tsolo-' . esc_attr( $name ) . esc_attr( $extra ) . '">';
	if ( ! empty( $branding['logo_url'] ) ) {
		echo '<p class="tsolo-brand-logo"><img src="' . esc_url( $branding['logo_url'] ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" /></p>';
	}
	if ( '' !== $heading ) {
		echo '<h2 class="tsolo-form-heading">' . esc_html( $heading ) . '</h2>';
	}
	echo tsolo_render_errors_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside helper.

	if ( ! empty( $args['show_form'] ) ) {
		$attrs = '';
		foreach ( (array) $form['attributes'] as $attr => $val ) {
			if ( '' === $val || null === $val ) {
				continue;
			}
			$attrs .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $val ) . '"';
		}
		echo '<form name="' . esc_attr( $name ) . '" class="tsolo-form" action="' . esc_url( $form['action'] ) . '" method="' . esc_attr( $form['method'] ) . '"' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attrs built with esc_attr.
		do_action( 'tsolo_before_form', $name );

		// Re-read after before_form so modules can inject fields.
		$fresh  = tsolo_get_form( $name );
		$fields = ( is_array( $fresh ) && isset( $fresh['fields'] ) ) ? $fresh['fields'] : $form['fields'];
		uasort(
			$fields,
			function ( $a, $b ) {
				return (int) $a['priority'] - (int) $b['priority'];
			}
		);

		foreach ( $fields as $field_name => $field ) {
			echo tsolo_render_form_field( $name, $field_name, $field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in renderer.
		}

		do_action( 'tsolo_after_form', $name );
		echo '</form>';
	}

	if ( ! empty( $args['show_links'] ) ) {
		echo tsolo_render_form_links( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
	}
	echo '</div>';
	return (string) ob_get_clean();
}

/**
 * Render one field.
 *
 * @param string $form_name Form name.
 * @param string $field_name Field name.
 * @param array  $field Field args.
 * @return string
 */
function tsolo_render_form_field( $form_name, $field_name, $field ) {
	$type = $field['type'];
	if ( 'action' === $type ) {
		ob_start();
		if ( 'login_form' === $field_name ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core login_form action.
			do_action( 'login_form' );
		} elseif ( 'register_form' === $field_name ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core register_form action.
			do_action( 'register_form' );
		} elseif ( 'lostpassword_form' === $field_name ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core lostpassword_form action.
			do_action( 'lostpassword_form' );
		}
		return (string) ob_get_clean();
	}
	if ( 'custom' === $type ) {
		return '<div class="tsolo-field tsolo-field-' . esc_attr( $field_name ) . '">' . $field['content'] . '</div>'; // Content is plugin-built HTML; escaped at creation for static strings.
	}

	$name_attr = isset( $field['attributes']['name'] ) ? $field['attributes']['name'] : $field_name;
	$id        = ! empty( $field['id'] ) ? $field['id'] : $field_name;
	$value     = isset( $field['value'] ) ? $field['value'] : '';
	$extra     = '';
	foreach ( (array) $field['attributes'] as $attr => $val ) {
		if ( 'name' === $attr ) {
			continue;
		}
		$extra .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $val ) . '"';
	}

	$html = '<p class="tsolo-field tsolo-field-' . esc_attr( $field_name ) . '">';
	if ( 'checkbox' === $type ) {
		$html .= '<label for="' . esc_attr( $id ) . '"><input type="checkbox" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '"' . $extra . ' /> ' . esc_html( $field['label'] ) . '</label>';
	} elseif ( 'submit' === $type ) {
		$html .= '<button type="submit" name="wp-submit" id="wp-submit" class="tsolo-button">' . esc_html( $value ) . '</button>';
	} elseif ( 'hidden' === $type ) {
		$html = '<input type="hidden" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" />';
		return $html;
	} elseif ( 'textarea' === $type ) {
		if ( $field['label'] ) {
			$html .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		}
		$html .= '<textarea name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $id ) . '" class="input" rows="4"' . $extra . '>' . esc_textarea( $value ) . '</textarea>';
	} else {
		if ( $field['label'] ) {
			$html .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		}
		$input_type = in_array( $type, array( 'text', 'email', 'password', 'url', 'tel' ), true ) ? $type : 'text';
		$html      .= '<input type="' . esc_attr( $input_type ) . '" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" class="input"' . $extra . ' />';
	}
	$html .= '</p>';
	return $html;
}

/**
 * Form navigation links.
 *
 * @param string $current Current form.
 * @return string
 */
function tsolo_render_form_links( $current ) {
	$links = array();
	foreach ( tsolo_get_actions() as $action ) {
		$show = $action->show_on_forms;
		if ( ! $show || $action->get_name() === $current ) {
			continue;
		}
		$label   = is_string( $show ) ? $show : $action->get_title();
		$links[] = '<a href="' . esc_url( $action->get_url() ) . '">' . esc_html( $label ) . '</a>';
	}
	$links = apply_filters( 'tsolo_get_form_links', $links, $current );
	if ( empty( $links ) ) {
		return '';
	}
	$html = '<nav class="tsolo-links" aria-label="' . esc_attr__( 'Account', 'tso-login-organizer' ) . '"><ul>';
	foreach ( $links as $link ) {
		$html .= '<li>' . $link . '</li>';
	}
	$html .= '</ul></nav>';
	return $html;
}
