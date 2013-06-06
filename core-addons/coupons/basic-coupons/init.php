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
 * Register the cart coupon type
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_register_coupon_type() {
	it_exchange_register_coupon_type( 'cart' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_basic_coupons_register_coupon_type' );

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
add_filter( 'it_exchange_coupon_additional_data', 'it_exchange_basic_coupons_add_meta_data_to_coupon_object', 9, 2 );

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
	$cart_data = it_exchange_get_cart_data( 'basic_coupons' );
	return empty( $cart_data ) ? false : $cart_data;
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
		'class'       => 'apply-coupon',
		'placeholder' => __( 'Coupon Code', 'LION' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );	

	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';
	return '<input type="text" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '" placeholder="' . esc_attr( $options['placeholder'] ) . '" value="" />';	
}
add_filter( 'it_exchange_apply_cart_coupon_field', 'it_exchange_base_coupons_apply_cart_coupon_field', 10, 2 );

/**
 * Apply a coupon to a cart on update
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_handle_coupon_on_cart_update() {
	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';

	// Abort if no coupon code was added
	if ( ! $coupon_code = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var] )
		return;

	it_exchange_apply_coupon( 'cart', $coupon_code );
}
add_action( 'it_exchange_update_cart', 'it_exchange_basic_coupons_handle_coupon_on_cart_update' );

/**
 * Applies a coupon code to a cart if it exists and is valid
 *
 * @since 0.4.0
 *
 * @param boolean $result this is default to false. gets set by apply_filters
 * @param array $options - must contain coupon key
 * @return boolean
*/
function it_exchange_basic_coupons_apply_to_cart( $result, $options=array() ) {

	// Set coupon code. Return false if one is not available
	$coupon_code = empty( $options['code'] ) ? false : $options['code'];
	if ( empty( $coupon_code ) )
		return false;

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
		return false;

	$coupon = reset( $coupons );

	// Abort if not within start and end dates
	$start_okay = empty( $coupon->start_date ) || strtotime( $coupon->start_date ) <= strtotime( date( 'Y-m-d' ) );
	$end_okay   = empty( $coupon->end_date ) || strtotime( $coupon->end_date ) >= strtotime( date( 'Y-m-d' ) );
	if ( ! $start_okay || ! $end_okay )
		return false;

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

	return true;
}
add_action( 'it_exchange_apply_coupon_to_cart', 'it_exchange_basic_coupons_apply_to_cart', 10, 2 );

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
		$url = clean_it_exchange_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
		$url = add_query_arg( $var . '[]', $options['code'] );
		return '<a data-coupon-code="' . esc_attr( $options['code'] ) . '" class="' . esc_attr( $options['class'] ) . '" href="' . $url . '">' . esc_attr( $options['label'] ) . '</a>';
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
	$total_discount = it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );
	$total = $total - $total_discount;
	return $total;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_basic_coupons_apply_discount_to_cart_total' );

/**
 * Returns the total discount from applied coupons
 *
 * @since 0.4.0
 *
 * @param string $total existing value passed in by WP filter
 * @return string
*/
function it_exchange_basic_coupons_get_total_discount_for_cart( $discount, $options=array() ) {
    $defaults = array(
        'format_price' => true,
    );  
    $options = ITUtility::merge_defaults( $options, $defaults );

	$coupons = it_exchange_get_applied_coupons( 'cart' );
	$subtotal = it_exchange_get_cart_subtotal( false );
	
	foreach( (array) $coupons as $coupon ) {
		$discount = ( '%' == $coupon['amount_type'] ) ? $discount + ( ( $coupon['amount_number'] / 100 ) * $subtotal ) : $discount + $coupon['amount_number'];
	}
	if ( $options['format_price'] )
		$discount = it_exchange_format_price( $discount );
	return $discount;
}
add_filter( 'it_exchange_get_total_discount_for_cart', 'it_exchange_basic_coupons_get_total_discount_for_cart', 10, 2 );

