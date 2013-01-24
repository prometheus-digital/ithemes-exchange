<?php
/**
 * This file registers the Product Tags Taxonomy via the CartBuddy API
*/

/**
 * Core CartBuddy Product Tags Taxonomy Class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_Core_Product_Tag_Taxonomy' ) ) {
	class IT_Core_Product_Tag_Taxonomy {

		/**
		 * Hooks for registering the taxonomy and integrating into admin menu / settings pages.
		 *
		 * @since 0.1
		*/
		function IT_Core_Product_Tag_Taxonomy() {
			add_action( 'init', array( $this, 'register_taxonomy' ), 1 );	
		}

		/**
		 * Registers the taxonomy with CartBuddy and WP
		 *
		 * @since 0.1
		*/
		function register_taxonomy() {
            $options = array(
                'var' => 'it_cb_prod_tag',
                'name' => __( 'Product Tag', 'LION' ),
                'name_plural' => __( 'Product Tags', 'LION' ),
                'disabled_by_default' => false,
                'settings' => array(
                    'hierarchical' => false,
                ),
            );
            $options = apply_filters( 'it_cartbuddy_core_product_tag_options', $options );
            cartbuddy( 'add_taxonomy', $options );
		}
	}
	new IT_Core_Product_Tag_Taxonomy();
}
