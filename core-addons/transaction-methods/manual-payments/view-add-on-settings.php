<?php
/**
 * This file contains the HTML for the add-on settings
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>  
<div class="wrap">
	<h2>Manual Payment Settings</h2>
	<?php echo do_action( 'it_exchange_manual_payments_settings_page_top' ); ?>
	<?php $form->start_form( $form_options, 'it-exchange-manual-payments-settings' ); ?>
		<?php echo do_action( 'it_exchange_manual_payments_settings_form_top' ); ?>
		<table class="form-table">
			<?php do_action( 'it_exchange_manual_payments_settings_table_top' ); ?>
			<tr valign="top">
				<th scope="row"><label for="it_exchange_manual_payments_title"><?php _e( 'Title' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'manual_payments_title', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What would you like to title this payment option? eg: Check', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_exchange_manual_payments_instructions"><?php _e( 'Instructions after purchase' ) ?></label></th>
				<td>
					<?php $form->add_text_area( 'manual_payments_instructions', array( 'cols' => 50, 'rows' => 5, 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'Use this field to give your customers instructions for payment after purchase.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_exchange_manual_payments_default_status"><?php _e( 'Default Payment Status' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'manual_payments_default_status', $default_status_options ); ?>
				</td>
			</tr>
			<?php do_action( 'it_exchange_manual_payments_settings_table_bottom' ); ?>
		</table>
		<?php do_action( 'it_exchange_manual_payments_settings_form_bottom' ); ?>
		<p class="submit">
			<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary' ) ); ?>
		</p>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_manual_payments_settings_page_bottom' ); ?>
</div>
<?php
