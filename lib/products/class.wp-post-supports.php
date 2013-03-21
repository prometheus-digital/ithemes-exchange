<?php
/**
 * This add-on will enable the product title (post title ) box on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

class IT_Cart_Buddy_WP_Post_Supports {

	/**
	 * Constructor. Loads hooks for various post supports
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function IT_Cart_Buddy_WP_Post_Supports() {

		// WordPress Post Title
		add_action( 'it_cart_buddy_enabled_addons_loaded', array( $this, 'add_title_support_to_products' ) );
		add_filter( 'it_cart_buddy_get_product_feature-product_title', array( $this, 'get_title' ), 9, 2 );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	*/
	function add_title_support_to_products() {
		// Register the product feature
		$slug        = 'product-title';
		$description = __( 'Adds support for default WordPress Title field', 'LION' );
		it_cart_buddy_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$product_types = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $product_types as $key => $product_type ) { 
			it_cart_buddy_add_feature_support_to_product_type( $slug, $product_type['slug'] );
		}   
	}

	/**
	 * Return the product's title
	 *
	 * @since 0.3.8
	 * @param mixed $title the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string post_title
	*/
	function get_title( $title, $product_id ) { 
		return get_the_title( $product_id );
	}
}
$IT_Cart_Buddy_WP_Post_Supports = new IT_Cart_Buddy_WP_Post_Supports();
