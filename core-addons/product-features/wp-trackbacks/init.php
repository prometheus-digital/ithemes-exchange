<?php
/**
 * This add-on will enable the trackbacks metabox on the edit add / edit product page
 *
 * This provides no get function. Just use get_post_meta()
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_wp_trackbacks_addon' );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_wp_trackbacks_addon() {
	// Register the product feature
	$this_addon  = it_cart_buddy_get_addon( 'wp-trackbacks' );
	$slug        = $this_addon['slug'];
	$description = $this_addon['description'];
	it_cart_buddy_register_product_feature( $slug, $description );

	// Add it to all enabled product-type addons
	$products = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
	foreach( $products as $key => $params ) {
		it_cart_buddy_add_feature_support_to_product_type( $this_addon['slug'], $params['slug'] );
	}
}
