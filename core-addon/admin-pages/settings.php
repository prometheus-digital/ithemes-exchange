<?php
/**
 * This file adds the Core Settings Page to CartBuddy
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Settings_Admin_Page' ) ) {

	class IT_CartBuddy_Core_Settings_Admin_Page {
		
		/**
		 * Constructor. Add's needed filters and actions to hooks
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Settings_Admin_Page() {
			add_action( 'init', array( $this, 'add_page_and_main_tab' ), 1 );
			add_action( 'it_cb_product_settings_after_mbs', array( $this, 'add_submit_button' ), 99, 2 );
		}

		/**
		 * Registers the Admin Menu page and the Main Tab for the settings page
		 *
		 * @since 0.1
		*/
		function add_page_and_main_tab() {
			$rel_url = plugins_url('css',dirname(__FILE__));
            $options = array(
                'var'        => 'settings',
                'page_title' => __( 'CartBuddy Settings', 'LION' ),
                'menu_title' => __( 'Settings', 'LION' ),
                'capability' => 'manage_options',
            );
            $options = apply_filters( 'it_cartbuddy_core_admin_settings_page_options', $options );
            cartbuddy( 'add_admin_page', $options );
		}

		/**
		 * Places the submit button at the bottom of the page
		 *
		*/
		function add_submit_button( $form ) {
			$form->add_submit( 'it_cb_product_settings_submit' );
		}
	}

	new IT_CartBuddy_Core_Settings_Admin_Page();
}
