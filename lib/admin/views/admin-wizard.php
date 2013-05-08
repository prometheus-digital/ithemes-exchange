<?php
/**
 * This file prints the wizard page in the Admin
 *
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<!-- temp icon --> 
	<?php screen_icon( 'page' );  ?>
    
	<h2>iThemes Exchange <?php _e( 'Setup', 'LION' ); ?></h2>

	<?php
	
	$form_action = add_query_arg( array( 'page' => 'it-exchange-setup' ), get_admin_url() . 'admin.php' );
	
	$errors = it_exchange_get_errors();
	if ( ! empty( $errors ) ) {
		foreach( $errors as $error ) {
			ITUtility::show_error_message( $error );
		}
	} else if ( ! empty( $_GET['updated'] ) ) {
		ITUtility::show_status_message( __( 'Settings Updated', 'LION' ) );
	}
	
	$form_values  = empty( $values ) ? ITForm::get_post_data() : $values;
	$form_values  = ! empty( $errors ) ? ITForm::get_post_data() : $form_values;
	$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-setup' ) ); 
	$form_options = array(
		'id'      => apply_filters( 'it-exchange-setup_form_id', 'it-exchange-basic-coupons' ),
		'enctype' => apply_filters( 'it-exchange-setup_enctype', false ),
		'action'  => $form_action,
	); 
	
	$form->start_form( $form_options, 'it-exchange-setup' );
	
	?>
	<div class="it-exchange-add-basic-coupon">
		<div class="fields">
			<div class="field paypal-email">
				<label for="it-exchange-setup-paypal-email"><?php _e( 'PayPal Account E-mail', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-email' ); ?>
			</div>
			<div class="field company-email">
				<label for="it-exchange-setup-company-email"><?php _e( 'Company E-mail', 'LION' ); ?> <span class="tip" title="<?php _e( 'The E-mail address your customers will see.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'company-email' ); ?>
				<?php $form->add_check_box( 'exchange-notifications' ); ?>
				<label for="it-exchange-setup-exchange-notifications"><?php _e( 'Get e-mail updates from us about iThemes Exchange', 'LION' ); ?> <span class="tip" title="<?php _e( "We'll send you updates, discounts on add-ons and other iThemes products, and our eternal love.", 'LION' ); ?>">i</span></label>
			</div>
			<div class="field currency">
				<label for="it-exchange-setup-currency"><?php _e( 'Currency', 'LION' ); ?> <span class="tip" title="<?php _e( 'Select the currenc you plan on using in your store.', 'LION' ); ?>">i</span></label>
				<?php $form->add_drop_down( 'currency', array() ); ?>
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