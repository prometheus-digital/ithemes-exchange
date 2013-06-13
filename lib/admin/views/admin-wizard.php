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
	<?php screen_icon( 'it-exchange' );  ?>
	
	<h2>iThemes Exchange <?php _e( 'Setup', 'LION' ); ?></h2>
	
	<?php $form->start_form( $form_options, 'exchange-general-settings' ); ?>
		<div class="it-exchange-wizard">
			<div class="fields">
				<div class="field payments">
					<label><?php _e( 'How will you be accepting payments?', 'LION' ); ?> <span class="tip" title="How you gonna get dat muh-nay?">i</span></label>
					<ul>
						<?php
							$addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods', 'show_required' => false ) );
							foreach( (array) $addons as $addon ) {
								$icon = empty( $addon['options']['icon'] ) ? $addon['name'] : '<img src="' . $addon['options']['icon'] . '" />';
								echo '<li class="payoption ' . $addon['slug'] . '-payoption" data-toggle="' . $addon['slug'] . '-wizard">' . $icon . '</li>';
							}
						?>
						
						<?php if ( ! it_exchange_is_addon_enabled( 'stripe' ) ) : ?>
							<li class="payoption stripe-payoption inactive" data-toggle="stripe-wizard"><img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/stripe.png' ); ?>" /><span>$</span></li>
						<?php endif; ?>
					</ul>
				</div>
				
				<?php if ( ! it_exchange_is_addon_enabled( 'stripe' ) ) : ?>
					<div class="field stripe-wizard inactive hide-if-js">
						<h3><?php _e( 'Stripe', 'LION' ); ?></h3>
						<p><?php _e( 'To use Stripe, you need to install the Stripe premium add-on.', 'LION' ); ?></p>
						<div class="stripe-action activate-stripe">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/plugin32.png' ); ?>" />
							<p><?php _e( 'I have the Stripe add-on and just need to install and/or activate it.', 'LION' ); ?></p>
							<p><a href="<?php echo admin_url( 'plugins.php' ); ?>" target="_self"><?php _e( 'Go to the plugins page', 'LION' ); ?></a></p>
						</div>
						<div class="stripe-action buy-stripe">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
							<p><?php _e( "I don't have the Stripe add-on yet, but I want to use Stripe.", 'LION' ); ?></p>
							<p><a href="http://ithemes.com/" target="_blank"><?php _e( 'Buy the Stripe Add-On', 'LION' ); ?></a></p>
						</div>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'it_exchange_print_wizard_settings', $form ); ?>
				
				<div class="field general-settings-wizard">
					<h3><?php _e( 'General', 'LION' ); ?></h3>
					<label for="company-email"><?php _e( 'E-mail Notifications', 'LION' ); ?> <span class="tip" title="<?php _e( 'The E-mail address you should receive notifcations to, from your store.', 'LION' ); ?>">i</span></label>
					<?php $form->add_text_box( 'company-email', array( 'value' => get_bloginfo( 'admin_email' ), 'class' => 'clearfix' ) ); ?>
					<p>
						<?php $form->add_check_box( 'exchange-notifications' ); ?>
						<label for="exchange-notifications"><?php _e( 'Get e-mail updates from us about iThemes Exchange', 'LION' ); ?> <span class="tip" title="<?php _e( "We'll send you updates, discounts on add-ons and other iThemes products, and our eternal love.", 'LION' ); ?>">i</span></label>
					</p>
					<div class="default-currency">
						<label for="default-currency"><?php _e( 'Currency', 'LION' ); ?> <span class="tip" title="<?php _e( 'Select the currenc you plan on using in your store.', 'LION' ); ?>">i</span></label>
						<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
					</div>
				</div>
				
				<!-- 
				NOTE: We are removing this for now, but will probably add this later.
				<div class="field add-on-banner">
					<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
					<p><?php _e( 'You\'re almost ready to start selling digital products using PayPal and iThemes Exchange.', 'LION' ); ?></p>
					<p><strong><?php _e( 'Remember, if you want to do more with Exchange, check out our Add-Ons Library.', 'LION' ); ?></strong></p>
					<a class="get-add-ons " href="javascript:void(0);" target="_blank"><span><?php _e( "Get Add-Ons", 'LION' ); ?></span></a>
				</div>
				-->
				
				<div class="field submit-wrapper">
					<?php $form->add_submit( 'submit', array( 'class' => 'button button-primary button-large', 'value' => __( 'Start Selling!', 'LION' ) ) ); ?>
					<?php $form->add_hidden( 'dismiss-wizard-nag', true ); ?>
					<?php $form->add_hidden( 'wizard-submitted', true ); ?>
				</div>
			</div>
		</div>
	<?php $form->end_form(); ?> 
</div>