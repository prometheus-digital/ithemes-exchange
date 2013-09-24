<?php
/**
 * Registers core shipping features
 *
*/
function it_exchange_shipping_register_core_shipping_features() {

	// Available Shipping Methods
	$options = array(
		'slug'  => 'core-available-shipping-methods',
		'file'  => dirname( __FILE__ ) . '/core-available-shipping-methods.php',
		'class' => 'IT_Exchange_Core_Shipping_Feature_Available_Shipping_Methods',
	);
	it_exchange_register_shipping_feature( $options['slug'], $options );

	// From Address Override
	$options = array(
		'slug'  => 'core-from-address',
		'file'  => dirname( __FILE__ ) . '/core-from-address.php' ,
		'class' => 'IT_Exchange_Core_Shipping_Feature_From_Address',
	);
	it_exchange_register_shipping_feature( $options['slug'], $options );

	// Weight and Dimensions
	$options = array(
		'slug'  => 'core-weight-dimensions',
		'file'  => dirname( __FILE__ ) . '/core-weight-dimensions.php' ,
		'class' => 'IT_Exchange_Core_Shipping_Feature_Weight_Dimensions',
	);
	it_exchange_register_shipping_feature( $options['slug'], $options );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_shipping_register_core_shipping_features' );
