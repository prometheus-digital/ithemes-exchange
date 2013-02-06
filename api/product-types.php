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
 * @todo Refactor this mess.
 * @since 0.3.1
 * @return string the product type
*/
function it_cart_buddy_get_product_type( $post=false ) {
	global $pagenow;
	$product_type = $post_type  = false;
	if ( $post && ! is_object( $post ) )
		$post = get_post( $post );

	if ( is_admin() ) {
		if ( ! $post )
			global $post;
		$post_type = empty( $post->post_type ) ? false : $post->post_type;
		$post_type = ( empty( $post_type ) && ! empty( $_GET['post_type'] ) ) ? $_GET['post_type'] : $post_type;
		if ( 'post-new.php' == $pagenow && 'it_cart_buddy_prod' == $post_type ) {
			// If we're in the admin on a new product page
			$product_type = empty( $_GET['product_type'] ) ? false : $_GET['product_type'];
		} else if ( is_admin() && 'post.php' == $pagenow && 'it_cart_buddy_prod' == $post_type ) {
			// If we're in the admin on an existing product page
			if ( 'it_cart_buddy_prod' == $post_type && ! empty( $post->ID ) )
				$product_type = get_post_meta( $post->ID, '_it_cart_buddy_product_type', true );
		}
	} else {
		if ( ! $post )
			global $post;

		if ( 'it_cart_buddy_prod' == $post->post_type )
			$product_type = get_post_meta( $post->ID, '_cart_buddy_product_type', 'true' );
	}

	if ( empty( $product_type ) )
		return false;

	return $product_type;

}
