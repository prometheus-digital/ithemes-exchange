<?php
/**
 * This file contains our functions / hooks for adding purchase requirements
 * to the Super Widget and the Checkout pages
*/

/**
 * Registers the shipping purchase requirements
 *
 * Use the it_exchange_register_purchase_requirement function to tell exchange
 * that your add-on requires certain conditionals to be set prior to purchase.
 * For more details see api/misc.php
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_addon_shipping_register_purchase_requirements() {
	// User must have a shipping address to purchase
	$properties = array(
		'requirement-met'        => 'it_exchange_addon_shipping_is_address_valid', // This is a PHP callback
		'sw-template-part'       => 'shipping',
		'checkout-template-part' => 'shipping-address',
		'notification'           => __( 'You must enter a shipping address before you can checkout', 'LION' ),
	);
	it_exchange_register_purchase_requirement( 'shipping-has-address', $properties );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_shipping_register_purchase_requirements' );

/**
 * Grabs the shipping address
 *
 * We'll check for one stored in the DB first. If it isn't there,
 * we'll grab look in the cart data
 *
 * @since 1.0.0
 *
 * @return array
*/
function it_exchange_addon_shipping_get_cart_address() {
	$address = it_exchange_get_cart_data('shipping-address');
	unset( $address['invalid'] );
	return  $address;
	return false;
}

/**
 * Is cart address valid?
 *
 * @since 1.0.0
 *
 * @return boolean
*/
function it_exchange_addon_shipping_is_address_valid() {
	$cart_address = it_exchange_get_cart_data('shipping-address');
	$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
	$customer_id  = it_exchange_get_current_customer_id();
	$customer_id  = empty( $customer_id ) ? $cart_customer : $customer_id;
	
	return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
}

/**
 * Process Adding the shipping address to the SW via ajax
 *
 * Processes the POST request. If data is good, it updates the DB (where we store the data)
 * permanantly as well as the session where we store it for the template part.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_addon_shipping_process_ajax_request() {
    // Parse data
    $name     = empty( $_POST['shippingName'] ) ? false : $_POST['shippingName'];
    $address1 = empty( $_POST['shippingAddress1'] ) ? false : $_POST['shippingAddress1'];
    $address2 = empty( $_POST['shippingAddress2'] ) ? false : $_POST['shippingAddress2'];
    $city     = empty( $_POST['shippingCity'] ) ? false : $_POST['shippingCity'];
    $state    = empty( $_POST['shippingState'] ) ? false : $_POST['shippingState'];
    $zip      = empty( $_POST['shippingZip'] ) ? false : $_POST['shippingZip'];
    $country  = empty( $_POST['shippingCountry'] ) ? false : $_POST['shippingCountry'];
	$customer = empty( $_POST['shippingCustomer'] ) ? false : $_POST['shippingCustomer'];
    $invalid  = ( ! $name || ! $address1 || ! $city || ! $state || ! $zip || ! $country || ! $customer );

    // Update object with what we have
    $address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'customer' );
    it_exchange_update_cart_data( 'shipping-address', $address );
	unset( $address['customer'] );

    // Register fail or success
    if ( $invalid ) {
        it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
        die('0');
    } else {
		it_exchange_addon_shipping_save_address( $address, $customer );
        die('1');
    }
}
add_action( 'it_exchange_processing_super_widget_ajax_update-shipping', 'it_exchange_addon_shipping_process_ajax_request' );

/**
 * Process Adding the shipping address to the checkout page via POST request
 *
 * Processes the POST request. If data is good, it updates the DB (where we store the data)
 * permanantly as well as the session where we store it for the template part.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_addon_shipping_process_update_address_request() {

	// Abandon if not processing
	if ( ! it_exchange_is_page( 'checkout' ) || empty( $_POST['it-exchange-shipping-add-address-from-checkout'] ) )
		return;

    // Parse data
    $name     = empty( $_POST['it-exchange-addon-shipping-name'] ) ? false : $_POST['it-exchange-addon-shipping-name'];
    $address1 = empty( $_POST['it-exchange-addon-shipping-address-1'] ) ? false : $_POST['it-exchange-addon-shipping-address-1'];
    $address2 = empty( $_POST['it-exchange-addon-shipping-address-2'] ) ? false : $_POST['it-exchange-addon-shipping-address-2'];
    $city     = empty( $_POST['it-exchange-addon-shipping-city'] ) ? false : $_POST['it-exchange-addon-shipping-city'];
    $state    = empty( $_POST['it-exchange-addon-shipping-state'] ) ? false : $_POST['it-exchange-addon-shipping-state'];
    $zip      = empty( $_POST['it-exchange-addon-shipping-zip'] ) ? false : $_POST['it-exchange-addon-shipping-zip'];
    $country  = empty( $_POST['it-exchange-addon-shipping-country'] ) ? false : $_POST['it-exchange-addon-shipping-country'];
    $invalid  = ( ! $name || ! $address1 || ! $city || ! $state || ! $zip || ! $country );

    // Update object with what we have
    $address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country' );
    it_exchange_update_cart_data( 'shipping-address', $address );

    // Register fail or success
    if ( $invalid ) {
        it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
    } else {
		it_exchange_addon_shipping_save_address( $address );
        it_exchange_add_message( 'notice', __( 'Shipping Address Updated' ) );
    }
}
add_action( 'template_redirect', 'it_exchange_addon_shipping_process_update_address_request' );

/**
 * Save the shipping address based on the User's ID
 *
 * @since 1.0.0
 *
 * @param array $address the shipping address as an array
 * @param int   $customer_id optional. if empty, will attempt to get he current user's ID
 * @return boolean Will fail if no user ID was provided or found
*/
function it_exchange_addon_shipping_save_address( $address, $customer_id=false ) {
	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
	if ( ! it_exchange_get_customer( $customer_id ) )
		return false;

	// Add to usermeta
	update_user_meta( $customer_id, 'it_exchange_shipping_address', $address );
}
