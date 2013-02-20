<?php
/**
 * Manual Payments Transaction Method
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

/**
 * Call back for settings page
 *
 * @since 0.3.6
 * @return void
*/
function it_cart_buddy_manual_payments_settings_callback() {
	$IT_Cart_Buddy_Manual_Payments_Add_On = new IT_Cart_Buddy_Manual_Payments_Add_On();
	$IT_Cart_Buddy_Manual_Payments_Add_On->print_settings_page();
}

/**
 * Class for Manual Payments
 * @since 0.3.6
*/
class IT_Cart_Buddy_Manual_Payments_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.3.6
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.3.6
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add_on_settings'] value
	 * @since 0.3.6
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.3.6
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.3.6
	*/
	var $error_message;

	/**
 	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.3.6
	 * @return void
	*/
	function IT_Cart_Buddy_Manual_Payments_Add_On() {
		$this->_is_admin                      = is_admin();
		$this->_current_page                 = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add_on_settings'] ) ? false : $_GET['add_on_settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-cart-buddy-addons' == $this->_current_page && 'manual-payments' == $this->_current_add_on ) {
			add_action( 'it_cart_buddy_save_add_on_settings-manual-payments', array( $this, 'save_settings' ) );
			do_action( 'it_cart_buddy_save_add_on_settings-manual-payments' );
		}

		add_filter( 'it_storage_get_defaults_cart-buddy-addon-manual-payments', array( $this, 'set_default_settings' ) );
	}

	function print_settings_page() {
		$settings = it_cart_buddy_get_options( 'cart-buddy-addon-manual-payments', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$default_status_options = $this->get_default_status_options();
		$form_options = array(
			'id'      => apply_filters( 'it_cart_buddy_add_on_manual_payments', 'it-cart-buddy-add-on-manual-payments-settings' ),
			'enctype' => apply_filters( 'it_cart_buddy_add_on_manual_payments_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-cart-buddy-addons&add_on_settings=manual-payments',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_cart_buddy_add_on_manual_payments' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'view-add-on-settings.php' );
	}

	/**
	 * Save settings
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function save_settings() {
		$defaults = it_cart_buddy_get_options( 'cart-buddy-addon-manual-payments' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		$errors = apply_filters( 'it_cart_buddy_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_cart_buddy_save_options( 'cart-buddy-addon-manual-payments', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function get_form_errors( $values ) {
		$errors = array();
		if ( empty( $values['manual_payments_title'] ) )
			$errors[] = __( 'The Title field cannot be left blank', 'LION' );
		if ( empty( $values['manual_payments_instructions'] ) )
			$errors[] = __( 'Please leave some instructions for customers checking out with this transaction method', 'LION' );

		$valid_status_options = $this->get_default_status_options();
		if ( empty( $values['manual_payments_default_status'] ) || empty( $valid_status_options[$values['manual_payments_default_status']] ) )
			$errors[] = __( 'Please select a valid default transaction status.', 'LION' );

		return $errors;
	}

	/**
	 * Prints HTML options for default status
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function get_default_status_options() {
		$add_on = it_cart_buddy_get_add_on( 'manual-payments' );
		$options = empty( $add_on['options']['supports']['transaction_status']['options'] ) ? array() : $add_on['options']['supports']['transaction_status']['options'];
		return $options;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 * @since 0.3.6
	 * @return array settings
	*/
	function set_default_settings( $defaults ) {
		$defaults['manual_payments_title']          = __( 'Pay with check', 'LION' );
		$defaults['manual_payments_instructions']   = __( 'Thank you for your order. We will contact you shortly for payment.', 'LION' );
		$defaults['manual_payments_default_status'] = 'pending';
		return $defaults;
	}
}
