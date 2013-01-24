<?php
/**
 * This file registers the Product Categories Taxonomy via the CartBuddy API
*/

/**
 * Core CartBuddy Product Categories Taxonomy Class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_Core_Product_Category_Taxonomy' ) ) {
	class IT_Core_Product_Category_Taxonomy {

		/**
		 * Hooks for registering the taxonomy and integrating into admin menu / settings pages.
		 *
		 * @since 0.1
		*/
		function IT_Core_Product_Category_Taxonomy() {
			add_action( 'init', array( $this, 'register_taxonomy' ), 1 );	
		}

		/**
		 * Registers the taxonomy with CartBuddy and WP
		 *
		 * @since 0.1
		*/
		function register_taxonomy() {
            $options = array(
                'var' => 'it_cb_prod_cat',
                'name' => __( 'Product Category', 'LION' ),
                'name_plural' => __( 'Product Categories', 'LION' ),
                'settings' => array(
                    'hierarchical' => true,
                ),
            );
            $options = apply_filters( 'it_cartbuddy_core_product_cat_options', $options );
            cartbuddy( 'add_taxonomy', $options );
		}
	}
	new IT_Core_Product_Category_Taxonomy();
}
