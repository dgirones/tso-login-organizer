<?php
/**
 * Custom registration / profile fields module (opt-in).
 *
 * @package TSO_Login_Organizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TSOLO_Mod_Fields
 */
class TSOLO_Mod_Fields {

	/**
	 * User meta prefix for unmapped fields.
	 *
	 * @var string
	 */
	const META_PREFIX = 'tsolo_cf_';

	/**
	 * Max custom fields.
	 *
	 * @var int
	 */
	const MAX_FIELDS = 20;

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
		add_action( 'tsolo_before_form', array( $this, 'inject_register_fields' ), 5 );
		add_filter( 'registration_errors', array( $this, 'validate_registration' ), 10, 3 );
		add_action( 'user_register', array( $this, 'save_registration' ), 20, 1 );
		add_action( 'tsolo_profile_fields', array( $this, 'render_profile_fields' ) );
		add_filter( 'tsolo_profile_validate', array( $this, 'validate_profile' ), 10, 2 );
		add_action( 'tsolo_profile_save', array( $this, 'save_profile' ), 10, 1 );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'fields' => array(),
		);
	}

	/**
	 * Allowed field types.
	 *
	 * @return string[]
	 */
	public static function allowed_types() {
		return array( 'text', 'textarea', 'email', 'url', 'tel', 'checkbox' );
	}

	/**
	 * WP user property maps.
	 *
	 * @return string[]
	 */
	public static function allowed_maps() {
		return array( '', 'first_name', 'last_name', 'description', 'user_url' );
	}

	/**
	 * Reserved keys (core form fields).
	 *
	 * @return string[]
	 */
	public static function reserved_keys() {
		return array(
			'user_login',
			'user_email',
			'user_pass',
			'user_pass1',
			'user_pass2',
			'pass1',
			'pass2',
			'log',
			'pwd',
			'rememberme',
			'redirect_to',
			'wp_submit',
			'submit',
			'display_name',
			'tsolo_profile_nonce',
			'tsolo_avatar',
			'tsolo_2fa_enable',
		);
	}

	/**
	 * Settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return self::sanitize_settings( tsolo_get_option_array( TSOLO_OPT_FIELDS, self::defaults() ) );
	}

	/**
	 * Sanitize one field definition.
	 *
	 * @param mixed $raw Raw row.
	 * @return array|null
	 */
	public static function sanitize_field( $raw ) {
		if ( ! is_array( $raw ) ) {
			return null;
		}
		$key = isset( $raw['key'] ) ? sanitize_key( (string) $raw['key'] ) : '';
		if ( '' === $key || in_array( $key, self::reserved_keys(), true ) || strlen( $key ) > 40 ) {
			return null;
		}
		$type = isset( $raw['type'] ) ? sanitize_key( (string) $raw['type'] ) : 'text';
		if ( ! in_array( $type, self::allowed_types(), true ) ) {
			$type = 'text';
		}
		$map = isset( $raw['map'] ) ? sanitize_key( (string) $raw['map'] ) : '';
		if ( ! in_array( $map, self::allowed_maps(), true ) ) {
			$map = '';
		}
		$label = isset( $raw['label'] ) ? sanitize_text_field( (string) $raw['label'] ) : '';
		if ( '' === $label ) {
			$label = $key;
		}
		return array(
			'key'      => $key,
			'label'    => $label,
			'type'     => $type,
			'required' => empty( $raw['required'] ) ? 0 : 1,
			'register' => empty( $raw['register'] ) ? 0 : 1,
			'profile'  => empty( $raw['profile'] ) ? 0 : 1,
			'map'      => $map,
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_settings( $raw ) {
		$out = self::defaults();
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		$rows = isset( $raw['fields'] ) && is_array( $raw['fields'] ) ? $raw['fields'] : array();
		$seen = array();
		foreach ( $rows as $row ) {
			$field = self::sanitize_field( $row );
			if ( ! $field ) {
				continue;
			}
			if ( isset( $seen[ $field['key'] ] ) ) {
				continue;
			}
			$seen[ $field['key'] ] = true;
			$out['fields'][]       = $field;
			if ( count( $out['fields'] ) >= self::MAX_FIELDS ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * Meta key for a field.
	 *
	 * @param array $field Field.
	 * @return string
	 */
	public static function meta_key( $field ) {
		if ( ! empty( $field['map'] ) ) {
			return (string) $field['map'];
		}
		return self::META_PREFIX . $field['key'];
	}

	/**
	 * Read stored value for a user.
	 *
	 * @param int   $user_id User ID.
	 * @param array $field   Field.
	 * @return string
	 */
	public static function get_user_value( $user_id, $field ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_array( $field ) ) {
			return '';
		}
		$map = isset( $field['map'] ) ? $field['map'] : '';
		if ( 'first_name' === $map || 'last_name' === $map || 'description' === $map ) {
			$val = get_user_meta( $user_id, $map, true );
			return is_string( $val ) ? $val : '';
		}
		if ( 'user_url' === $map ) {
			$user = get_userdata( $user_id );
			return ( $user instanceof WP_User ) ? (string) $user->user_url : '';
		}
		$val = get_user_meta( $user_id, self::meta_key( $field ), true );
		return is_string( $val ) || is_numeric( $val ) ? (string) $val : '';
	}

	/**
	 * Sanitize submitted value for a field type.
	 *
	 * @param array  $field Field.
	 * @param string $raw   Raw value.
	 * @return string
	 */
	public static function sanitize_value( $field, $raw ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';
		$raw  = (string) $raw;
		if ( 'checkbox' === $type ) {
			return ( '' !== $raw && '0' !== $raw ) ? '1' : '';
		}
		if ( 'textarea' === $type ) {
			return sanitize_textarea_field( $raw );
		}
		if ( 'email' === $type ) {
			return sanitize_email( $raw );
		}
		if ( 'url' === $type || 'user_url' === ( isset( $field['map'] ) ? $field['map'] : '' ) ) {
			$url = esc_url_raw( trim( $raw ) );
			return ( $url && preg_match( '#^https?://#i', $url ) ) ? $url : '';
		}
		return sanitize_text_field( $raw );
	}

	/**
	 * Persist value on user.
	 *
	 * @param int    $user_id User ID.
	 * @param array  $field   Field.
	 * @param string $value   Sanitized value.
	 */
	public static function save_user_value( $user_id, $field, $value ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_array( $field ) ) {
			return;
		}
		$map = isset( $field['map'] ) ? $field['map'] : '';
		if ( 'user_url' === $map ) {
			wp_update_user(
				array(
					'ID'       => $user_id,
					'user_url' => $value,
				)
			);
			return;
		}
		if ( 'first_name' === $map || 'last_name' === $map || 'description' === $map ) {
			update_user_meta( $user_id, $map, $value );
			return;
		}
		update_user_meta( $user_id, self::meta_key( $field ), $value );
	}

	/**
	 * Inject fields into register form registry.
	 *
	 * @param string $form_name Form name.
	 */
	public function inject_register_fields( $form_name ) {
		if ( 'register' !== $form_name ) {
			return;
		}
		$priority = 18;
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['register'] ) ) {
				continue;
			}
			$name = 'tsolo_cf_' . $field['key'];
			$type = $field['type'];
			$args = array(
				'type'     => ( 'textarea' === $type ) ? 'textarea' : ( ( 'checkbox' === $type ) ? 'checkbox' : 'text' ),
				'label'    => $field['label'],
				'value'    => tsolo_get_request_text( $name, 'post' ),
				'id'       => $name,
				'priority' => $priority,
			);
			if ( in_array( $type, array( 'email', 'url', 'tel' ), true ) ) {
				$args['type']                    = $type;
				$args['attributes']['inputmode'] = ( 'tel' === $type ) ? 'tel' : '';
			}
			if ( 'checkbox' === $type ) {
				$args['value'] = '1';
				if ( tsolo_request_post_is_set( $name ) ) {
					$args['attributes']['checked'] = 'checked';
				}
			}
			if ( ! empty( $field['required'] ) ) {
				$args['attributes']['required'] = 'required';
			}
			tsolo_add_form_field( 'register', $name, $args );
			++$priority;
		}
	}

	/**
	 * Validate registration extras.
	 *
	 * @param WP_Error $errors Errors.
	 * @param string   $login  Login.
	 * @param string   $email  Email.
	 * @return WP_Error
	 */
	public function validate_registration( $errors, $login, $email ) {
		unset( $login, $email );
		if ( ! ( $errors instanceof WP_Error ) ) {
			return $errors;
		}
		if ( is_admin() && ! ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
			return $errors;
		}
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['register'] ) ) {
				continue;
			}
			$name  = 'tsolo_cf_' . $field['key'];
			$raw   = tsolo_get_request_text( $name, 'post' );
			$value = self::sanitize_value( $field, $raw );
			if ( ! empty( $field['required'] ) && '' === $value ) {
				$errors->add(
					'tsolo_cf_' . $field['key'],
					sprintf(
						/* translators: %s: field label */
						__( '<strong>Error:</strong> %s is required.', 'tso-login-organizer' ),
						$field['label']
					)
				);
			}
			if ( 'email' === $field['type'] && '' !== $value && ! is_email( $value ) ) {
				$errors->add(
					'tsolo_cf_' . $field['key'],
					sprintf(
						/* translators: %s: field label */
						__( '<strong>Error:</strong> %s must be a valid email address.', 'tso-login-organizer' ),
						$field['label']
					)
				);
			}
		}
		return $errors;
	}

	/**
	 * Whether this request looks like a frontend registration POST (not admin/REST/social).
	 *
	 * @return bool
	 */
	public static function is_frontend_registration_request() {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( is_admin() && ! ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
			return false;
		}
		if ( ! tsolo_request_post_is_set( 'user_email' ) && ! tsolo_request_post_is_set( 'user_login' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Save registration field values.
	 *
	 * @param int $user_id User ID.
	 */
	public function save_registration( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! self::is_frontend_registration_request() ) {
			return;
		}
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['register'] ) ) {
				continue;
			}
			$name  = 'tsolo_cf_' . $field['key'];
			$value = self::sanitize_value( $field, tsolo_get_request_text( $name, 'post' ) );
			self::save_user_value( $user_id, $field, $value );
		}
	}

	/**
	 * Profile fields markup (echo).
	 */
	public function render_profile_fields() {
		$user_id = get_current_user_id();
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['profile'] ) ) {
				continue;
			}
			$name  = 'tsolo_cf_' . $field['key'];
			$value = self::get_user_value( $user_id, $field );
			$type  = $field['type'];
			echo '<p class="tsolo-field tsolo-field-cf">';
			if ( 'checkbox' === $type ) {
				echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="1" ' . checked( '1' === $value, true, false ) . ' /> ' . esc_html( $field['label'] ) . '</label>';
			} elseif ( 'textarea' === $type ) {
				echo '<label for="' . esc_attr( $name ) . '">' . esc_html( $field['label'] ) . '</label>';
				echo '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" class="input" rows="4">' . esc_textarea( $value ) . '</textarea>';
			} else {
				$input = in_array( $type, array( 'email', 'url', 'tel' ), true ) ? $type : 'text';
				echo '<label for="' . esc_attr( $name ) . '">' . esc_html( $field['label'] ) . '</label>';
				echo '<input type="' . esc_attr( $input ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="input" />';
			}
			echo '</p>';
		}
	}

	/**
	 * Validate profile custom fields before userdata is written.
	 *
	 * @param WP_Error|null $errors Existing errors.
	 * @param int           $user_id User ID.
	 * @return WP_Error|null
	 */
	public function validate_profile( $errors, $user_id ) {
		unset( $user_id );
		if ( ! ( $errors instanceof WP_Error ) ) {
			$errors = null;
		}
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['profile'] ) ) {
				continue;
			}
			$name  = 'tsolo_cf_' . $field['key'];
			$raw   = ( 'checkbox' === $field['type'] && ! tsolo_request_post_is_set( $name ) ) ? '' : tsolo_get_request_text( $name, 'post' );
			$value = self::sanitize_value( $field, $raw );
			if ( ! empty( $field['required'] ) && '' === $value ) {
				$err = new WP_Error(
					'tsolo_cf_' . $field['key'],
					sprintf(
						/* translators: %s: field label */
						__( '<strong>Error:</strong> %s is required.', 'tso-login-organizer' ),
						$field['label']
					)
				);
				if ( $errors instanceof WP_Error ) {
					$errors->merge_from( $err );
				} else {
					$errors = $err;
				}
			}
			if ( 'email' === $field['type'] && '' !== $value && ! is_email( $value ) ) {
				$err = new WP_Error(
					'tsolo_cf_' . $field['key'],
					sprintf(
						/* translators: %s: field label */
						__( '<strong>Error:</strong> %s must be a valid email address.', 'tso-login-organizer' ),
						$field['label']
					)
				);
				if ( $errors instanceof WP_Error ) {
					$errors->merge_from( $err );
				} else {
					$errors = $err;
				}
			}
		}
		return $errors;
	}

	/**
	 * Save profile extras (after validation passed).
	 *
	 * @param int $user_id User ID.
	 */
	public function save_profile( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return;
		}
		foreach ( self::get_settings()['fields'] as $field ) {
			if ( empty( $field['profile'] ) ) {
				continue;
			}
			$name  = 'tsolo_cf_' . $field['key'];
			$raw   = ( 'checkbox' === $field['type'] && ! tsolo_request_post_is_set( $name ) ) ? '' : tsolo_get_request_text( $name, 'post' );
			$value = self::sanitize_value( $field, $raw );
			self::save_user_value( $user_id, $field, $value );
		}
	}
}
