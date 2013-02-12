<?php
/**
 * API Functions for Product Type Add-ons
 *
 * In addition to the functions found below, Cart Buddy offers the following actions related to products
 * - it_cart_buddy_save_post_unvalidated                // Runs every time a cart buddy product is saved.
 * - it_cart_buddy_save_post_unavalidate-[product-type] // Runs every time a specific cart buddy product type is saved.
 * - it_cart_buddy_save_post                            // Runs every time a cart buddy product is saved if not an autosave and if user has permission to save post
 * - it_cart_buddy_save_post-[product-type]             // Runs every time a specific cart buddy product-type is saved if not an autosave and if user has permission to save post
 *
 * @package IT_Cart_Buddy
 * @since 0.3.1
*/

/**
 * Grabs the product type of a product
 *
 * @since 0.3.1
 * @return string the product type
*/
function it_cart_buddy_get_product_type( $post=false ) {
	if ( ! $post )
		global $post;

	// Return value from IT_Cart_Buddy_Product if we are able to locate it
	$product = it_cart_buddy_get_product( $post );
	if ( is_object( $product ) && ! empty ( $product->product_type ) )
		return $product->product_type;

	// Return query arg if is present
	if ( ! empty ( $_GET['product_type'] ) )
		return $_GET['product_type'];

	return false;
}

/**
 * Returns the options array for a registered product-type
 *
 * @since 0.3.2
 * @param string $product_type  slug for the product-type
*/
function it_cart_buddy_get_product_type_options( $product_type ) {
	if ( $addon = it_cart_buddy_get_add_on( $product_type ) )
		return $addon['options'];
	
	return false;
}

/**
 * Retreives a product object by passing it the WP post object or post id
 *
 * @since 0.3.2
 * @param mixed $post  post object or post id
 * @rturn object IT_Cart_Buddy_Product object for passed post
*/
function it_cart_buddy_get_product( $post ) {
	return new IT_Cart_Buddy_Product( $post );
}

/**
 * Get IT_Cart_Buddy_Products
 *
 * @since 0.3.3
 * @return array  an array of IT_Cart_Buddy_Product objects
*/
function it_cart_buddy_get_products( $args=array() ) {
	$defaults = array(
		'post_type' => 'it_cart_buddy_prod',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $args['product_type'] ) ) {
		$meta_query = empty( $args['meta_query'] ) ? array() : $args['meta_query'];
		$meta_query[] = array( 
			'key'   => '_it_cart_buddy_product_type',
			'value' => $args['product_type'],
		);
		$args['meta_query'] = $meta_query;
	}

	if ( $products = get_posts( $args ) ) {
		foreach( $products as $key => $product ) {
			$products[$key] = it_cart_buddy_get_product( $product );
		}
		return $products;
	}

	return array();
}

/**
 * Does the passed product have the referenced feature?
 *
 * @since 0.3.3
 * @param mixed $product id or object referncing the object
 * @param string $feature feature being asked for
 * @return boolean
*/
function it_cart_buddy_product_has_feature( $product, $feature ) {
	if ( ! is_object( $product ) || 'IT_Cart_Buddy_Product' != get_class( $product ) )
		$product = it_cart_buddy_get_product( $product );

	// Return false if this isn't a product ID
	if ( ! $product->ID )
		return false;

	// Return false if this product doesn't support this feature. Set feature_key if supported.
	if ( empty( $product->product_supports[$feature]['key'] ) )
		return false;
	else
		$feature_key = $product->product_supports[$feature]['key'];

	// Return true if this product has a value for this feature
	if ( ! empty ( $product->product_data[$feature_key] ) )
		return true;

	return false;
}
