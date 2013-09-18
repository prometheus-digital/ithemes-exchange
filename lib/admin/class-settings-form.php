<?php
class IT_Exchange_Admin_Settings_Form {

	var $prefix         = false;
	var $form_fields    = array();
	var $form_options   = array();
	var $field_values   = array();
	var $button_options = array();
	var $saved_settings = array();

	/**
	 * Constructor Sets up the object
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function IT_Exchange_Admin_Settings_Form( $args ) {

		$defaults = array(
			'prefix'  => false,
			'form-fields'  => array(),
			'form-options' => array(
				'id'                => false,
				'enctype'           => false,
				'action'            => false,
			),
			'button-options' => array(
				'save-button-label' => __( 'Save Changes', 'LION' ),
				'save-button-class' => 'button button-primary',
			),
		);

		// Merge defaults
		$options = ITUtility::merge_defaults( $options, $defaults );

		// If no prefix or form fields, return false
		if ( empty( $args['prefix'] ) || empty( $args['fields'] ) )
			return false;

		// Set Object Properties
		$this->prefix         = $options['prefix'];
		$this->form_fields    = $options['form-fields'];
		$this->form_options   = $options['form-options'];
		$this->button_options = $options['button-options'];

		// Set form options
		$this->set_form_options( $options['form-options'] );

		// Set form fields 
		$this->set_form_fields( $options['form-fields'] );

		// Loads settings saved previously
		$this->load_settings();

		// Update settings if form was submitted
		$this->save_settings();
	}

	/**
	 * Checks the default form options and sets them if empty
	 *
	 * @since CHANGEME
	 *
	 * @param  array $form_options the options for the HTML form tag
	 * @return void
	*/
	function set_form_options( $options ) {

		// Validate Options
		$options['id']      = empty( $options['id'] ) ? 'it-exchange-' . $this->prefix;
		$options['action']  = empty( $options['action'] ? '' : $options['action'];
		$options['enctype'] = empty( $options['enctype'] ? '' : $options['enctype'];
		
		// Update property
		$this->form_options = $options;
	}

	/**
	 * Sets the form fields property
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_form_fields( $fields ) {
		// Update property
		$this->form_fields = $fields;
	}

	/**
	 * Grabs existing settings and loads them in the object property
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function load_settings() {
		$this->settings = it_exchange_get_option( $this->prefix, true );
	}

	/**
	 * Sets form field values for this page load
	 *
	 * Uses POST data, Previously saved settings, Defaults
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_field_values() {
		$this->form_values  = ! it_exchange_has_messages( 'error' ) ? $this->settings : ITForm::get_post_data();
	}

	/**
	 * Init the form
	 *
	 * @return void
	*/
	function init_form() {
		// Init the form
		$this->form = new ITForm( $this->form_values, array( $this->prefix ) );
	}

	/**
	 * Start the form
	 *
	 * Prints the opening form HTML tag
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function start_form() {
		$this->form->start_form( $this->form_options, $this->prefix );
	}

	/**
	 * Prints the form
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function print_form() {

		// Print errors if they exist
		if ( it_exchange_has_messages( 'error' ) ) {
			foreach( it_exchange_get_messages( 'error' ) as $message ) {
				ITUtility::show_error_message( $message );
			}
		}
		// Print notices if they exist
		if ( it_exchange_has_messages( 'notice' ) ) {
			foreach( it_exchange_get_messages( 'notice' ) as $message ) {
				ITUtility::show_status_message( $message );
			}
		}
		?>
		<table class="form-table">
			<?php do_action( 'it_exchange_' . $this->prefix . '_top' ); ?>
			<?php
			foreach( $this->fields as $row => $field ) {
				if ( 'heading' == $field['type'] ) {
					$this->print_heading_row( $field );
				} else {
					$form_method = 'add_' . $field['type'];
					if ( is_callable( array( $this->form, $form_method ) ) )
						$this->print_setting_row( $field, $form_method );
					else
						$this->print_uncallable_method_row( $field );
				}
			}
			$this->form->add_hidden( 'processing-' . $this->prefix, true );
			?>
			<?php do_action( 'it_exchange_' . $this->prefix . '_bottom' ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php esc_attr_e( $this->options['save-button-label'] ); ?>" class="<?php esc_attr_e( $this->options['save-button-class'] ); ?>" /></p>
		<?php
		$this->form->end_form();
	}

	function print_heading_row( $heading ) {
		?>
		<tr valign="top">
			<th scope="row"><strong><?php echo $heading['label']; ?></strong></th>
			<td></td>
		</tr>
		<?php
	}

	function print_setting_row( $setting, $form_method ) {
		?>
		<tr valign="top">
			<th scope="row"><label for="<?php esc_attr_e( $setting['slug'] ); ?>"><?php echo $setting['label']; ?></label></th>
			<td>
				<?php $this->form->$form_method( $setting['slug'], $setting['options'] ); ?>
			</td>
		</tr>
		<?php
	}

	function print_uncallable_method_row( $setting ) {
		?>
		<tr valign="top">
			<th scope="row" class="error"><strong><?php _e( 'Coding Error!', 'LION' ); ?></strong></th>
			<td><?php printf( __( 'The setting for %s has an incorrect type argument. No such method exists in the ITForm class', 'LION' ), $setting['slug'] ); ?></td>
		</tr>
		<?php
	}

	function save_settings() {

		// Abandon if not processing
		if ( empty( $_POST['_wpnonce'] ) || empty( $_POST['it-exchange-add-on-shipping-' . $this->provider->slug . '-processing-shipping-settings'] ) )
			return;

		// Log error if nonce wasn't set
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-shipping-settings-for-' . $this->provider->slug ) ) {
			it_exchange_add_message( 'error', 'Problem with nonce' );
			return;
		}

		$values = ITForm::get_post_data();
		unset( $values['processing-shipping-settings'] );

		$this->provider->update_settings( $values );
		it_exchange_add_message( 'notice', sprintf( __( '%s settings updated', 'LION' ), $this->provider->label ) );
	}
}
