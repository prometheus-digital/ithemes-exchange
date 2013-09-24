<?php
/**
 * This file contains methods for accessing and updating Shipping features
 * @since CHANGEME
 * @package IT_Exchange
*/

/**
 * Keeps track of all available shipping features
 *
 * @since CHANGEME
 * @param string $slug the identifying slug for the shipping feature
 * @param string $class the shipping feature class
 * @return void
*/
function it_exchange_register_shipping_feature( $slug, $class ) {
	// Don't attempt to register if class doesn't exist
	if ( ! class_exists( $class ) )
		return false;

	// Add it to the global
	$GLOBALS['it_exchange']['shipping']['shipping_features'][$slug] = $class;

	// Provide an action for 3rd parties
	do_action( 'it_exchange_register_shipping_feature', $slug, $class);
}

/**
 * Grabs all shipping registered features from GLOBALS and inits objects and returns
 *
 * @since CHANGEME
 *
 * @return array
*/
function it_exchange_get_registered_shipping_features() {
	$features = empty( $GLOBALS['it_exchange']['shipping']['shipping_features'] ) ? array() : $GLOBALS['it_exchange']['shipping']['shipping_features'];
	return $features;
}

/**
 * Prints the shipping feature boxes on the add/edit product page
 *
 * This will loop through all registered shipping features for the current product
 * and print thier feature boxes inside the shipping settings metabox.
 * If it determines that a shipping registered shipping feature is not available because
 * the shipping methods its assoicated with are not enabled, it will hide the box.
 *
 * @since CHANGEME
 *
 * @param  object $product an IT_Exchange_Product object
 * @return void
*/
function it_exchange_do_shipping_feature_boxes( $product ) {

	// Grab all shipping features needed for the passed product
	$shipping_features = it_exchange_get_shipping_features_for_product( $product );

	// Loop through returned shipping features and call the method to print the UI
	foreach( (array) $shipping_features as $feature ) {
		$feature->print_add_edit_feature_box();
	}

}

/**
 * Grab any features needed by all possible shipping methods applicable to this product
 *
 * - Shipping features are tied to Shipping Methods
 * - Shipping methods are associated with Shipping Providers
 * - Shipping methods are available to a product if the Provider is available to the product type
 *
 * @since CHANGEME
 *
 * @param  object $product an IT_Exchange_Product object
 * @return an array of shipping feature objects
*/
function it_exchange_get_shipping_features_for_product( $product ) {

	// Grab all available methods for this product
	$methods  = it_exchange_get_available_shipping_methods_for_product( $product );

	// Init features array
	$features = array();

	// Loop through methods and add all required features to the array
	foreach( $methods as $method ) {
		if ( ! empty( $method->shipping_features ) && is_array( $method->shipping_features ) )
			$features = array_merge( $features, $method->shipping_features );
	}

	// Clean the array
	$features = array_values( array_unique( $features ) );

	// Grab registered feature details
	$registered_features = it_exchange_get_registered_shipping_features( $features );

	// Init return array
	$shipping_features = array();

	// Loop through array and init objects
	foreach( $registered_features as $slug => $class ) {
		if ( class_exists( $class ) && in_array( $slug, $features ) )
			$shipping_features[$slug] = new $class( $product->ID );
	}

	return apply_filters( 'it_exchange_get_shipping_features_for_product', $shipping_features, $product );
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
	$providers         = it_exchange_get_registered_shipping_providers(); /** @todo Make this dynamic per product **/
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
