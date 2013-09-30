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

/**
 * Formats the Shipping Address for display
 *
 * @todo this function sucks. Lets make a function for formatting any address. ^gta
 * @since 1.3.0
 *
 * @return string HTML
*/
function it_exchange_get_formatted_shipping_address( $shipping_address=false ) { 
	$formatted   = array();
	$shipping     = empty( $shipping_address ) ? it_exchange_get_cart_shipping_address() : $shipping_address;
	$formatted[] = implode( ' ', array( $shipping['first-name'], $shipping['last-name'] ) );
	if ( ! empty( $shipping['company-name'] ) ) 
		$formatted[] = $shipping['company-name'];
	if ( ! empty( $shipping['address1'] ) ) 
		$formatted[] = $shipping['address1'];
	if ( ! empty( $shipping['address2'] ) ) 
		$formatted[] = $shipping['address2'];
	if ( ! empty( $shipping['city'] ) || ! empty( $shipping['state'] ) || ! empty( $shipping['zip'] ) ) { 
		$formatted[] = implode( ' ', array( ( empty( $shipping['city'] ) ? '': $shipping['city'] .',' ),
			( empty( $shipping['state'] ) ? '': $shipping['state'] ),
			( empty( $shipping['zip'] ) ? '': $shipping['zip'] ),
		) );
	}   
	if ( ! empty( $shipping['country'] ) ) 
		$formatted[] = $shipping['country'];

	$formatted = implode( '<br />', $formatted );
	return apply_filters( 'it_exchange_get_formatted_shipping_address', $formatted );
}

/**
 * Grabs all the shipping methods available to the passed product
 *
 * 1) Grab all shipping methods
 * 2) Check to see if they're enabled
 * 3) Return an arry of ones that are enabled.
 *
 * @since CHANGEME
 *
 * @param  object product an IT_Exchange_Product object
 * @return an array of shipping methods
*/
function it_exchange_get_available_shipping_methods_for_product( $product ) { 

	$providers         = it_exchange_get_registered_shipping_providers();
	$provider_methods  = array();
	$available_methods = array();

	// Grab all registerd shipping methods for all providers
	foreach( (array) $providers as $provider ) { 
		$provider         = it_exchange_get_registered_shipping_provider( $provider['slug'] );
		$provider_methods = array_merge( $provider_methods, $provider->shipping_methods );
	}   

	// Loop through provider methods and only use the ones that are available for this product
	foreach( $provider_methods as $slug ) { 
		if ( $method = it_exchange_get_registered_shipping_method( $slug, $product->ID ) ) { 
			if ( $method->available )
				$available_methods[$slug] = $method;
		}   
	}   

	return apply_filters( 'it_exchange_get_available_shipping_methods_for_product', $available_methods, $product );
}

function it_exchange_get_enabled_shipping_methods_for_product( $product, $return='object' ) { 

	// Are we viewing a new product?
	$screen         = is_admin() ? get_current_screen() : false;
	$is_new_product = is_admin() && ! empty( $screen->action ) && 'add' == $screen->action;

	// Return false if shipping is turned off for this product
	if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) && ! $is_new_product )
		return false;

	$enabled_methods                    = array();
	$product_overriding_default_methods = it_exchange_get_shipping_feature_for_product( 'core-available-shipping-methods', $product->ID );

	foreach( (array) it_exchange_get_available_shipping_methods_for_product( $product ) as $slug => $available_method ) { 
		// If we made it here, the method is available. Check to see if it has been turned off for this specific product
		if ( false !== $product_overriding_default_methods ) { 
			if ( ! empty( $product_overriding_default_methods->$slug ) ) 
				$enabled_methods[$slug] = ( 'slug' == $return ) ? $slug : $available_method;
		} else {
			$enabled_methods[$slug] = ( 'slug' == $return ) ? $slug : $available_method;
		}   
	}   
	return $enabled_methods;
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

function it_exchange_get_cart_shipping_method() {
	$method = it_exchange_get_cart_data( 'shipping-method' );
	$method = empty( $method[0] ) ? false : $method[0];
	return empty( $method ) ? false : $method;
}

function it_exchange_get_shipping_methods_for_cart() {
	$methods = array();
	foreach( it_exchange_get_cart_products() as $product ) {
		if ( false === ( $product = it_exchange_get_product( $product['product_id'] ) ) )
			continue;

		foreach( (array) it_exchange_get_enabled_shipping_methods_for_product( $product ) as $method ) {
			if ( ! empty( $method->slug ) )
				$methods[$method->slug] = $method;
		}
	}

	return $methods;
}
