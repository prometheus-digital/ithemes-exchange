<?php
/**
 * API Functions for Product Type Add-ons
 * @package IT_Exchange
 * @since 0.3.1
*/

/**
 * Grabs the product type of a product
 *
 * @since 0.3.1
 * @return string the product type
*/
function it_exchange_get_product_type( $post=false ) {
	if ( ! $post )
		global $post;

	// Return value from IT_Exchange_Product if we are able to locate it
	$product = it_exchange_get_product( $post );
	if ( is_object( $product ) && ! empty ( $product->product_type ) )
		return $product->product_type;

	// Return query arg if is present
	if ( ! empty ( $_GET['it-exchange-product-type'] ) )
		return $_GET['it-exchange-product-type'];

	return false;
}

/**
 * Returns the options array for a registered product-type
 *
 * @since 0.3.2
 * @param string $product_type  slug for the product-type
*/
function it_exchange_get_product_type_options( $product_type ) {
	if ( $addon = it_exchange_get_addon( $product_type ) )
		return $addon['options'];
	
	return false;
}

/**
 * Retreives a product object by passing it the WP post object or post id
 *
 * @since 0.3.2
 * @param mixed $post  post object or post id
 * @rturn object IT_Exchange_Product object for passed post
*/
function it_exchange_get_product( $post ) {
	$product = new IT_Exchange_Product( $post );
	if ( $product->ID )
		return $product;
	return false;
}

/**
 * Get IT_Exchange_Products
 *
 * @since 0.3.3
 * @return array  an array of IT_Exchange_Product objects
*/
function it_exchange_get_products( $args=array() ) {
	$defaults = array(
		'post_type' => 'it_exchange_prod',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['product_type'] ) ) {
		$meta_query = empty( $args['meta_query'] ) ? array() : $args['meta_query'];
		$meta_query[] = array( 
			'key'   => '_it_exchange_product_type',
			'value' => $args['product_type'],
		);
		$args['meta_query'] = $meta_query;
	}

	if ( $products = get_posts( $args ) ) {
		foreach( $products as $key => $product ) {
			$products[$key] = it_exchange_get_product( $product );
		}
		return $products;
	}

	return array();
}

/**
 * Sets a global for the current product's id
 *
 * Looks for paramater. If passed param is a vailid product id, it sets that.
 * If passed param is false or not a product id, it looks for global $post.
 * If global $post and passed param are not product ids, it is set to false
 *
 * @since 0.3.8
 * @param integer $product_id
 * @return void
*/
function it_exchange_set_the_product_id( $product_id=false ) {
	if ( $product = it_exchange_get_product( $product_id ) )
		$GLOBALS['it_exchange']['product_id'] = $product->ID;
	else
		$GLOBALS['it_exchange']['product_id'] = false;
}

/**
 * Returns the global for the current product's id
 *
 * @since 0.3.8
 * @return mixed product id or false
*/
function it_exchange_get_the_product_id() {
	return empty( $GLOBALS['it_exchange']['product_id'] ) ? false : $GLOBALS['it_exchange']['product_id'];
}

/**
 * Is the product availabel based on start and end availability dates
 *
 * @since 0.4.0
 *
 * @param int $product_id Product ID
 * @return boolean
*/
function it_exchange_is_product_available( $product_id=false ) {
	if ( ! it_exchange_get_product( $product_id ) )
		return false;
	
	$past_start_date = true;
	$before_end_date = true;
	$now_start = strtotime( date( 'Y-m-d 00:00:00' ) );
	$now_end = strtotime( date( 'Y-m-d 23:59:59' ) );

	// Check start time
	if ( 
		it_exchange( 'product', 'supports-availability', 'type=start' ) &&  
		it_exchange( 'product', 'has-availability', 'type=start' )   
	) { 
		$start_date = strtotime( it_exchange_get_product_feature( $product_id, 'availability', array( 'type' => 'start' ) ) . ' 00:00:00' );
		if ( $now_start < $start_date )
			$past_start_date = false;

	}   

	// Check end time
	if ( 
		it_exchange( 'product', 'supports-availability', 'type=end' ) &&  
		it_exchange( 'product', 'has-availability', 'type=end' )   
	) { 
		$end_date = strtotime( it_exchange_get_product_feature( $product_id, 'availability', array( 'type' => 'end' ) ) . ' 23:59:59' );
		if ( $now_end > $end_date )
			$before_end_date = false;
	}   

	return $past_start_date && $before_end_date;

}
