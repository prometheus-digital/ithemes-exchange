<?php
/**
 * This file demonstrates how to add an additional tab to an existing admin page. It also does it for reals.
 *
 * @since 0.1
 * @package IT_CartBuddy
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Email_Settings_Tab' ) ) {
	class IT_CartBuddy_Core_Email_Settings_Tab {
		/**
		 * Hooks the function that registers the tab with CartBuddy
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Core_Email_Settings_Tab() {
			add_action( 'init', array( $this, 'add_email_settings_tab' ), 1 ); // Addons: please use priority higher than 1
			add_action( 'it_cb_product_settings_email_after_mbs', array( $this, 'add_submit_button' ), 99, 2 );
		}

		/**
		 * Registers the tab with CartBuddy
		 *
		 * @since 0.1
		*/
		function add_email_settings_tab() {
			$rel_url = plugins_url('css',dirname(__FILE__));
            $options = array(
                'var'        => 'email',
                'parent_var' => 'settings',
                'title'      => 'Email Settings',
				'enqueued_styles' => array(
					array( 'handle' => 'email-settings', 'src' => $rel_url . '/settings-email.css' ),
				),
            );  
            $options = apply_filters( 'it_cartbuddy_core_admin_settings_email_tab_options', $options );
            cartbuddy( 'add_admin_tab', $options );
		}

		/**
		 * Adds submit button
		 *
		*/
		function add_submit_button( $form ) {
			$form->add_submit( 'it_cartbuddy_submit_email_settings' );
		}
	}
	new IT_CartBuddy_Core_Email_Settings_Tab();
}
