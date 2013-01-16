<?php
/**
 * This file contains all the functions needed for our core gateways page and tabs.
 * One could copy this file, add a plugin header to the top, and modify to add an additional admin page w/ tabs
*/
if ( ! class_exists( 'IT_CartBuddy_Gateways_Admin_Page' ) ) {
	class IT_CartBuddy_Gateways_Admin_Page {

		// Class constructor handles hooks
		function IT_CartBuddy_Gateways_Admin_Page() {
			add_filter( 'it_cartbuddy-admin_pages', array( $this, 'register_page' ), -99 ); // Add-ons should set priority greater than 0
			add_filter( 'it_cartbuddy-admin_tabs', array( $this, 'register_main_tab' ), -99 ); // Add-ons should set priority greater than 0
		}

		/**
		 * Adds the Gateways Page if not already set
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_page( $admin_pages ) {
			if ( ! empty( $admin_pages['gateways'] ) )
				return;

			// Add Gateways Page to array
			$admin_pages['gateways'] = array(
				'page_title' => __( 'CartBuddy Gateways', 'LION' ),
				'menu_title' => __( 'Gateways', 'LION' ),
				'capability' => 'manage_options',
			);

			return $admin_pages;
		}

		/**
		 * This registers the main tab for the gateways page
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_main_tab( $tabs ) {
			$tabs['gateways']['main'] = array(
				'title' => __( 'CartBuddy Gateways', 'LION' ),
				'callback' => array( $this, 'print_main_tab_content' ),
			);
			return $tabs;
		}

		/**
		 * This adds the content to the Gateways main tab
		 *
		 * @since 0.1
		*/
		function print_main_tab_content() {
			echo "<p>In the Main Gateways Tab!</p>";
		}
	}
	$it_cartbuddy_gateways_admin_page = new IT_CartBuddy_Gateways_Admin_Page();
}
