<?php
/**
 * This add-on will enable the author metabox on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_wp_author_addon' );
add_filter( 'it_cart_buddy_get_product_feature-wp-author', 'it_cart_buddy_wp_author_addon_get_wp_author', 9, 2 );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_wp_author_addon() {
    // Register the product feature
    $this_addon  = it_cart_buddy_get_addon( 'wp-author' );
    $slug        = $this_addon['slug'];
    $description = $this_addon['description'];
    it_cart_buddy_register_product_feature( $slug, $description );

    // Add it to all enabled product-type addons
    $products = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
    foreach( $products as $key => $params ) { 
        it_cart_buddy_add_feature_support_to_product_type( $this_addon['slug'], $params['slug'] );
    }
}

/**
 * Return the product's wp_author
 *
 * This returns the image, not the ID
 *
 * @since 0.3.8
 * @param mixed $wp_author the values passed in by the WP Filter API. Ignored here.
 * @param integer product_id the WordPress post ID
 * @return string author
*/
function it_cart_buddy_wp_author_addon_get_wp_author( $wp_author, $product_id ) {
	$product = it_cart_buddy_get_product( $product_id );
	if ( empty( $product->post_author ) )
		return;

	if ( $author = get_the_author_meta( 'display_name', $product->post_author ) )
		return $author;

	return false;
}
