<?php
/**
 * This file adds the Core Add-ons Page to CartBuddy
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Addons_Admin_Page' ) ) {

	class IT_CartBuddy_Core_Addons_Admin_Page {
		
		/**
		 * Constructor. Add's needed filters and actions to hooks
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Addons_Admin_Page() {
			add_action( 'init', array( $this, 'add_page_and_main_tab' ), 1 );
		}

		/**
		 * Registers the Admin Menu page and the Main Tab for the add-ons page
		 *
		 * @since 0.1
		*/
		function add_page_and_main_tab() {
            $options = array(
                'var'        => 'addons',
                'page_title' => __( 'CartBuddy Add-ons', 'LION' ),
                'menu_title' => __( 'Add-ons', 'LION' ),
                'capability' => 'manage_options',
            );
            $options = apply_filters( 'it_cartbuddy_core_admin_addons_page_options', $options );
            cartbuddy( 'add_admin_page', $options );
		}
	}

	new IT_CartBuddy_Core_Addons_Admin_Page();
}
