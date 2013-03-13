<?php
/**
 * This add-on will enable the product description (post content) box on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_product_description_addon' );
add_action( 'it_cart_buddy_update_product_feature-product_description', 'it_cart_buddy_product_description_addon_save_description', 9, 2 );
add_filter( 'it_cart_buddy_get_product_feature-product_description', 'it_cart_buddy_product_description_addon_get_description', 9, 2 );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_product_description_addon() {
    // Register the product feature
    $this_addon  = it_cart_buddy_get_addon( 'product-description' );
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
 * Return the product's description
 *
 * @since 0.3.8
 * @param mixed $description the values passed in by the WP Filter API. Ignored here.
 * @param integer product_id the WordPress post ID
 * @return string post_content (product descritpion) 
*/
function it_cart_buddy_product_description_addon_get_description( $description, $product_id ) {
	return get_the_content( $product_id );
}
