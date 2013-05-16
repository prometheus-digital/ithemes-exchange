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

/**
 * Add field names
 *
 * @since 0.4.0
 *
 * @param array $names Incoming core vars => values
 * @return array
*/
function it_exchange_basic_coupons_register_field_names( $names ) {
	$names['apply_coupon'] = 'it-exchange-basic-coupons-apply-coupon';
	$names['remove_coupon'] = 'it-exchange-basic-coupons-remove-coupon';
	return $names;
}
add_filter( 'it_exchange_default_field_names', 'it_exchange_basic_coupons_register_field_names' ); 

/**
 * Register support for cart coupons if we have at least one
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_supports_cart_coupons() {
	return (boolean) it_exchange_get_coupons();
}
add_filter( 'it_exchange_supports_cart_coupons', 'it_exchange_basic_coupons_supports_cart_coupons' );

/**
 * Returns applied cart coupons
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return boolean
*/
function it_exchange_basic_coupons_applied_cart_coupons( $incoming=false ) {
	$cart_data = it_exchange_get_cart_data();
	return empty( $cart_data['basic_coupons'] ) ? false : $cart_data['basic_coupons'];
}
add_filter( 'it_exchange_get_applied_cart_coupons', 'it_exchange_basic_coupons_applied_cart_coupons' );

/**
 * Determines if we are currently accepting more coupons
 *
 * Basic coupons only allows one coupon applied to each cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return boolean
*/
function it_exchange_basic_coupons_accepting_cart_coupons( $incoming=false ) {
	return ! (boolean) it_exchange_get_applied_coupons( 'cart' );
}
add_filter( 'it_exchange_accepting_cart_coupons', 'it_exchange_basic_coupons_accepting_cart_coupons' );

/**
 * Return the form field for applying a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return string
*/
function it_exchange_base_coupons_apply_cart_coupon_field( $incoming=false, $options=array() ) {
	$defaults = array(
		'class' => 'apply-coupon',
	);
	$options = ITUtility::merge_defaults( $options, $defaults );	

	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';
	return '<input type="text" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '" value="" />';	
}
add_filter( 'it_exchange_apply_cart_coupon_field', 'it_exchange_base_coupons_apply_cart_coupon_field', 10, 2 );

/**
 * Apply a coupon to a cart on update
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_apply_coupon_to_cart() {
	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';

	// Abort if no coupon code was added
	if ( ! $coupon_code = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var] )
		return;

	// Abort if no coupon code matches and falls within dates
	$args = array(
		'meta_query' => array( 
			array( 
				'key' => '_it-basic-code',
				'value' => $coupon_code,
			),
		),
	);
	if ( ! $coupons = it_exchange_get_coupons( $args ) )
		return;

	$coupon = reset( $coupons );

	// Abort if not within start and end dates
	$start_okay = empty( $coupon->start_date ) || strtotime( $coupon->start_date ) <= strtotime( date( 'Y-m-d' ) );
	$end_okay   = empty( $coupon->end_date ) || strtotime( $coupon->end_date ) >= strtotime( date( 'Y-m-d' ) );
	if ( ! $start_okay || ! $end_okay )
		return;

	// Format data for session
	$coupon = array(
		'id'            => $coupon->ID,
		'title'         => $coupon->post_title,
		'code'          => $coupon->code,
		'amount_number' => $coupon->amount_number,
		'amount_type'   => $coupon->amount_type,
		'start_date'    => $coupon->start_date,
		'end_date'      => $coupon->end_date,
	);

	// Add to session data
	$data = array( $coupon['code'] => $coupon );
	it_exchange_update_cart_data( 'basic_coupons', $data );
}
add_action( 'it_exchange_update_cart', 'it_exchange_basic_coupons_apply_coupon_to_cart' );

/**
 * Clear cart coupons when cart is emptied
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_clear_cart_coupons_on_empty() {
	it_exchange_remove_cart_data( 'basic_coupons' );
}
add_action( 'it_exchange_empty_shopping_cart', 'it_exchange_clear_cart_coupons_on_empty' );

/**
 * Return the form checkbox for removing a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return string
*/
function it_exchange_base_coupons_remove_cart_coupon_html( $incoming=false, $code, $options=array() ) {
	$defaults = array(
		'class'  => 'remove-coupon',
		'format' => 'link',
		'label'  => __( '&times;', 'LION' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );	

	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';

	if ( 'checkbox' == $options['format'] ) {
		return '<input type="checkbox" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '[]" value="' . esc_attr( $options['code'] ) . '" />&nbsp;' . esc_attr( $options['label'] );	
	} else {
		$url = add_query_arg( $var . '[]', $options['code'] );
		return '<a class="' . esc_attr( $options['class'] ) . '" href="' . $url . '">' . esc_attr( $options['label'] ) . '</a>';
	}
}
add_filter( 'it_exchange_remove_cart_coupon_html', 'it_exchange_base_coupons_remove_cart_coupon_html', 10, 3 );

/**
 * Modify the cart total to reflect coupons
 *
 * @since 0.4.0
 *
 * @return price
*/
function it_exchange_basic_coupons_apply_discount_to_cart_total( $total ) {
	$coupons = it_exchange_get_applied_coupons( 'cart' );
	foreach( (array) $coupons as $coupon ) {
		$total = ( '%' == $coupon['amount_type'] ) ? $total - ( ( $coupon['amount_number'] / 100 ) * $total ) : $total - $coupon['amount_number'];
	}
	return $total;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_basic_coupons_apply_discount_to_cart_total' );

/**
 * Remove coupon from cart
 *
 * @todo redirect with feedback?
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_remove_coupon_from_cart() {
	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';
	if ( empty( $_REQUEST[$var] ) )
		return;

	$coupons = it_exchange_get_applied_coupons( 'cart' );
	foreach( (array) $coupons as $code => $data ) {
		if ( in_array( $code, $_REQUEST[$var] ) )
			unset( $coupons[$code] );
	}

	// Unset coupons
	it_exchange_update_cart_data( 'basic_coupons', $coupons );

}
add_action( 'template_redirect', 'it_exchange_basic_coupons_remove_coupon_from_cart', 9 );
