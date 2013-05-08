<?php
/**
 * Basic Coupons
 * @package IT_Exchange
 * @since 0.4.0
*/

if ( is_admin() ) {
	include( dirname( __FILE__) . '/admin.php' );
}

/**
 * Adds meta data for Basic Coupons to the coupon object
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_basic_coupons_add_meta_data_to_coupon_object( $data, $object ) {
	// Set post meta keys used in basic coupons
	$post_meta_keys = array(
		'code'          => '_it-basic-code',
		'amount_number' => '_it-basic-amount-number',
		'amount_type'   => '_it-basic-amount-type',
		'start_date'    => '_it-basic-start-date',
		'end_date'      => '_it-basic-end-date',
	);

	// Loop through and add them to the data that will be added as properties to coupon object
	foreach( $post_meta_keys as $property => $key ) {
		$data[$property] = get_post_meta( $object->ID, $key, true );
	}

	// Return data
	return $data;
}
add_filter( 'it_excahnge_coupon_additional_data', 'it_exchange_basic_coupons_add_meta_data_to_coupon_object', 9, 2 );
