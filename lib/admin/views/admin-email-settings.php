<?php
/**
 * This file prints the Email Settings tab in the admin
 *
 * @scine 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php
	screen_icon( 'page' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_email_page_top' );
	$form->start_form( $form_options, 'exchange-email-settings' );
	do_action( 'it_exchange_general_settings_email_form_top' );
	?>
	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_email_top' ); ?>
		<tr valign="top">
			<th scope="row"><strong>Customer Receipt Emails</strong></th>
			<td></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-receipt-email-address"><?php _e( 'Email Address' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-address', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Email address used for customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-from-email-name"><?php _e( 'Email Name' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-name', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Name used for account that sends customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-receipt-email-subject"><?php _e( 'Subject Line' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-subject', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Subject line used for customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-receipt-email-template"><?php _e( 'Email Template' ) ?></label></th>
			<td>
				<?php $form->add_text_area( 'receipt_email_template', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) ); ?>
			</td>
		</tr>
		<?php do_action( 'it_exchange_general_settings_email_table_bottom' ); ?>
	</table>
	<?php wp_nonce_field( 'save-email-settings', 'exchange-email-settings' ); ?>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
	<?php
	do_action( 'it_exchange_general_settings_email_form_bottom' );
	$form->end_form();
	do_action( 'it_exchange_general_settings_email_page_bottom' );
	?>
</div>
<?php
