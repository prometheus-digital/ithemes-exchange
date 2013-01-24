<?php
/**
 * This is an example of how to add metaboxes to an existing admin tab (Email Settings in this example)
 *
*/
if ( ! class_exists( 'IT_CartBuddy_Email_Settings_Tab_MBs' ) ) {
	class IT_CartBuddy_Email_Settings_Tab_MBs {
		
		/**
		 * Hooks to function that will register meta boxes with WordPress
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Email_Settings_Tab_MBs() {
			add_action( 'admin_init', array( $this, 'register_mbs' ), 1 );	
		}

		/**
		 * Register's the Core meta-boxes for the Email Settings tab.
		 *
		 * @since 0.1
		*/
		function register_mbs() {
			// Add Meta-box for Email Template to Admin
            add_meta_box( 
				'it_cb_product_email_settings_admin',           // CSS ID for metabox
				__( 'New Purchase Email to Admin', 'LION' ), // Metabox Title
				array( $this, 'new_purchase_to_admin' ),        // Callback for content of metabox 
				'it_cb_product_settings_email'                  // [post type]_[page var]_[current_tab var] <-- Must match URL args in admin
			);

			// Add Meta-box for Email Template to Customer
            add_meta_box( 
				'it_cb_product_email_settings_customer',        // CSS ID for metabox
				__( 'New Purchase Email to Customer', 'LION' ), // Metabox Title
				array( $this, 'new_purchase_to_customer' ),     // Callback for content of metabox 
				'it_cb_product_settings_email'                  // [post type]_[page var]_[current_tab var] <-- Must match URL args in admin
			);

			// Add Meta-box for Email Template Variables
            add_meta_box( 
				'it_cb_product_email_template_vars',            // CSS ID for metabox
				__( 'Available Email Variables', 'LION' ),      // Metabox Title
				array( $this, 'print_email_template_vars' ),    // Callback for content of metabox 
				'it_cb_product_settings_email'                  // [post type]_[page var]_[current_tab var] <-- Must match URL args in admin
			);
		}

        /**
         * Email sent to admin after a new purchase
         *
		 * @param object $form the ITForm object passed to all metaboxes on this tab
         * @since 0.1
        */
        function new_purchase_to_admin( $form ) {

            // Set current values using ITStorage2 and ITForm
            $settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_settings_email' ) );
            if ( ! empty( $settings['it_cb_product_email_template_new_purchase_to_admin'] ) ) 
                $form->set_option( 'it_cb_product_email_template_new_purchase_to_admin', $settings['it_cb_product_email_template_new_purchase_to_admin'] );
            ?>
            <p class="description">
                <?php _e( 'This text will be used as a template to email the site administrator when new purchases are completed', 'LION' ); ?>
            </p>
            <?php
            $form->add_text_area( 'it_cb_product_email_template_new_purchase_to_admin' );
        }

        /**
         * Email sent to customer after a new purchase
         *
		 * @param object $form the ITForm object passed to all metaboxes on this tab
         * @since 0.1
        */
        function new_purchase_to_customer( $form ) {
            $settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_settings_email' ) );
            if ( ! empty( $settings['it_cb_product_email_template_new_purchase_to_customer'] ) ) 
                $form->set_option( 'it_cb_product_email_template_new_purchase_to_customer', $settings['it_cb_product_email_template_new_purchase_to_customer'] );
            ?>
            <p class="description">
                <?php _e( 'This text will be used as a template to email the Customer when new purchases are completed', 'LION' ); ?>
            </p>
            <?php
            $form->add_text_area( 'it_cb_product_email_template_new_purchase_to_customer' );
        }

        /**
         * Prints variables reference for email templates
         *
		 * @param object $form the ITForm object passed to all metaboxes on this tab
         * @since 0.1
        */
        function print_email_template_vars( $form ) {
            $tags = array(
                'customer_name'        => __( 'First and Last name of customer', 'LION' ),
				'customer_email'       => __( 'Email provided by customer at time of purchase', 'LION' ), 
				'transaction_date'     => __( 'The date the purchase was made', 'LION' ),
				'transaction_amount'   => __( 'The final transaction amount', 'LION' ),
				'transaction_currency' => __( 'The 3 character abbriviation for the currency used in the transaction', 'LION' ),
            );
			$tags = apply_filters( 'it_cartbuddy_email_template_tags', $tags );
			?>
            <p class="description">
                <?php _e( 'Use the following Tags to insert variable information into your email templates', 'LION' ); ?>
            </p>

			<table class="form-table">
			<?php
            foreach( $tags as $tag => $description ) { 
				?>
				<tr valign="top">
					<td scope="row"><code>%%<?php esc_attr_e( $tag ); ?>%%</code></td>
					<td><?php echo $description; ?></td>
				</tr>
				<?php
            }   
			echo '</table>';
        }
	}
	new IT_CartBuddy_Email_Settings_Tab_MBs();
}
