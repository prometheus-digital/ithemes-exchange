<?php

/**
 * This class will build a form for an admin settings page.
 *
 * It can be extended as needed or simply invoked by calling it_exchange_print_admin_settings_form()
 * and passing the correct options to the constructor.
 * By default, it will look for POST data and attempt to save. If you need to do this on your own, you can disable
 * the save public functionality by passing save-on-load => false
 *
 * @since 1.3.1
 */
class IT_Exchange_Admin_Settings_Form {

	private static $html5 = array(
		'color',
		'date',
		'datetime',
		'datetime-local',
		'email',
		'month',
		'number',
		'range',
		'search',
		'tel',
		'time',
		'url',
		'week'
	);

	public $prefix = false;
	public $form_fields = array();
	public $form_options = array();
	public $field_values = array();
	public $button_options = array();
	public $saved_settings = array();
	public $settings;

	/** @var array|false */
	public $country_states_js = false;

	/** @public ITForm */
	public $form;

	/** @var array */
	private $show_if = array();

	/**
	 * Constructor Sets up the object
	 *
	 * @since 1.3.1
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {

		// Default Settings
		$defaults = array(
			'prefix'            => false,
			'form-fields'       => array(),
			'form-options'      => array(
				'id'      => false,
				'enctype' => false,
				'action'  => false,
			),
			'button-options'    => array(
				'save-button-label' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ),
				'save-button-class' => 'button button-primary',
			),
			'country-states-js' => false,
			'save-on-load'      => true,
		);

		// Merge defaults
		$options = ITUtility::merge_defaults( $args, $defaults );

		// If no prefix or form fields, return
		if ( empty( $options['prefix'] ) || empty( $options['form-fields'] ) ) {
			return;
		}

		// Set prefix and form fields
		$this->prefix      = $options['prefix'];
		$this->form_fields = $options['form-fields'];

		// Update settings if form was submitted
		if ( ! empty( $options['save-on-load'] ) ) {
			$this->save_settings();
		}

		// Set form options
		$this->form_options   = $options['form-options'];
		$this->button_options = $options['button-options'];

		// Set form options
		$this->set_form_options( $options['form-options'] );

		// Set form fields
		$this->set_form_fields( $options['form-fields'] );

		// Loads settings saved previously
		$this->load_settings();

		// Do we want to include the country states JS?
		$this->set_country_states_js( $options['country-states-js'] );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 *
	 * @param array $args
	 */
	public function IT_Exchange_Admin_Settings_Form( $args ) {

		self::__construct( $args );

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Checks the default form options and sets them if empty
	 *
	 * @since 1.3.1
	 *
	 * @param array $options the options for the HTML form tag
	 *
	 * @return void
	 */
	public function set_form_options( $options ) {

		// Validate Options
		$options['id']      = empty( $options['id'] ) ? 'it-exchange-' . $this->prefix : $options['id'];
		$options['action']  = empty( $options['action'] ) ? '' : $options['action'];
		$options['enctype'] = empty( $options['enctype'] ) ? '' : $options['enctype'];

		// Update property
		$this->form_options = $options;
	}

	/**
	 * Sets the form fields property
	 *
	 * @since 1.3.1
	 *
	 * @param array $fields
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set_form_fields( $fields ) {
		// Update property
		$this->form_fields = $fields;

		foreach ( $fields as $field ) {
			if ( isset( $field['show_if'] ) ) {

				$show_if = $field['show_if'];

				if ( ! is_array( $show_if ) ) {
					throw new InvalidArgumentException( "`show_if` must be an array for {$field['slug']}." );
				}

				if ( ! isset( $show_if['field'], $show_if['compare'], $show_if['value'] ) ) {
					throw new InvalidArgumentException( "Invalid `show_if` value for {$field['slug']}." );
				}

				$this->show_if[ $field['slug'] ] = $show_if;
			}

			if ( $field['type'] === 'file_upload' ) {
				$this->form_options['enctype'] = 'multipart/form-data';
			}
		}
	}

	/**
	 * Grabs existing settings and loads them in the object property
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function load_settings() {

		if ( ! has_filter('it_storage_get_defaults_exchange_' . $this->prefix ) ) {
			add_filter( 'it_storage_get_defaults_exchange_' . $this->prefix, array( $this, 'get_default_settings' ) );
		}

		$settings       = it_exchange_get_option( $this->prefix, true );
		$this->settings = apply_filters( 'it_exchange_load_admin_form_settings_for_' . $this->prefix, $settings );
	}

	/**
	 * Gives the default settings to the ITStorage API
	 *
	 * @since 1.3.1
	 *
	 * @param  array $options
	 *
	 * @return array
	 */
	public function get_default_settings( $options ) {
		foreach ( (array) $this->form_fields as $field ) {
			$options[ $field['slug'] ] = empty( $field['default'] ) ? '' : $field['default'];
		}

		return $options;
	}

	/**
	 * Print the form
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function print_form() {
		$this->print_messages();
		$this->set_field_values();
		$this->init_form();
		$this->start_form();
		$this->print_fields();
		$this->print_actions();
		$this->end_form();
	}

	/**
	 * Sets form field values for this page load
	 *
	 * Uses POST data, Previously saved settings, Defaults
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function set_field_values() {
		$this->field_values = ! it_exchange_has_messages( 'error' ) ? $this->settings : ITForm::get_post_data();
	}

	/**
	 * Init the form
	 *
	 * @return void
	 */
	public function init_form() {
		// Init the form
		$this->form = new ITForm( $this->field_values, array( 'prefix' => $this->prefix ) );
	}

	/**
	 * Start the form
	 *
	 * Prints the opening form HTML tag
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function start_form() {
		$this->form->start_form( $this->form_options, $this->prefix );
	}

	/**
	 * Prints the messages if they are present
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function print_messages() {
		// Print errors if they exist
		if ( it_exchange_has_messages( 'error' ) ) {
			foreach ( it_exchange_get_messages( 'error' ) as $message ) {
				ITUtility::show_error_message( $message );
			}
		}

		// Print notices if they exist
		if ( it_exchange_has_messages( 'notice' ) ) {
			foreach ( it_exchange_get_messages( 'notice' ) as $message ) {
				ITUtility::show_status_message( $message );
			}
		}
	}

	/**
	 * Prints the form fields
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function print_fields() {
		$i = 0;
		?>
		<?php do_action( 'it_exchange_' . $this->prefix . '_top' ); ?>
		<fieldset class="it-exchange-addon-settings">
			<?php
			foreach ( $this->form_fields as $row => $field ) {
				$field['options'] = empty( $field['options'] ) ? array() : $field['options'];
				if ( 'heading' === $field['type'] ) {
					if ( $i === 0 ) {
						echo '<legend>' . $field['label'] . '</legend>';
					} else {
						$this->print_heading_row( $field );
					}
				} elseif ( 'html' === $field['type'] ) {
					$this->print_html_row( $field );
				} else {

					if ( in_array( $field['type'], self::$html5, true ) ) {
						$form_method              = '_get_simple_input';
						$field['options']['type'] = $field['type'];
					} else {
						$form_method = 'get_' . $field['type'];
					}

					// Allow forms to override this by providing a callback public function
					if ( ! empty( $field['print_setting_field_override'] ) && is_callable( $field['print_setting_field_override'] ) ) {
						// Force ITForm to include this input name in saveable inputs
						$this->form->_used_inputs[''][] = $this->prefix . '-' . $field['slug'];
						call_user_func( $field['print_setting_field_override'], $this->field_values );
					} else if ( is_callable( array( $this->form, $form_method ) ) ) {
						$this->print_setting_row( $field, $form_method );
					} else {
						$this->print_uncallable_method_row( $field );
					}
				}

				$i ++;
			}
			// Add a hidden field to identify this form
			$this->form->add_hidden( 'it-exchange-saving-settings', true );
			?>
			<?php do_action( 'it_exchange_' . $this->prefix . '_bottom' ); ?>
		</fieldset>
		<?php

		// Include Country State JS if needed
		if ( is_array( $this->country_states_js ) ) {
			$this->print_country_states_js();
		}

		$this->generate_show_if_js();
	}

	/**
	 * Prints the form actions
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function print_actions() {
		?>
		<p class="submit">
			<input type="submit" value="<?php esc_attr_e( $this->button_options['save-button-label'] ); ?>" class="<?php esc_attr_e( $this->button_options['save-button-class'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * Prints the close of the form
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function end_form() {
		$this->form->end_form();
	}

	/**
	 * Print a heading row.
	 *
	 * @since 1.3.1
	 *
	 * @param array $heading
	 */
	public function print_heading_row( $heading ) {
		?>
		<h2><?php echo $heading['label']; ?></h2>
		<?php
	}

	/**
	 * Print a preamble row.
	 *
	 * @since 1.36.0
	 *
	 * @param array $preamble
	 */
	protected function print_html_row( $preamble ) {
		echo $preamble['html'];
	}

	/**
	 * Prints a table row with the setting
	 *
	 * @since 1.3.1
	 *
	 * @param array  $setting
	 * @param string $form_method
	 *
	 * @return void
	 */
	public function print_setting_row( $setting, $form_method ) {

		if ( $setting['type'] === 'drop_down' && ! isset( $setting['options']['value'] ) ) {
			$setting['options']['value'] = $setting['options'];
		}

		$id = $setting['slug'] . '-' . $this->prefix;
		?>

		<div class="<?php echo $this->should_show_field( $setting['slug'] ) ? '' : 'hidden'; ?>" id="<?php echo $id . '-container'; ?>">
		<label for="<?php echo esc_attr( $setting['slug'] . '-' . $this->prefix ); ?>">
			<?php echo $setting['label']; ?>

			<?php if ( ! empty( $setting['tooltip'] ) ): ?>
				<span class="tip" title="<?php echo esc_attr( $setting['tooltip'] ); ?>">i</span>
			<?php endif; ?>
		</label>

		<?php
		echo empty( $setting['before'] ) ? '' : $setting['before'];
		echo $this->form->$form_method(
			$setting['slug'],
			ITUtility::merge_defaults( $setting['options'], array( 'id' => $id ) ),
			false
		);
		echo empty( $setting['after'] ) ? '' : $setting['after'];
		echo '</div>';
	}

	/**
	 * Prints a warning if the setting has an uncallable method
	 *
	 * @since 1.3.1
	 *
	 * @param array $setting
	 *
	 * @return void
	 */
	public function print_uncallable_method_row( $setting ) {
		?>

		<p>
			<strong><?php _e( 'Coding Error!', 'it-l10n-ithemes-exchange' ); ?></strong>
			<?php printf( __( 'The setting for %s has an incorrect type argument. No such method exists in the ITForm class', 'it-l10n-ithemes-exchange' ), $setting['slug'] ); ?>
		</p>

		<?php
	}

	/**
	 * Whether a field should be displayed based on the `show_if` rules.
	 *
	 * @since 1.36.0
	 *
	 * @param string $field
	 *
	 * @return bool
	 *
	 * @throws \UnexpectedValueException
	 */
	protected function should_show_field( $field ) {

		if ( ! isset( $this->show_if[ $field ] ) ) {
			return true;
		}

		$required_field = $this->show_if[ $field ]['field'];
		$compare        = $this->show_if[ $field ]['compare'];
		$required_value = $this->show_if[ $field ]['value'];
		$actual_value   = $this->settings[ $required_field ];

		switch ( $compare ) {
			case '=':
				return $actual_value == $required_value;
			case '!=':
				return $actual_value != $required_value;
			case '<':
				return $actual_value < $required_value;
			case '<=':
				return $actual_value <= $required_value;
			case '>':
				return $actual_value > $required_value;
			case '>=':
				return $actual_value >= $required_value;
		}

		throw new UnexpectedValueException( 'Invalid field operator.' );
	}

	/**
	 * Saves the settings via ITStorage
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function save_settings() {
		// Abandon if not processing
		if ( empty( $_POST['_wpnonce'] ) || empty( $_POST[ $this->prefix . '-it-exchange-saving-settings' ] ) ) {
			return;
		}

		// Log error if nonce wasn't set
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $this->prefix ) ) {
			it_exchange_add_message( 'error', __( 'Invalid security token. Please try again', 'it-l10n-ithemes-exchange' ) );

			return;
		}

		$values = ITForm::get_post_data();
		unset( $values['it-exchange-saving-settings'] );

		$values = apply_filters( 'it_exchange_save_admin_form_settings_for_' . $this->prefix, $values );
		$errors = apply_filters( 'it_exchange_validate_admin_form_settings_for_' . $this->prefix, null, $values );

		if ( is_wp_error( $errors ) ) {
			it_exchange_add_message( 'error', implode( ', ', $errors->get_error_messages() ) );

			return;
		}

		it_exchange_save_option( $this->prefix, $values );
		it_exchange_add_message( 'notice', __( 'Settings updated', 'it-l10n-ithemes-exchange' ) );
	}

	/**
	 * Set the country state js property
	 *
	 * @since 1.3.1
	 *
	 * @param  array $args args needed to pass to the jQuery plugin
	 *
	 * @return void
	 */
	public function set_country_states_js( $args ) {

		// Return false if we're missing any required vars
		if (
			empty( $args['country-id'] ) ||
			empty( $args['states-id'] ) ||
			empty( $args['states-wrapper'] )
		) {
			$this->country_states_js = false;
		} else {
			$this->country_states_js = $args;
			$url                     = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/country-states-sync.js' );
			wp_enqueue_script( 'it-exchange-country-states-sync', $url, array( 'jquery' ), false, true );
		}
	}

	/**
	 * Prints the JS that binds the country state sync JS to the country field
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function print_country_states_js() {
		$country_id     = empty( $this->country_states_js['country-id'] ) ? '' : $this->country_states_js['country-id'];
		$states_id      = empty( $this->country_states_js['states-id'] ) ? '' : $this->country_states_js['states-id'];
		$states_wrapper = empty( $this->country_states_js['states-wrapper'] ) ? '' : $this->country_states_js['states-wrapper'];
		?>
		<script type="text/javascript">
			var itExchangeAjaxCountryStatesAjaxURL = '<?php echo esc_js( trailingslashit( get_home_url() ) ); ?>';
			jQuery( function () {
				jQuery( '#<?php echo esc_js( $country_id ); ?>' ).itCountryStatesSync(
					{
						stateWrapper: '<?php echo esc_js( $states_wrapper ); ?>',
						stateFieldID: '<?php echo esc_js( $states_id ); ?>',
						adminPrefix : '<?php echo esc_js( $this->prefix ); ?>'
					}
				).trigger( 'change' );
			} );
		</script>
		<?php
	}

	/**
	 * Generate show if JS.
	 *
	 * @since 1.36.0
	 */
	protected function generate_show_if_js() {

		?>

		<script type="text/javascript">

			jQuery(document).ready( function( $ ) {
				<?php foreach ( $this->show_if as $field => $show_if ):
					$id       = $field . '-' . $this->prefix;
				    $other_id = $show_if['field'] . '-' . $this->prefix;
				    $compare  = $show_if['compare'] === '=' ? '==' : $show_if['compare'];
					?>

					$("#<?php echo esc_js( $other_id ); ?>").change( function() {

						var thisVal,
						 requiredVal = <?php echo esc_js( $show_if['value'] ); ?>,
						 $container = $("#<?php echo esc_js( $id . '-container' ); ?>");

						if ( $( this ).is( 'input:checkbox' ) ) {
							thisVal = $( this ).is(':checked');
						} else {
							thisVal = $( this ).val();
						}

						if ( thisVal <?php echo $compare; ?> requiredVal ) {
							$container.removeClass( 'hidden' );
						} else {
							$container.addClass( 'hidden' );
						}
					} );
				<?php endforeach; ?>
			});
		</script>

		<?php
	}
}
