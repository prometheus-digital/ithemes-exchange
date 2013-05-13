<?php
/**
 * This file prints the wizard page in the Admin
 *
 * @since 0.4.0
 * @package IT_Exchange
 * @todo update the Stripe links
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
            	<?php if ( !is_it_exchange_addon_enabled( 'stripe' ) ) { ?>
                <li class="payoption stripe-payoption inactive"><img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/stripe.png' ); ?>" /></li>
           		<?php } ?>
            </div>
            <?php if ( !is_it_exchange_addon_enabled( 'stripe' ) ) { ?>
			<div class="field stripe-wizard hide-if-js">
				<h2><?php _e( 'Stripe', 'LION' ); ?></h2>
				<p><?php _e( 'To use Stripe, you need to install the <a href="http://ithemes.com/">Stripe premium add-on</a>', 'LION' ); ?></p>
                <div class="activate-stripe">
                	<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/plugin32.png' ); ?>" />
                    <p><?php _e( 'I have the Stripe add-on and just need to activate it.', 'LION' ); ?></p>
                    <p><a href="plugins.php"><?php _e( 'Go to the plugin page to activate Stripe', 'LION' ); ?></a></p>
                </div>
                <div class="buy-stripe">
                	<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
                    <p><?php _e( "I don't have the Stripe add-on yet, but I want to use Stripe.", 'LION' ); ?></p>
                    <p><a href="http://ithemes.com/"><?php _e( 'Buy the Stripe Add-On', 'LION' ); ?></a></p>
                </div>
			</div>
            <?php } ?>
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