<?php
/**
 * This file prints the wizard page in the Admin
 *
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php screen_icon( 'it-exchange' );  ?>
	
	<h2>iThemes Exchange <?php _e( 'Setup', 'LION' ); ?></h2>
	
	<?php $form->start_form( $form_options, 'exchange-general-settings' ); ?>
		<div class="it-exchange-wizard">
			<div class="fields">
				<div class="field payments">
					<p><?php _e( 'How will you be accepting payments? Choose one.', 'LION' ); ?><span class="tip" title="<?php _e( "Choose your preferred payment gateway for processing transactions. You can select more than one option but it's not recommended.", 'LION' ); ?>">i</span></p>
					<ul>
						<?php
							$addons = it_exchange_get_addons( array( 'category' => 'transaction-methods', 'show_required' => false ) );
							it_exchange_temporarily_load_addons( $addons );
							foreach( (array) $addons as $addon ) {
								if ( ! empty( $addon['options']['wizard-icon'] ) )
									$name = '<img src="' . $addon['options']['wizard-icon'] . '" alt="' . $addon['name'] . '" />';
								else
									$name = $addon['name'];
									
								if ( it_exchange_is_addon_enabled( $addon['slug'] ) )
									$selected_class = 'selected';
								else
									$selected_class = '';
								
								echo '<li class="payoption ' . $addon['slug'] . '-payoption ' . $selected_class . '" transaction-method="' . $addon['slug']. '" data-toggle="' . $addon['slug'] . '-wizard">';
								echo $name;
								echo '<input type="hidden" class="remove-if-js" name="it-exchange-transaction-methods[]" value="' . $addon['slug'] . '" />';
								echo  '</li>';
							}
						?>
						
						<?php if ( ! it_exchange_is_addon_registered( 'stripe' ) ) : ?>
							<li class="stripe-payoption inactive" data-toggle="stripe-wizard"><img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/stripe32.png' ); ?>" alt="<?php _e( 'Stripe', 'LION' ); ?>" /><span>$</span></li>
						<?php endif; ?>
					</ul>
				</div>
				
				<?php if ( ! it_exchange_is_addon_registered( 'stripe' ) ) : ?>
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
							<p><a href="http://ithemes.com/exchange/stripe/" target="_blank"><?php _e( 'Buy the Stripe Add-On', 'LION' ); ?></a></p>
						</div>
					</div>
				<?php endif; ?>
				
				<?php 
				foreach( (array) $addons as $addon ) {
					do_action( 'it_exchange_print_' . $addon['slug'] . '_wizard_settings', $form ); 
				}
				?>
				
				<div class="field general-settings-wizard">
					<h3><?php _e( 'General', 'LION' ); ?></h3>
					<label for="company-email"><?php _e( 'E-mail Notifications', 'LION' ); ?> <span class="tip" title="<?php _e( 'At what email address would you like to receive store notifications?', 'LION' ); ?>">i</span></label>
					<?php $form->add_text_box( 'company-email', array( 'value' => get_bloginfo( 'admin_email' ), 'class' => 'clearfix' ) ); ?>
					<p>
						<?php $form->add_check_box( 'exchange-notifications', array( 'checked' => true ) ); ?>
						<label for="exchange-notifications"><?php _e( 'Get e-mail updates from us about iThemes Exchange', 'LION' ); ?> <span class="tip" title="<?php _e( 'Subscribe to get iThemes Exchange news, updates, discounts and swag &hellip; oh, and our endless love.', 'LION' ); ?>">i</span></label>
					</p>
					<div class="default-currency">
						<label for="default-currency"><?php _e( 'Currency', 'LION' ); ?><span class="tip" title="<?php _e( 'Select the currency you plan to use in your store.', 'LION' ); ?>">i</span></label>
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
					<?php $form->add_submit( 'submit', array( 'class' => 'button button-primary button-large', 'value' => __( 'Save Settings', 'LION' ) ) ); ?>
					<?php $form->add_hidden( 'dismiss-wizard-nag', true ); ?>
					<?php $form->add_hidden( 'wizard-submitted', true ); ?>
				</div>
			</div>
		</div>
	<?php $form->end_form(); ?> 
</div>
