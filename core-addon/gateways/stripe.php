<?php
/**
 * This file registers the core Stripe Gateway
 *
 * It uses the CartBuddy API to integrate into the plugin. Future addon-ons could copy this class and
 * rename / refactor to quickly add additional gateways to CartBuddy
*/

/**
 * Core CartBuddy Stripe Gateway Class
 *
 * @since 0.1
*/
if ( ! class_exists( 'IT_Core_Stripe_Gateway' ) ) {
	class IT_Core_Product_Stripe_Gateway {

		/**
		 * Hooks for registering the gateway and integrating into admin menu / settings pages.
		 *
		 * @since 0.1
		*/
		function IT_Core_Product_Stripe_Gateway() {
			// Register Gateway
			add_action( 'init', array( $this, 'register_gateway' ), 9 );	

            // Hook Metabox
            add_action( 'admin_init', array( $this, 'hook_meta_boxes' ), 1 );

            // Add submit button to settings page
            add_action( 'it_cb_product_gateways_stripe_after_mbs', array( $this, 'add_submit_button' ), 99, 2 );
		}

		/**
		 * Registers the gateway with CartBuddy
		 *
		 * @since 0.1
		*/
		function register_gateway() {
            $options = array(
                'var'       => 'stripe',
                'title'     => __( 'Stripe', 'LION' ),
            );
            $options = apply_filters( 'it_cartbuddy_core_stripe_gateway_options', $options );
            cartbuddy( 'add_gateway', $options );
		}

		/**
         * This is the callback function needed to register metaboxes for our settings page
		 *
		 * @since 0.1
		*/
		function hook_meta_boxes() {
			do_action( 'it_cartbuddy_core_before_hook_stripe_settings_mb' );
            add_meta_box(
                'it_cb_stripe_basic_settings',
                __( 'Basic Settings', 'LION' ),
                array( $this, 'print_basic_settings' ),
                'it_cb_product_gateways_stripe'
            );
			do_action( 'it_cartbuddy_core_after_hook_stripe_settings_mb' );
		}

		/**
		 * This prints the contents of the metabox we added in hook_meta_boxes()
		 *
		 * @since 0.1
		*/
		function print_basic_settings( $form ) {
            $settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_gateways_stripe' ) );
            if ( ! empty( $settings ) ) { 
                foreach( (array) $settings as $key => $value ) { 
                    $form->set_option( $key, $value );
                }   
            }   
            ?>
            <table class="form-table">
                <?php do_action( 'it_cartbuddy_before_core_stripe_basic_settings_rows', $form ); ?>
                <tr valign="top">
                    <td scope="row"><label for="manual_payments_label"><?php _e( 'Gateway Title', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_box( 'manual_payments_label' ); ?>
                        <br /><span class="description"><?php  _e( 'Title of the gateway displayed at checkout.', 'LION' ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="manual_payments_description"><?php _e( 'Gateway Description', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_area( 'manual_payments_description' ); ?>
                        <br /><span class="description"><?php _e( 'Description of the gateway shown at checkout.', 'LION' ); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="manual_payments_instructions"><?php _e( 'Gateway Instructions', 'LION' ); ?></label></td>
                    <td>
                        <?php $form->add_text_area( 'manual_payments_instructions' ); ?>
                        <br /><span class="description"><?php _e( 'Payment instructions shown at checkout.', 'LION' ); ?>
                    </td>
                </tr>
                <?php do_action( 'it_cartbuddy_after_core_stripe_basic_settings_rows', $form ); ?>
            </table>
            <?php
		}

        /**
         * Adds the submit button at the bottom of the page
         *
         * @since 0.1
        */
        function add_submit_button( $form ) {
            $form->add_submit( 'it_cartbuddy_submit_stripe_gateway_options' );
        }
	}
	new IT_Core_Product_Stripe_Gateway();
}
