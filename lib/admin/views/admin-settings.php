<?php
/**
 * This file contains the contents of the Settings page
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php
	screen_icon( 'page' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_page_top' );

	$form->start_form( $form_options, 'exchange-general-settings' );
	?>
		<?php do_action( 'it_exchange_general_settings_form_top', $form ); ?>
		<table class="form-table">
			<?php do_action( 'it_exchange_general_settings_table_top', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Company Details', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-settings-company-name"><?php _e( 'Company Name' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-name', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'The name used in customer receipts.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-company-settings-tax-id"><?php _e( 'Company Tax ID' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-tax-id', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-company-settings-email"><?php _e( 'Company Email' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-email', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-company-settings-phone"><?php _e( 'Company Phone' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-phone', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-company-settings-address"><?php _e( 'Company Address' ) ?></label></th>
				<td>
					<?php $form->add_text_area( 'company-address', array( 'rows' => 5, 'cols' => 30 ) ); ?>
				</td>
			</tr>
			<?php do_action( 'it_exchange_general_settings_before_settings_currency', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Currency Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-settings-default-currency"><?php _e( 'Default Currency' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-settings-currency-symbol-position"><?php _e( 'Symbol Position' ) ?></label></th>
				<td>
					<?php 
					$symbol_positions = array( 'before' => __( 'Before: $10.00', 'LION' ), 'after' => __( 'After: 10.00$', 'LION' ) );
					$form->add_drop_down( 'currency-symbol-position', $symbol_positions ); ?>
					<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-currency-thousands-separator"><?php _e( 'Thousands Separator' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-thousands-separator', array( 'class' => 'small-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it-exchange-currency-decimals-separator"><?php _e( 'Decimals Separator' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-decimals-separator', array( 'class' => 'small-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<?php do_action( 'it_exchange_general_settings_table_bottom', $form ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
		<?php do_action( 'it_exchange_general_settings_form_bottom', $form ); ?>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_general_settings_page_bottom' ); ?>
</div>
<?php
