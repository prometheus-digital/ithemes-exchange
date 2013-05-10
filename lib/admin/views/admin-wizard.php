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

	<?php $form->start_form( $form_options, 'exchange-general-settings' ); ?>
	<div class="it-exchange-wizard">
		<div class="fields">
        	<div class="field payments">
            	<p><?php _e( 'How will you be accepting payments?', 'LION' ); ?> <span class="tip" title="How you gonna get dat muh-nay?">i</span></p>
                <?php
				$addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
				foreach( (array) $addons as $addon ) {
					
					$icon = empty( $addon['options']['icon'] ) ? $addon['name'] : '<img src="' . $addon['options']['icon'] . '" />';
					
					echo '<li class="payoption ' . $addon['slug'] . '-payoption">' . $icon . '</li>';
					
				}
				?>
                <li class="payoption other-payoption inactive"><?php _e( 'Other', 'LION' ) ?></li>
            </div>
			<div class="field other-wizard hide-if-js">
				<p><?php _e( 'Want something better? Buy one of our luxiorious and sexy plugins at <a href="http://ithemes.com" target="_blank">iThemes</a>.', 'LION' ); ?></p>
				<p><?php _e( 'Then, install it and activate it in the WordPress plugin manager, come back to this screen and finish setting up your store!', 'LION' ); ?></p>
			</div>
            <?php do_action( 'it_exchange_print_wizard_settings', $form ); ?>
			<div class="field company-email">
            	<h2><?php _e( 'General', 'LION' ); ?></h2>
				<label for="company-email"><?php _e( 'E-mail Notifications', 'LION' ); ?> <span class="tip" title="<?php _e( 'The E-mail address you should receive notifcations to, from your store.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'company-email', get_bloginfo( 'admin_email' ) ); ?>
				<?php $form->add_check_box( 'exchange-notifications' ); ?>
				<label for="exchange-notifications"><?php _e( 'Get e-mail updates from us about iThemes Exchange', 'LION' ); ?> <span class="tip" title="<?php _e( "We'll send you updates, discounts on add-ons and other iThemes products, and our eternal love.", 'LION' ); ?>">i</span></label>
			</div>
			<div class="field default-currency">
				<label for="default-currency"><?php _e( 'Currency', 'LION' ); ?> <span class="tip" title="<?php _e( 'Select the currenc you plan on using in your store.', 'LION' ); ?>">i</span></label>
				<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
			</div>
            
			<div class="clearfix"></div>
			<br>
			
			<div class="clearfix"></div>
			<br>

			<?php $form->add_submit( 'submit', __( 'Start Selling!', 'LION' ) ); ?>
			<?php $form->add_hidden( 'dismiss-wizard-nag', true ); ?>
			<?php $form->add_hidden( 'wizard-submitted', true ); ?>
		</div>
        <div class="add-on-banner">
            <a class="addon-banner" href="#" target="_blank">
                <p><?php _e( "You're almost ready to start selling digital products using PayPal and iThemes Exchange. <strong>Remember, if you want to do more with Exchange, check out our Add-Ons Library</strong>.", 'LION' ); ?></p>
                <span><?php _e( "Get Add-Ons", 'LION' ); ?></span>
            </a>
        </div>
	</div>
    
	<?php $form->end_form(); ?> 
</div>