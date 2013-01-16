<?php
/**
 * This file contains all the functions needed for our General Settings Tab.
 * One could copy this file, add a plugin header to the top, and modify to add an additional admin page w/ tabs
*/
if ( ! class_exists( 'IT_CartBuddy_General_Settings' ) ) {
	class IT_CartBuddy_General_Settings {

		// Class constructor handles hooks
		function IT_CartBuddy_General_Settings() {
			add_filter( 'it_cartbuddy-admin_pages', array( $this, 'register_settings_page' ), -99 ); // Add-ons should set priority greater than 0
			add_filter( 'it_cartbuddy-admin_tabs', array( $this, 'register_settings_main_tab' ), -99 ); // Add-ons should set priority greater than 0
		}

		/**
		 * Adds the Settings Page if not already set
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_settings_page( $admin_pages ) {
			if ( ! empty( $admin_pages['general'] ) )
				return;

			// Add Settings Page to array
			$admin_pages['settings'] = array(
				'page_title' => __( 'CartBuddy Settings', 'LION' ),
				'menu_title' => __( 'Settings', 'LION' ),
				'capability' => 'manage_options',
			);

			return $admin_pages;
		}

		/**
		 * This registers the general settings tab for the general settings page
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_settings_main_tab( $tabs ) {
			$tabs['settings']['main'] = array(
				'title' => __( 'CartBuddy Settings', 'LION' ),
				'callback' => array( $this, 'print_main_tab_content' ),
			);
			return $tabs;
		}

		/**
		 * This adds the content to the main settings page
		 *
		 * @since 0.1
		*/
		function print_main_tab_content() {
			/*
			 * This is an iThemes class. It is located in /lib/classes/it-form.php
			 * Data is stored via the iThemes Storage object
			*/
			$form = new ITForm();
			$form->start_form( array( 'class' => 'validate' ), 'save-main-content' );
			?>
			<div id="js-error-response"></div>

            <table class="form-table">
                <tr class="form-required">
                    <th scope="row"><label for="product_taxonomies"><?php _e( 'Product Taxonomies', 'LION' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
                    <td>
						<?php
						// Taxonomies
						foreach ( $this->_product_taxonomies as $tax => $settings ) {
							
						}
						?>
					</td>
                </tr>
			</table>

            <p class="submit">
                <?php $form->add_submit( 'save', array( 'value' => __( 'Save Changes' ), 'class' => 'button-primary' ) ); ?>
            </p>
			<?php
			$form->end_form();
		}
	}
	$it_cartbuddy_general_settings = new IT_CartBuddy_General_Settings();
}
