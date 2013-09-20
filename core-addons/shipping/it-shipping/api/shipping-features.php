<?php
/**
 * This file contains methods for accessing and updating Shipping features
 *
 * @since CHANGEME
 * @package IT_Exchange
*/


function it_exchange_get_shipping_features_for_product_type( $product_type ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/class-shipping-feature.php' );
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping-features/exchange-flat-rate.php' );
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping-features/available-shipping-methods.php' );
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping-features/from-address.php' );
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping-features/weight-dimensions.php' );
	$flat_rate         = new IT_Exchange_Exchange_Flat_Rate_Shipping_Feature();
	$enabled_methods   = new IT_Exchange_Available_Shipping_Methods_Shipping_Feature();
	$from_address      = new IT_Exchange_Exchange_From_Address_Shipping_Feature();
	$weight_dimensions = new IT_Exchange_Exchange_Weight_Dimensions_Shipping_Feature();
	return array( $flat_rate, $enabled_methods, $from_address, $weight_dimensions );
}


#############
##############




/**
 * Check if a given product supports a specific feature 
 *
 * @since CHANGEME
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 * @return boolean
*/
function it_exchange_product_supports_shipping_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'it_exchange_product_supports_shipping_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Check if a given product has a specific feature 
 *
 * @since CHANGEME
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 * @return boolean
*/
function it_exchange_product_has_shipping_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'it_exchange_product_has_shipping_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Update the given product's shipping feature value
 *
 * @since CHANGEME
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param mixed $feature_value the value for the feature
 * @param array $options the options for the feature
 * @return boolean
*/
function it_exchange_update_shipping_feature( $product_id, $feature_key, $feature_value, $options=array() ) {
	do_action( 'it_exchange_update_shipping_feature_' . $feature_key, $product_id, $feature_value, $options );
}

/**
 * Get the value for a shipping feature of a specific product
 *
 * @since CHANGEME
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 * @return mixed the value of the feature
*/
function it_exchange_get_shipping_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'it_exchange_get_shipping_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Adds support for a specific product-feature to a specific product-type
 *
 * @since CHANGEME
 * @param string $feature_key the slug for the featuer
 * @param string $product_type the product-type slug
 * @return void
*/
function it_exchange_add_shipping_feature_support_to_product_type( $feature_key, $product_type ) {
	$_feature_key = str_replace( 'temp_disabled_', '', $feature_key );
	if ( ! isset( $GLOBALS['it_exchange']['shipping_features'][$_feature_key] ) )
		return;
	$GLOBALS['it_exchange']['shipping_features'][$feature_key]['product_types'][$product_type] = true;
}

/**
 * Removes support for a shipping feature from a specific product-type
 *
 * @since CHANGEME
 * @param string $feature_key the slug for the feature
 * @param string $product_type the product-type slug
 * @return void
*/
function it_exchange_remove_shipping_feature_support_for_product_type( $feature_key, $product_type ) {
	if ( isset( $GLOBALS['it_exchange']['shipping_features'][$feature_key]['product_types'][$product_type] ) )
		$GLOBALS['it_exchange']['shipping_features'][$feature_key]['product_types'][$product_type] = false;
	do_action( 'it_exchange_remove_shipping_feature_support_for_product_type', $feature_key, $product_type );
}

/**
 * Check if a given product-type supports a specific shipping feature
 *
 * @since CHANGEME
 *
 * @param string $product_type the product-type slug
 * @param string $feature_key the slug for the feature
 * @return boolean
*/
function it_exchange_product_type_supports_shipping_feature( $product_type, $feature_key ) {
	$shipping_features = it_exchange_get_registered_shipping_features();

	if ( empty( $shipping_features[$feature_key] ) )
		return false;

	if ( empty( $shipping_features[$feature_key]['product_types'][$product_type] ) )
		return false;

	return true;
}

/**
 * Keeps track of all available shipping features
 *
 * @since CHANGEME
 * @param slug
 * @return void
*/
function it_exchange_register_shipping_feature( $slug, $description='', $default_product_types=array() ) {
	$GLOBALS['it_exchange']['shipping_features'][$slug]['slug']        = $slug;
	$GLOBALS['it_exchange']['shipping_features'][$slug]['description'] = $description;
	do_action( 'it_exchange_register_shipping_feature', $slug, $description, $default_product_types );
}

/**
 * Returns all registered shipping_features
 *
 * @since CHANGEME
 * @return array
*/
function it_exchange_get_registered_shipping_features() {
	$shipping_features = isset( $GLOBALS['it_exchange']['shipping_features'] ) ? (array) $GLOBALS['it_exchange']['shipping_features'] : array();
	return apply_filters( 'it_exchange_get_registered_shipping_features', $shipping_features );
}
