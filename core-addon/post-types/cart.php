<?php
/**
 * This file contains the CartBuddy Core Cart Post Type and supporting hooks.
 * If an add-on developer is looking for code to assist in creation of a new post type, use the transaction.php or cart.php
*/

/**
 * The Core CartBuddy Cart Post Type Class
 * Uses the CartBuddy API to create the class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Cart_Post_Type' ) ) {
	class IT_CartBuddy_Core_Cart_Post_Type {
		
		/**
		 * Adds needed hooks
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Cart_Post_Type() {
			add_action( 'init', array( $this, 'register_post_type' ), 1 );
		}

		/**
		 * Registers the post type via the CartBuddy API
		 *
		 * @since 0.1
		*/
		function register_post_type() {
            $options = array(
                'var'         => 'it_cb_cart',
                'name'        => __( 'Cart', 'LION' ),
                'name_plural' => __( 'Carts', 'LION' ),
                'settings'    => array(
                    'rewrite'             => array(
                        'slug' => 'cartbuddy-cart',
                    ),
                    'supports'            => array( 'title', 'editor' ),
                    'exclude_from_search' => true,
                    'show_in_nav_menus'   => false,
                    'publicly_queryable'  => false,
                    'show_in_menu'        => false,
                )
            );
            cartbuddy( 'register_post_type', $options );
		}
	}
	new IT_CartBuddy_Core_Cart_Post_Type();
}
