<?php
/**
 * Callback function for add-on settings
 *
 * We are using this differently than most add-ons. We want the gear
 * to appear on the add-ons screen so we are registering the callback.
 * It will be intercepted though if the user clicks on it and redirected to 
 * The Exchange settings --> shipping tab.
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_simple_shipping_settings_callback() {
	// Store Owners should never arrive here. Add a link just in case the do somehow
	?>
	<div class="wrap">
		<?php screen_icon( 'it-exchange' ); ?>
		<h2><?php _e( 'Shipping', 'LION' ); ?></h2>
		<?php
		$url = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), esc_url( admin_url( 'admin.php' ) ) );
		?><p><?php printf( __( 'Settings are located in the %sShipping tab%s on the Exchange Settings page.', 'LION' ), '<a href="' . $url . '">', '</a>' ); ?></p>
	</div>
	<?php
}

/**
 * Redirects to General Settings -> Shipping -> Simple Shipping from add-on settings page.
 *
 * @since CHANGEME
 *
 * return void
*/
function it_exchange_simple_shipping_settings_redirect() {
	$page  = ! empty( $_GET['page'] ) && 'it-exchange-addons' == $_GET['page'];
	$addon = ! empty( $_GET['add-on-settings'] ) && 'simple-shipping' == $_GET['add-on-settings'];

	if ( $page && $addon ) {
		wp_redirect( add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping', 'provider' => 'simple-shipping' ), admin_url( 'admin' ) ) );
		die();
	}
}
add_action( 'admin_init', 'it_exchange_simple_shipping_settings_redirect' );

/**
 * Outputs wizard settings for Flat Rate Shipping
 *
 * Exchange allows add-ons to add a small amount of settings to the wizard.
 * You can add these settings to the wizard by hooking into the following action:
 * - it_exchange_print_[addon-slug]_wizard_settings
 * Exchange exspects you to print your fields here. 
 * 
 * @since 1.4.0
 *
 * @param object $form Current IT Form object
 * @return void
*/
function it_exchange_print_simple_shipping_flat_rate_wizard_settings( $form ) { 
	$options = it_exchange_get_option( 'simple-shipping', true );
	
	//$form_values = ITUtility::merge_defaults( ITForm::get_post_data(), $settings );
	$hide_if_js  = it_exchange_is_addon_enabled( 'simple-shipping' ) && ! empty( $options['enable-flat-rate-shipping'] ) ? '' : 'hide-if-js';
	?>  
	<div class="field stripe-wizard <?php echo $hide_if_js; ?>">
		<p>Here I am</p>
	</div>
	<?php
}
add_action( 'iiiiiiiiit_exchange_print_simple-shipping-flat-rate_wizard_settings', 'it_exchange_print_simple_shipping_flat_rate_wizard_settings' );

/**
	 * Saves stripe settings when the Wizard is saved
	 *
	 * @since 0.1.0
	 *
	 * @return void
	*/
	function it_exchange_save_stripe_wizard_settings( $errors ) { 
		if ( ! empty( $errors ) ) 
			return $errors;

		$IT_Exchange_Stripe_Add_On = new IT_Exchange_Stripe_Add_On();
		return $IT_Exchange_Stripe_Add_On->stripe_save_wizard_settings();
	}
	add_action( 'it_eixchange_save_stripe_wizard_settings', 'it_exchange_save_stripe_wizard_settings' );
