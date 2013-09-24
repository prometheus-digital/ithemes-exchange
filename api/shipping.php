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
