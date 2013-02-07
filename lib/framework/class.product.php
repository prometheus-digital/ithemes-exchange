<?php
/**
 * This file holds the class for a Cart Buddy Product
 *
 * @package IT_Cart_Buddy
 * @since 0.3.2
*/

/**
 * Merges a WP Post with Cart Buddy Product data
 *
 * @since 0.3.2
*/
class IT_Cart_Buddy_Product {

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param string $product_type The product type for this product
	 * @since 0.3.2
	*/
	var $product_type;


	/**
	 * @param array $product_data  any custom data registered by the product-type for this product
	 * @since 0.3.2
	*/
	var $product_data = array();

	/**
	 * Constructor. Loads post data and product data
	 *
	 * @since 0.3.2
	 * @param mixed $post  wp post id or post object. optional.
	 * @return void
	*/
	function IT_Cart_Buddy_Product( $post=false ) {
		
		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && 'WP_Post' != get_class( $post ) )
			$post = false;

		// Ensure this is a product post type
		if ( 'it_cart_buddy_prod' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			return new WP_Error( 'it-cart-buddy-product-not-a-wp-post', __( 'The IT_Cart_Buddy_Product class must have a WP post object or ID passed to its constructor', 'LION' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set the product type
		$this->set_product_type();

		// Set the product data
		if ( did_action( 'init' ) )
			$this->set_product_data();
		else
			add_action( 'init', array( $this, 'set_product_data' ) );

	}

	/**
	 * Sets the product_type property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.3.2
	*/
	function set_product_type() {
		global $pagenow;
		if ( ! $product_type = get_post_meta( $this->ID, '_it_cart_buddy_product_type', true ) ) {
			if ( is_admin() && 'post-new.php' == $pagenow && ! empty( $_GET['product_type'] ) )	
				$product_type = $_GET['product_type'];		
		}
		$this->product_type = $product_type;
	}

	/**
	 * Sets the product_data property from appropriate product-type options and assoicated post_meta
	 *
	 * @ since 0.3.2
	 * @return void
	*/
	function set_product_data() {
		// Get product-type options
		if ( $product_type_options = it_cart_buddy_get_product_type_options( $this->product_type ) ) {
			if ( ! empty( $product_type_options['default_meta'] ) ) {
				foreach( $product_type_options['default_meta'] as $key => $default ) {
					$stored = get_post_meta( $this->ID, $key, true );
					$this->product_data[$key] = $stored ? $stored : $default;
				}
			}
		}
	}
}
