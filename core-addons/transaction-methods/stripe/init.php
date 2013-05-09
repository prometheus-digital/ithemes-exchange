<?php
/**
 * iThemes Exchangey Stripe Add-on
 * @package IT_Exchange
 * @since 0.2.0
*/

/**
 * Outputs wizard settings for Stripe
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function stripe_wizard_settings( $form ) {
	?>	
	<div class="field stripe-wizard hide-if-js">
		<h2><?php _e( 'Stripe Account Information', 'LION' ); ?></h2>
		<p><?php _e( 'Do not have a Stripe account yet? <a href="http://stripe.com" target="_blank">Go set one up here</a>.', 'LION' ); ?></p>
		<label for="stripe-live-secret-key"><?php _e( 'Live Secret Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
		<?php $form->add_text_box( 'stripe-live-secret-key' ); ?>
		<label for="stripe-live-publishable-key"><?php _e( 'Live Publishable Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
		<?php $form->add_text_box( 'stripe-live-publishable-key' ); ?>
		<label for="stripe-test-secret-key"><?php _e( 'Test Secret Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
		<?php $form->add_text_box( 'stripe-test-secret-key' ); ?>
		<label for="stripe-test-publishable-key"><?php _e( 'Test Publishable Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
		<?php $form->add_text_box( 'stripe-test-publishable-key' ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_setup_wizard_transaction_settings', 'stripe_wizard_settings' );