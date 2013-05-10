<?php
/**
 * This file contains the HTML for the add-on settings
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>  
<div class="wrap">
	<?php $form->start_form( $form_options, 'it-exchange-offline-payments-settings' ); ?>
		<?php do_action( 'it_exchange_offline_payments_settings_form_top' ); ?>
        <?php $this->get_offline_payment_form_table( $form ); ?>
		<?php do_action( 'it_exchange_offline_payments_settings_form_bottom' ); ?>
		<p class="submit">
			<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary' ) ); ?>
		</p>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_offline_payments_settings_page_bottom' ); ?>
</div>