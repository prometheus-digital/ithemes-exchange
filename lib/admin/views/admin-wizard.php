<?php
/**
 * This file prints the wizard page in the Admin
 *
 * @since 0.4.0
 * @package IT_Exchange
 * @todo saving settings for various other addons in wizard... using IT Forms?
*/
?>
<div class="wrap">
	<!-- temp icon --> 
	<?php screen_icon( 'page' );  ?>
    
	<h2>iThemes Exchange <?php _e( 'Setup', 'LION' ); ?></h2>

	<?php $form->start_form( $form_options, 'it-exchange-setup' ); ?>
	<div class="it-exchange-add-basic-coupon">
		<div class="fields">
			<div class="field paypal-email">
				<label for="paypal-email"><?php _e( 'PayPal Account E-mail', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-email' ); ?>
			</div>
			<div class="field company-email">
				<label for="company-email"><?php _e( 'Company E-mail', 'LION' ); ?> <span class="tip" title="<?php _e( 'The E-mail address your customers will see.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'company-email' ); ?>
				<?php $form->add_check_box( 'exchange-notifications' ); ?>
				<label for="exchange-notifications"><?php _e( 'Get e-mail updates from us about iThemes Exchange', 'LION' ); ?> <span class="tip" title="<?php _e( "We'll send you updates, discounts on add-ons and other iThemes products, and our eternal love.", 'LION' ); ?>">i</span></label>
			</div>
			<div class="field default-currency">
				<label for="default-currency"><?php _e( 'Currency', 'LION' ); ?> <span class="tip" title="<?php _e( 'Select the currenc you plan on using in your store.', 'LION' ); ?>">i</span></label>
				<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
			</div>
            
			<div class="clearfix"></div>
			<br>
            
            <a class="addon-banner" href="#" target="_blank">
                <p><?php _e( "You're almost ready to start selling digital products using PayPal and iThemes Exchange. <strong>Remember, if you want to do more with Exchange, check out our Add-Ons Library</strong>.", 'LION' ); ?></p>
                <span><?php _e( "Get Add-Ons", 'LION' ); ?></span>
            </a>
			
			<div class="clearfix"></div>
			<br>

			<?php $form->add_submit( 'submit', __( 'Start Selling!', 'LION' ) ); ?>
		</div>
	</div>
    
	<?php $form->end_form(); ?> 
</div>