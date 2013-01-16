<?php
/**
 * This file contains all the functions needed for our core addons page and tabs.
 * One could copy this file, add a plugin header to the top, and modify to add an additional admin page w/ tabs
*/
if ( ! class_exists( 'IT_CartBuddy_Addons_Admin_Page' ) ) {
	class IT_CartBuddy_Addons_Admin_Page {

		// Class constructor handles hooks
		function IT_CartBuddy_Addons_Admin_Page() {
			add_filter( 'it_cartbuddy-admin_pages', array( $this, 'register_page' ), -99 ); // Add-ons should set priority greater than 0
			add_filter( 'it_cartbuddy-admin_tabs', array( $this, 'register_main_tab' ), -99 ); // Add-ons should set priority greater than 0
		}

		/**
		 * Adds the Add-ons Page if not already set
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_page( $admin_pages ) {
			if ( ! empty( $admin_pages['addons'] ) )
				return;

			// Add Add-ons Page to array
			$admin_pages['addons'] = array(
				'page_title' => __( 'CartBuddy Add-ons', 'LION' ),
				'menu_title' => __( 'Add-ons', 'LION' ),
				'capability' => 'manage_options',
			);

			return $admin_pages;
		}

		/**
		 * This registers the main tab for the addons page
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_main_tab( $tabs ) {
			$tabs['addons']['main'] = array(
				'title' => __( 'CartBuddy Add-ons', 'LION' ),
				'callback' => array( $this, 'print_main_tab_content' ),
			);
			return $tabs;
		}

		/**
		 * This adds the content to the Add-ons main tab
		 *
		 * @since 0.1
		*/
		function print_main_tab_content() {
			echo "<p>In the Main Add-ons Tab!</p>";
		}
	}
	$it_cartbuddy_addons_admin_page = new IT_CartBuddy_Addons_Admin_Page();
}
