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
function it_exchange_shipping_settings_callback() {
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
