<?php
/**
 * This file contains the CartBuddy Core Transaction Post Type and supporting hooks.
*/

/**
 * The Core CartBuddy Transaction Post Type Class
 * Uses the CartBuddy API to create the class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Transaction_Post_Type' ) ) {
	class IT_CartBuddy_Core_Transaction_Post_Type {
		
		/**
		 * Adds needed hooks
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Transaction_Post_Type() {
			add_action( 'init', array( $this, 'register_post_type' ), 1 );
		}

		/**
		 * Registers the post type via the CartBuddy API
		 *
		 * @since 0.1
		*/
		function register_post_type() {
            $options = array(
                'var'                => 'it_cb_transaction',
                'name'               => __( 'Transaction', 'LION' ),
                'name_plural'        => __( 'Transactions', 'LION' ),
                'settings'           => array(
                    'rewrite'             => array(
                        'slug' => 'cartbuddy-transaction',
                    ),
                    'supports'            => array( 'title', 'editor' ),
                    'exclude_from_search' => true,
                    'show_in_nav_menus'   => false,
                    'publicly_queryable'  => false,
                    'show_in_menu'        => 'edit.php?post_type=it_cb_product',
                )
            );
            cartbuddy( 'register_post_type', $options );
		}
	}
	new IT_CartBuddy_Core_Transaction_Post_Type();
}