/**
 * Remove coupon from cart
 *
 * @todo redirect with feedback?
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_handle_remove_coupon_from_cart_request() {
	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';
	if ( empty( $_REQUEST[$var] ) )
		return;

	foreach( (array) $_REQUEST[$var] as $code ) {
		it_exchange_remove_coupon( 'cart', $code );
	}

	if ( it_exchange_is_multi_item_cart_allowed() )
		$url = it_exchange_get_page_url( 'cart' );
	else
		$url = clean_it_exchange_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );

	wp_redirect( $url );
	die();
}
add_action( 'template_redirect', 'it_exchange_basic_coupons_handle_remove_coupon_from_cart_request', 9 );

/**
 * Removes a coupon from the cart
 *
 * @param boolean $result default result passed by apply_filters
 * @param string $coupon_code code of coupon to be removed
 * @return boolean
*/
function it_exchange_basic_coupons_remove_coupon_from_cart( $result, $options=array() ) {
	$coupon_code = empty( $options['code'] ) ? false : $options['code'];
	if ( empty( $coupon_code ) )
		return false;

	$coupons = it_exchange_get_applied_coupons( 'cart' );
	if ( isset( $coupons[$coupon_code] ) )
		unset( $coupons[$coupon_code] );

	// Unset coupons
	it_exchange_update_cart_data( 'basic_coupons', $coupons );
	return true;
}
add_filter( 'it_exchange_remove_coupon_for_cart', 'it_exchange_basic_coupons_remove_coupon_from_cart', 10, 2 );

/**
 * Returns the summary needed for a transaction
 *
 * @since 0.4.0
 *
 * @param string $summary passed in by WP filter. Ignored here.
 * @param mixed  $transaction_coupon the coupon data stored in the transaction
 * @return string summary
*/
function it_exchange_basic_coupons_transaction_summary( $summary, $transaction_coupon ) {
	$transaction_coupon = reset( $transaction_coupon );
	$id     = empty( $transaction_coupon['id'] )            ? false : $transaction_coupon['id'];
	$title  = empty( $transaction_coupon['title'] )         ? false : $transaction_coupon['title'];
	$code   = empty( $transaction_coupon['code'] )          ? false : $transaction_coupon['code'];
	$number = empty( $transaction_coupon['amount_number'] ) ? false : $transaction_coupon['amount_number'];
	$type   = empty( $transaction_coupon['amount_type'] )   ? false : $transaction_coupon['amount_type'];
	$start  = empty( $transaction_coupon['start_date'] )    ? false : $transaction_coupon['start_date'];
	$end    = empty( $transaction_coupon['end_date'] )      ? false : $transaction_coupon['end_date'];

	$url = trailingslashit( get_admin_url() ) . 'admin.php';
	$url = add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $id ), $url );

	$link = '<a href="' . $url . '">' . __( 'View Coupon', 'LION' ) . '</a>';

	$string = '';
	if ( $title )
		$string .= $title . ': ';
	if ( $code )
		$string .= $code . ' | ';

	if ( $number && $type )
		$string .= implode( '', array( $number, $type ) ) . ' | ';

	$string .= ' ' . $link;

	return $string;
}
add_filter( 'it_exchange_get_transaction_cart_coupon_summary', 'it_exchange_basic_coupons_transaction_summary', 10, 2 );

/**
 * Returns the coupon discount type
 *
 * @since 0.4.0
 *
 * @param string $method default type passed by WP filters. Not used here.
 * @param array $options includes the ID we're looking for.
 * @return string
*/
function it_exchange_basic_coupons_get_dicount_method( $method, $options=array() ) {
	if ( empty( $options['id'] ) || ! $coupon = it_exchange_get_coupon( $options['id'] ) )
		return false;

	return empty( $coupon->amount_type ) ? false : $coupon->amount_type;
}
add_filter( 'it_exchange_get_coupon_discount_method', 'it_exchange_basic_coupons_get_dicount_method', 10, 2 );
