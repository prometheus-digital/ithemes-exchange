<?php
/**
 * Manual Payments Transaction Method
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

include( 'confirmation-template-functions.php' );

add_filter( 'it_cart_buddy_get_transaction_method_name-manual-payments', 'it_cart_buddy_get_manual_payments_name', 9 );
add_action( 'it_cart_buddy_do_transaction-manual-payments', 'it_cart_buddy_manual_payments_do_transaction', 9 );
add_filter( 'it_cart_buddy_possible_template_paths', 'it_cart_buddy_manual_payments_add_template_path' );

/**
 * Call back for settings page
 *
 * This is set in options array when registering the add-on and called from it_cart_buddy_enable_addon()
 *
 * @since 0.3.6
 * @return void
*/
function it_cart_buddy_manual_payments_settings_callback() {
	$IT_Cart_Buddy_Manual_Payments_Add_On = new IT_Cart_Buddy_Manual_Payments_Add_On();
	$IT_Cart_Buddy_Manual_Payments_Add_On->print_settings_page();
}

/**
 * Replace Manual Payments name with what is set in admin settings
 *
 * @since 0.3.7
 * @param string $name the name passed in from the WP filter API
 * @return string
*/
function it_cart_buddy_get_manual_payments_name( $name ) { 
    $options = it_cart_buddy_get_option( 'cart-buddy-addon-manual-payments' );
    if ( ! empty( $options['manual_payments_title'] ) ) 
        $name = $options['manual_payments_title'];

    return $name;
}

/**
 * Processes the transaction from the cart
 *
 * @since 0.3.7
*/
function it_cart_buddy_manual_payments_do_transaction( $cart_object ) {
	// Set transaction type as manual payment
	$args = array(
		'transaction-method' => 'manual-payments',
	);

	// Do transaction
	$transaction_id = it_cart_buddy_add_transaction( $args, $cart_object );
}

/**
 * Adds manual transactions template path inf on confirmation page
 *
 * @since 0.3.8
 * @return array of possible template paths + manual-payments template path
*/
function it_cart_buddy_manual_payments_add_template_path( $paths ) {
	if ( is_page( it_cart_buddy_get_page_id( 'transaction-confirmation' ) ) )
		$paths[] = dirname( __FILE__ ) . '/templates/';
	return $paths;
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
		$settings = it_cart_buddy_get_option( 'cart-buddy-addon-manual-payments', true );
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
		$defaults = it_cart_buddy_get_option( 'cart-buddy-addon-manual-payments' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cart-buddy-manual-payments-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_cart_buddy_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_cart_buddy_save_option( 'cart-buddy-addon-manual-payments', $new_values ) ) {
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
		$add_on = it_cart_buddy_get_addon( 'manual-payments' );
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
