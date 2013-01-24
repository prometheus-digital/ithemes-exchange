<?php
/**
 * The core-addon folder serves two purposes:
 *   - It uses the CartBuddy Framework to init core features of the plugin
 *   - It is an example for future addons that want to use the CartBuddy Framework to extend its functionality.
*/

if ( ! class_exists( 'IT_CartBuddy_Core_Addon' ) ) {
	class IT_CartBuddy_Core_Addon {
		
		/**
		 * Inits the Core features via the CartBuddy API
		 *
		 * This file structure has been organized in a manner to help third-party Add-on developers learn
		 * how to better interact with the API. The various files, classes, and hooks could have easily been
		 * contained within one file. They were split up to assit Add-on developers needing to extend a specific
		 * aspect of the CartBuddy plugin. The only Class that should not be used as a reference is the 
		 * post-types/product.php class. It is unique as it serves as the primary post_type / menu item for the plugin.
		 *
		 * If you need to extend a specific aspect of the plugin, see the following files for assistance
		 * =============================================================================================
		 *   - A new post_type, placed under the CartBuddy admin menu  | post-types/transaction.php
		 *   - A new post_type, not visible in the admin menu          | post-types/cart.php
		 *   - A new taxonomy for products post_type                   | taxonomies/categories.php or taxonomies/tags.php
		 *   - A new gateway (creates tab & settings checkbox)         | gateways/manual-payments.php, paypal-standard.php, stripe.php
		 *   - A new admin page under CartBuddy (as well as main tab)  | admin-pages/settings.php, admin-pages/add-ons.php
		 *   - A new tab to an existing admin page (but not gateways)  | admin-tabs/settings-email.php
		 *   - A new meta-box to the Add/Edit product view             | meta-boxes/products/pricing.php
		 *   - A new meta-box to an existing admin tab                 | meta-boxes/admin-pages/settings.php (shows multiple mbs)
		 * 
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Addon() {

			// Register Post Types 
			include_once( 'post-types/product.php' );
			include_once( 'post-types/transaction.php' );
			include_once( 'post-types/cart.php' );

			// Register Taxonomies
			include_once( 'taxonomies/categories.php' );
			include_once( 'taxonomies/tags.php' );

			// Register Gateways
			include_once( 'gateways/manual-payments.php' );
			include_once( 'gateways/paypal-standard.php' );
			include_once( 'gateways/stripe.php' );

			if ( is_admin() ) {
				// Include Core Admin Page Classes
				include_once( 'admin-pages/settings.php' );
				include_once( 'admin-pages/gateways.php' );
				include_once( 'admin-pages/add-ons.php' );

				// Additional Admin Page Tabs (not gateways though)
				include_once( 'admin-tabs/settings-email.php' );

				// Admin Page Meta Boxes
				include_once( 'meta-boxes/products/pricing.php' );
				include_once( 'meta-boxes/admin-pages/settings.php' );
				include_once( 'meta-boxes/admin-pages/settings-email.php' );
				include_once( 'meta-boxes/admin-pages/gateways.php' );

				// Add content to core admin pages
				add_action( 'admin_init', array( $this, 'hook_core_admin_page_content' ), 1 );
			}
		}

		/**
		 * Add core settings to admin pages
		 *
		 * @since 0.1
		*/
		function hook_core_admin_page_content() {

			// Add intro and quick links to settings page
			add_action( 'it_cb_product_settings_before_mbs', array( $this, 'print_settings_intro' ), 9 );

			// Add intro and quick links to email settings page
			add_action( 'it_cb_product_settings_email_before_mbs', array( $this, 'print_settings_intro' ), 9 );

			// Add intro to gateways main tab
			add_action( 'it_cb_product_gateways_before_mbs', array( $this, 'print_settings_intro' ), 9 );
		}

		/**
		 * Prints the intro paragraph on the settings page
		 *
		 * @since 0.1
		*/
		function print_settings_intro() {
			echo '<p>' . __( 'For information about this page, please click the "Help" button at the top right.', 'LION' ) . '</p>';
		}
	}
	$it_cartbuddy_core_addon = new IT_CartBuddy_Core_Addon();
}
