<?php
/**
 * This is an example of how to add metaboxes to an existing admin tab (Settings in this example)
 *
*/
if ( ! class_exists( 'IT_CartBuddy_Settings_Tab_MBs' ) ) {
	class IT_CartBuddy_Settings_Tab_MBs {
		
		/**
		 * Hooks to function that will register meta boxes with WordPress
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Settings_Tab_MBs() {
			add_action( 'admin_init', array( $this, 'register_mbs' ), 2 );	
		}

		/**
		 * Register's the Core meta-boxes for the main Settings tab.
		 *
		 * @since 0.1
		*/
		function register_mbs() {
			// Add Currency options to settings page
            add_meta_box( 
				'it_cb_product_settings-currency',         // CSS ID for metabox
				__( 'Currency', 'LION' ),                  // Metabox Title
				array( $this, 'print_currency_settings' ), // Callback for content of metabox 
				'it_cb_product_settings'                  // [post type]_[page var] <-- Must match URL args in admin
			);

            // Add Taxonomy options to settings page
            add_meta_box( 
				'it_cb_product_settings-taxonomies',       // CSS ID for metabox 
				__( 'Enabled Taxonomies', 'LION' ),        // Metabox Title
				array( $this, 'print_taxonomy_settings' ), // Callback for content of metabox
				'it_cb_product_settings'                   // [post type]_[page var] <-- Must match URL args in admin
			);
		}

        /**
         * Prints the currency setting
         *
		 * @param object $form the ITForm object passed to all metaboxes on this tab
         * @todo Make currencies table
         * @since 0.1
        */
        function print_currency_settings( $form ) {

			// Set current values using ITStorage2 and ITForm
			$settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_settings' ) );
			if ( ! empty( $settings['it_cartbuddy_currency'] ) )
				$form->set_option('it_cartbuddy_currency', $settings['it_cartbuddy_currency'] );

            $currencies = apply_filters(
                'it_cb_product_get_currencies',
                array(
                    '0'   => __( 'Select a Currency', 'LION' ),
                    'usd' => 'USD',
                    'cad' => 'CAD',
                    'eur' => 'EUR' )
            );
            ?>
            <p class="description"><?php printf( __( 'What currency will %1$s use for your products?', 'LION' ), 'CartBuddy' ); ?></p>
            <p>
                <?php $form->add_drop_down( 'it_cartbuddy_currency', $currencies ); ?>
            </p>
            <?php
        }

        /**
         * This prints the taxonomy options on the settings page
         *
		 * @param object $form the ITForm object passed to all metaboxes on this tab
         * @since 0.1
        */
        function print_taxonomy_settings( $form ) {

			// Set current values using ITStorage2 and ITForm
			$settings = cartbuddy( 'get_options', array( 'var' => 'it_cb_product_settings' ) );
			if ( ! empty( $settings['it_cb_product_enable_taxonomy'] ) )
				$form->set_option( 'it_cb_product_enable_taxonomy', $settings['it_cb_product_enable_taxonomy'] );

			// Print Taxonomy fields
            if ( $taxonomies = cartbuddy( 'get_taxonomies' ) ) {
                ?>
                <p class="description">
                    <?php printf( __( 'Check any taxonomies that you would like to enable for %1$s products', 'LION' ), 'CartBuddy' ); ?>
                </p>
                <?php
                foreach( $taxonomies as $tax ) {
                    $options = array( 'value' => $tax->query_var );
                    echo '<label for="it_cb_product_enable_taxonomy-' . $tax->query_var . '">';
                    $form->add_multi_check_box( 'it_cb_product_enable_taxonomy', $options );
                    echo ' ' . $tax->labels->name . '</label><br />';
                }
            }
        }
	}
	new IT_CartBuddy_Settings_Tab_MBs();
}
