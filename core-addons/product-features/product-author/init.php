<?php
/**
 * This add-on will enable the author metabox on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_product_author_addon' );
add_filter( 'it_cart_buddy_get_product_feature-product_author', 'it_cart_buddy_product_author_addon_get_product_author', 9, 2 );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_product_author_addon() {
	// Register the product feature
	$slug        = 'product_author';
	$description = 'Enables the default WordPress Author field for the product';
	it_cart_buddy_register_product_feature( $slug, $description );

	// Add it to all enabled product-type addons
	$products = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
	foreach( $products as $key => $params ) {
		it_cart_buddy_add_feature_support_to_product_type( 'product_author', $params['slug'] );
	}
}

/**
 * Return the product's product_author
 *
 * This returns the image, not the ID
 *
 * @since 0.3.8
 * @param mixed $product_author the values passed in by the WP Filter API. Ignored here.
 * @param integer product_id the WordPress post ID
 * @return string author
*/
function it_cart_buddy_product_author_addon_get_product_author( $product_author, $product_id ) {
	$product = it_cart_buddy_get_product( $product_id );
	if ( empty( $product->post_author ) )
		return;

	if ( $author = get_the_author_meta( 'display_name', $product->post_author ) )
		return $author;

	return false;
}
