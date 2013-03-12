<?php
/**
 * This add-on will enable the featured image box on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_featured_image_addon' );
add_action( 'it_cart_buddy_update_product_feature-featured_image', 'it_cart_buddy_featured_image_addon_save_featured_image', 9, 2 );
add_filter( 'it_cart_buddy_get_product_feature-featured_image', 'it_cart_buddy_featured_image_addon_get_featured_image', 9, 2 );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_featured_image_addon() {
	// Register the product feature
	$slug        = 'featured_image';
	$description = 'A Featured Image for the product';
	it_cart_buddy_register_product_feature( $slug, $description );

	// Add it to all enabled product-type addons
	$products = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
	foreach( $products as $key => $params ) {
		it_cart_buddy_add_feature_support_to_product_type( 'featured_image', $params['slug'] );
	}
}

/**
 * Return the product's featured_image
 *
 * This returns the image, not the ID
 *
 * @since 0.3.8
 * @param mixed $featured_image the values passed in by the WP Filter API. Ignored here.
 * @param integer product_id the WordPress post ID
 * @return string featured image
*/
function it_cart_buddy_featured_image_addon_get_featured_image( $featured_image, $product_id ) {
	if ( has_post_thumbnail( $product_id ) )
	return get_the_post_thumbnail( $product_id );
}
