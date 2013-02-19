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
	 * @var string $_current_add_on_settings_page Current $_GET['add_on_settings'] value
	 * @since 0.3.6
	*/
	var $_current_add_on_settings_page;

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
		$this->_current_add_on_settings_page = empty( $_GET['add_on_settings'] ) ? false : $_GET['add_on_settings'];

		if ( $this->_is_admin && 'it-cart-buddy-addons' == $this->_current_page && 'manual-payments' == $this->_current_add_on_settings_page ) {
			add_action( 'it_cart_buddy_save_add_on_settings-manual-payments', array( $this, 'save_settings' ) );
		}
	}

	function print_settings_page() {
		ITUtility::print_r($this);
		$storage = new ITStorage2( 'it-cart-buddy-addon-manual-payments' );
		$form_values  = empty( $error_message ) ? $storage->load() : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_cart_buddy_add_on_manual_payments', 'it-cart-buddy-add-on-manual-payments-settings' ),
			'enctype' => apply_filters( 'it_cart_buddy_add_on_manual_payments_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-cart-buddy-addons&add_on_settings=manual-payments',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_cart_buddy_add_on_manual_payments' ) );

		if ( ! empty ( $status_message ) )
			ITUtility::show_status_message( $status_message );
		if ( ! empty( $error_message ) )
			ITUtility::show_error_message( $error_message );
		include( 'view-add-on-settings.php' );
	}

	/**
	 * Prints HTML options for default status
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function print_default_status_options( $current ) {
		$add_on = it_cart_buddy_get_add_on( 'manual-payments' );
		$options = empty( $add_on['options']['supports']['transaction_status']['options'] ) ? array() : $add_on['options']['supports']['transaction_status']['options'];
		foreach( $options as $slug => $label ) {
			echo '<option value="' . esc_attr( $slug ) . '"' . selected( $current, $slug, true ) . '>' . esc_attr( $label ) . '</option>';
		}
	}
}
