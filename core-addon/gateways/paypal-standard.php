<?php
/**
 * This file registers the core PayPal Standard Gateway
 *
 * It uses the CartBuddy API to integrate into the plugin. Future addon-ons could copy this class and
 * rename / refactor to quickly add additional gateways to CartBuddy
*/

/**
 * Core CartBuddy PayPal Standard Gateway Class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_Core_PayPal_Standard_Gateway' ) ) {
	class IT_Core_Product_PayPal_Standard_Gateway {

		/**
		 * Hooks for registering the gateway and integrating into admin menu / settings pages.
		 *
		 * @since 0.1
		*/
		function IT_Core_Product_PayPal_Standard_Gateway() {
			// Register Gateway
			add_action( 'init', array( $this, 'register_gateway' ), 9 );	

			// Hook Metabox
			add_action( 'admin_init', array( $this, 'hook_meta_boxes' ), 1 );

            // Add submit button to settings page
            add_action( 'it_cb_product_gateways_paypal_standard_after_mbs', array( $this, 'add_submit_button' ), 99, 2 );
		}

		/**
		 * Registers the gateway with CartBuddy
		 *
		 * @since 0.1
		*/
		function register_gateway() {
            $options = array(
                'var'       => 'paypal_standard',
                'title'     => __( 'PayPal Standard', 'LION' ),
            );
            $options = apply_filters( 'it_cartbuddy_core_paypal_standard_gateway_options', $options );
            cartbuddy( 'add_gateway', $options );
		}

		/**
         * This is the callback function needed to register metaboxes for our settings page
		 *
		 * @since 0.1
		*/
		function hook_meta_boxes() {
			do_action( 'it_cartbuddy_core_before_hook_paypal_standard_settings_mb' );
            add_meta_box(
                'it_cb_paypal_standard_basic_settings',
                __( 'Basic Settings', 'LION' ),
                array( $this, 'print_basic_settings' ),
                'it_cb_product_gateways_paypal_standard'
            );
            add_meta_box(
                'it_cb_paypal_standard_advanced_settings',
                __( 'Advanced Settings', 'LION' ),
                array( $this, 'print_advanced_settings' ),
                'it_cb_product_gateways_paypal_standard'
            );
			do_action( 'it_cartbuddy_core_after_hook_paypal_standard_settings_mb' );
		}

		/**
		 * This prints the contents of the basic settings metabox we added in hook_meta_boxes()
		 *
		 * @since 0.1
		*/
		function print_basic_settings( $form ) {
            $settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_gateways_paypal_standard' ) );
            if ( ! empty( $settings ) ) { 
                foreach( (array) $settings as $key => $value ) { 
                    $form->set_option( $key, $value );
                }   
            }  
            ?>
            <table class="form-table">
                <?php do_action( 'it_cartbuddy_before_core_paypal_standard_basic_settings_rows', $form ); ?>
                <tr valign="top">
                    <td scope="row"><label for="paypal_standard_label"><?php _e( 'Gateway Title', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_box( 'paypal_standard_label' ); ?>
                        <br /><span class="description"><?php  _e( 'Title of the gateway displayed at checkout.', 'LION' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="paypal_standard_description"><?php _e( 'Gateway Description', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_area( 'paypal_standard_description' ); ?>
                        <br /><span class="description"><?php _e( 'Description of the gateway shown at checkout.', 'LION' ); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="paypal_standard_email"><?php _e( 'PayPal Email', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_box( 'paypal_standard_email' ); ?>
                        <br /><span class="description"><?php  _e( 'Email address associated with your PayPal account.', 'LION' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="paypal_standard_invoice_prefix"><?php _e( 'PayPal Invoice Prefix', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_box( 'paypal_standard_invoice_prefix' ); ?>
                        <br /><span class="description"><?php  _e( 'Prefix your PayPal invoices with this value.', 'LION' ); ?></span>
                    </td>
                </tr>
                <?php do_action( 'it_cartbuddy_after_core_paypal_standard_basic_settings_rows', $form ); ?>
            </table>
            <?php
		}

		/**
		 * This prints the contents of the advanced settings metabox we added in hook_meta_boxes()
		 *
		 * @since 0.1
		*/
		function print_advanced_settings( $form ) {
            ?>
            <table class="form-table">
                <?php do_action( 'it_cartbuddy_before_core_paypal_standard_advanced_settings_rows', $form ); ?>
                <tr valign="top">
                    <td scope="row"><?php _e( 'Enable Sandbox Mode', 'LION' ); ?></td>
                    <td>
                        <?php $form->add_check_box( 'paypal_standard_enable_sandbox' ); ?>
                         <span class="description"><?php  printf( __( 'You must have a %1$s to use this feature.', 'LION' ), '<a target="_blank" href="http://developer.paypal.com">PayPal Developer account</a>' ); ?></span>
                    </td>
                </tr>
                <?php do_action( 'it_cartbuddy_after_core_paypal_standard_advanced_settings_rows', $form ); ?>
            </table>
            <?php
		}

        /** 
         * Adds the submit button at the bottom of the page
         *
         * @since 0.1
        */
        function add_submit_button( $form ) { 
            $form->add_submit( 'it_cartbuddy_submit_paypal_standard_gateway_options' );
        }
	}
	new IT_Core_Product_PayPal_Standard_Gateway();
}
