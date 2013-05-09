<?php
/**
 * Hooks for PayPal Standard add-on
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

/**
 * Outputs wizard settings for PayPal
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function paypal_web_standard_wizard_settings( $form ) {
	?>	
	<div class="field paypal-wizard hide-if-js">
		<h2><?php _e( 'PayPal Account Information', 'LION' ); ?></h2>
		<p><?php _e( 'Do not have a PayPal account yet? <a href="http://paypal.com" target="_blank">Go set one up here</a>.', 'LION' ); ?></p>
		<label for="paypal-email"><?php _e( 'PayPal Account E-mail', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
		<?php $form->add_text_box( 'paypal-email', get_bloginfo( 'admin_email' ) ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_setup_wizard_transaction_settings', 'paypal_web_standard_wizard_settings' );