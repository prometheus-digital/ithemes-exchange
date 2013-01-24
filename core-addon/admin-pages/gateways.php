<?php
/**
 * This file adds the Core Gateways Page to CartBuddy
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Gateways_Admin_Page' ) ) {

	class IT_CartBuddy_Core_Gateways_Admin_Page {
		
		/**
		 * Constructor. Add's needed filters and actions to hooks
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Gateways_Admin_Page() {
			add_action( 'init', array( $this, 'add_page_and_main_tab' ), 1 );
			add_action( 'it_cb_product_gateways_after_mbs', array( $this, 'add_submit_button' ), 99, 2 );
		}

		/**
		 * Registers the Admin Menu page and the Main Tab for the gateways page
		 *
		 * @since 0.1
		*/
		function add_page_and_main_tab() {
            $options = array(
                'var'        => 'gateways',
                'page_title' => __( 'CartBuddy Gateways', 'LION' ),
                'menu_title' => __( 'Gateways', 'LION' ),
                'capability' => 'manage_options',
                'quicklinks' => false,
            );
            $options = apply_filters( 'it_cartbuddy_core_admin_gateways_page_options', $options );
            cartbuddy( 'add_admin_page', $options );
		}

        /** 
         * Places the submit button at the bottom of the page
         *
        */
        function add_submit_button( $form ) { 
            $form->add_submit( 'it_cb_product_gateways_submit' );
        }  
	}

	new IT_CartBuddy_Core_Gateways_Admin_Page();
}
