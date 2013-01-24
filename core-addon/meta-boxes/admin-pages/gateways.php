<?php
/**
 * This is an example of how to add metaboxes to an existing admin tab (Gateways in this example)
 *
*/
if ( ! class_exists( 'IT_CartBuddy_Gateways_Tab_MBs' ) ) {
	class IT_CartBuddy_Gateways_Tab_MBs {
		
		/**
		 * Hooks to function that will register meta boxes with WordPress
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Gateways_Tab_MBs() {
            add_action( 'admin_init', array( $this, 'register_mbs' ), 2 ); 
		}

		/**
		 * Register's the Core meta-boxes for the main Gateways tab.
		 *
		 * @since 0.1
		*/
		function register_mbs() {
			// Print List of installed Gateways
			add_meta_box(
				'it_cb_product_gateways_installed_gateways', // CSS ID for metabox
				__( 'Installed Gateways', 'LION' ),          // Metabox Title
				array( $this, 'print_installed_gateways' ),  // Callback for content of metabox 
				'it_cb_product_gateways'                     // [post type]_[page var] <-- Must match URL args in admin
			);
		}

        /** 
         * Prints the installed gateways
         *
		 * @since 0.1
		 * @param object $form the ITForm object passed to all metaboxes on this tab
        */
        function print_installed_gateways( $form ) { 
            if ( $installed = cartbuddy( 'get_gateways' ) ) { 
                if ( ! is_wp_error( $installed ) ) { 

					// Set current values using ITStorage2 and ITForm
					$settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_gateways' ) );
					if ( ! empty( $settings['it_cb_product_enable_gateway'] ) ) {
						$form->set_option( 'it_cb_product_enable_gateway', $settings['it_cb_product_enable_gateway'] );
					}

                    ?><p class="description"><?php _e( 'The following Gateway Add-ons are currently installed. Only checked Gateways will be available to your customers.', 'LION' ); ?></p><?php
                    echo '<ul id="installed_gateways">';
                    foreach( (array) $installed as $gateway ) { 
						echo '<label for="it_cb_product_enable_gateway-' . $gateway['var'] . '">';
                        $form->add_multi_check_box( 'it_cb_product_enable_gateway', array( 'value' => $gateway['var'] ) ); echo ' ' . $gateway['title'];
						echo '</label><br />';
                    }   
                    echo '</ul>';
                } else {
                    echo '<p class="description">' . __( 'You do not currently have any Gateways installed.', 'LION' ) . '</p>';
                }   
            }   

        }
	}
	new IT_CartBuddy_Gateways_Tab_MBs();
}
