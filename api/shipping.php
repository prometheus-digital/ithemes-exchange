<?php
/**
 * This file contains functions related to the shipping API
 * See also: api/shipping-features.php
 * @since CHANGEME
 * @package IT_Exchagne
*/

/**
 * Register a shipping provider
 *
 * @since CHANGEME
 *
 * @param  string  $slug    provider slug
 * @param  array   $options options for the provider
 * @return boolean 
*/
function it_exchange_register_shipping_provider( $slug, $options ) {

	// Lets just make sure the slug is in the options
	$options['slug'] = $slug;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['providers'][$slug] = $options;

	// Return the object
	return true;
}

/**
 * Returns all registered shipping providers
 *
 * @since CHANGEME
 *
 * @param  mixed $filtered a string or an array of strings to limit returned providers to specific providers
 * @return array
*/
function it_exchange_get_registered_shipping_providers( $filtered=array() ) {
	$providers = empty( $GLOBALS['it_exchange']['shipping']['providers'] ) ? array() : $GLOBALS['it_exchange']['shipping']['providers'];
	if ( empty( $filtered ) )
		return $providers;

	foreach( (array) $filtered as $provider ) {
		if ( isset( $providers[$provider] ) )
			unset( $providers[$provider] );
	}
	return $providers;
}

/**
 * Returns a specific registered shipping provider object
 *
 * @since CHANGEME
 *
 * @param  string $slug the registerd slug
 * @return mixed  false or object
*/
function it_exchange_get_registered_shipping_provider( $slug ) {
	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['providers'][$slug] ) )
		return false;

	// Retrieve the provider details
	$options = $GLOBALS['it_exchange']['shipping']['providers'][$slug];

	// Include the class
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping/class-provider.php' );

	// Init the class
	return new IT_Exchange_Shipping_Provider( $slug, $options );

	// Return false if no object was found
	return false;
}

/**
 * Is the requested shipping provider registered?
 *
 * @since CHANGEME
 *
 * @param  string  $slug the registerd slug
 * @return boolean
*/
function it_exchange_is_shipping_provider_registered( $slug ) {
	return (boolean) it_exchange_get_registered_shipping_provider( $slug );
}

/**
 * Register a shipping method
 *
 * @since CHANGEME
 *
 * @param string  $slug    method slug
 * @param array   $options options for the slug
 * @return boolean
*/
function it_exchange_register_shipping_method( $slug, $class ) {
	// Validate opitons
	if ( ! class_exists( $class ) )
		return false;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['methods'][$slug] = $class;

	// Return the object
	return true;
}

/**
 * Returns a specific registered shipping method object
 *
 * @since CHANGEME
 *
 * @param  string $slug the registerd slug
 * @return mixed  false or object
*/
function it_exchange_get_registered_shipping_method( $slug, $product_id=false ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) )
		return false;

	// Retrieve the method class 
	$class = $GLOBALS['it_exchange']['shipping']['methods'][$slug];

	// Make sure we have a class index and it corresponds to a defined class
	if ( empty( $class ) || ! class_exists( $class ) )
		return false;

	// Init the class
	return new $class( $product_id );

	// Return false if no object was found
	return false;
}

/**
 * Returns all registered shipping methods
 *
 * @since CHANGEME
 *
 * @param  mixed $filtered a string or an array of strings to limit returned methods to specific methods 
 * @return array
*/
function it_exchange_get_registered_shipping_methods( $filtered=array() ) {
	$methods = empty( $GLOBALS['it_exchange']['shipping']['methods'] ) ? array() : $GLOBALS['it_exchange']['shipping']['methods'];

	if ( empty( $filtered ) )
		return $methods;

	foreach( (array) $filtered as $method ) {
		if ( isset( $methods[$method] ) )
			unset( $methods[$method] );
	}
	return $methods;
}

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
function it_exchange_get_cart_shipping_address() {
	$address = it_exchange_get_cart_data('shipping-address');
	unset( $address['invalid'] );
	return  $address;
	return false;
}

/**
 * Save the shipping address based on the User's ID
 *
 * @since 1.0.0
 *
 * @param array $address the shipping address as an array
 * @param int   $customer_id optional. if empty, will attempt to get he current user's ID
 * @return boolean Will fail if no user ID was provided or found
*/
function it_exchange_save_shipping_address( $address, $customer_id=false ) {
	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
	if ( ! it_exchange_get_customer( $customer_id ) )
		return false;

	// Add to usermeta
	update_user_meta( $customer_id, 'it_exchange_shipping_address', $address );
}

/**
 * Get shipping for cart
 *
 * @since 1.0.0
 *
*/
function it_exchange_get_shipping_cost_for_cart( $format_price=true ) {

    // Grab the tax rate
    $options  = it_exchange_get_option( 'addon_shipping_general' );
    $tax_rate = empty( $options['default-tax-rate'] ) ? 1 : (float) $options['default-tax-rate'];
    $process_after_discounts = ! empty( $options['calculate-after-discounts'] );

    // Grab the cart subtotal or the cart total depending on the process after discounts option
    $cart_total = it_exchange_get_cart_subtotal( false );

    if ( $process_after_discounts )
        $cart_total -= it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );

    // Calculate shipping
    $cart_shipping = $cart_total * ( $tax_rate / 100 );

    $shipping = apply_filters( 'it_exchange_get_shipping_cost_for_cart', $cart_shipping );
    if ( $format_price )
        $shipping = it_exchange_format_price( $shipping );
    return $shipping;
}

/**
 * Returns the value of an address field for the address form.
 *
 * @since 1.0.0
 *
 * @param string $field       the form field we are looking for the value
 * @param int    $customer_id the wp ID of the customer
 *
 * @return string
*/
function it_exchange_print_shipping_address_value( $field, $customer_id=false ) {
    $customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
    $saved_address = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
    $cart_address = it_exchange_get_cart_shipping_address();

    $value = empty( $saved_address[$field] ) ? '' : $saved_address[$field];
    $value = empty( $cart_address[$field] ) ? $value : $cart_address[$field];
    echo 'value="' . esc_attr( $value ) . '" ';
}

/** @todo finish this 
 * merge with get_formatted_address
**/
function it_exchange_get_formatted_shipping_address() {
    $customer_id = it_exchange_get_current_customer_id();
    $address    = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
    $formatted  = $address['name'] . '<br />';
    $formatted .= $address['address1'];
    $formatted .= empty( $address['address2'] ) ? '' : '<br />' . $address['address2'];
    $formatted .= '<br />' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];

    return $formatted;
}

/** 
 * Is cart address valid?
 *
 * @since 1.0.0
 *
 * @return boolean
*/
function it_exchange_is_shipping_address_valid() {
	$cart_address  = it_exchange_get_cart_data( 'shipping-address' );
	$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
	$customer_id   = it_exchange_get_current_customer_id();
	$customer_id   = empty( $customer_id ) ? $cart_customer : $customer_id;

	return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
} 
