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
	 * @param array $product_supports features that this product supports along with defaults
	 * @since 0.3.3
	*/
	var $product_supports;

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
			$this->set_product_supports_and_data();
		else
			add_action( 'init', array( $this, 'set_product_supports_and_data' ) );


		// Set supports for new and edit screens
		if ( did_action( 'admin_init' ) )
			$this->set_add_edit_screen_supports();
		else
			add_action( 'admin_init', array( $this, 'set_add_edit_screen_supports' ) );
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
	function set_product_supports_and_data() {
		// Get product-type options
		if ( $product_type_options = it_cart_buddy_get_product_type_options( $this->product_type ) ) {
			if ( ! empty( $product_type_options['supports'] ) ) {
				foreach( $product_type_options['supports'] as $feature => $params ) {

					// Set the product_supports array
					$this->product_supports[$feature] = $params;

					// product_data only contains post_meta data
					if ( 'post_meta' != $params['componant'] )
						continue;

					// Set product_data to post_meta value or feature devault
					if ( $value = get_post_meta( $this->ID, $params['key'], true ) )
						$this->product_data[$params['key']] = $value;
					else
						$this->product_data[$params['key']] = $this->product_supports[$feature]['default'];
				}
			}
		}
	}

    /** 
     * Sets the supports array for the post_type.
     *
     * @since 0.3.3
    */
    function set_add_edit_screen_supports() {
		global $pagenow;
        $supports = array(
            'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
            'comments', 'revisions', 'post-formats',
        );  

        // If is_admin and is post-new.php or post.php, only register supports for current product-type
        if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return; // Don't remove any if not on post-new / or post.php

		if ( $addon = it_cart_buddy_get_add_on( $this->product_type ) ) { 
			// Remove any supports args that the product add-on does not want.
			foreach( $supports as $option ) { 
                if ( empty( $addon['options']['supports'][$option] ) )
					remove_post_type_support( 'it_cart_buddy_prod', $option );
            }   
        }   
    }  
}
