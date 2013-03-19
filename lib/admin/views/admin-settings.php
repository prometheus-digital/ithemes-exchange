<?php
/**
 * This file contains the contents of the Settings page
 * @since 0.3.6
 * @package IT_Cart_Buddy
*/
?>
<div class="wrap">
	<?php
	screen_icon( 'page' );
	$this->print_general_settings_tabs();
	echo do_action( 'it_cart_buddy_general_settings_page_top' );

	$form->start_form( $form_options, 'cart-buddy-general-settings' );
	?>
		<?php echo do_action( 'it_cart_buddy_general_settings_form_top', $form ); ?>
		<table class="form-table">
			<?php do_action( 'it_cart_buddy_general_settings_table_top', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Company Details', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_settings-company_name"><?php _e( 'Company Name' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company_name', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'The name used in customer receipts.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_company_settings-tax_id"><?php _e( 'Company Tax ID' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company_tax_id', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_company_settings-email"><?php _e( 'Company Email' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company_email', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_company_settings-phone"><?php _e( 'Company Phone' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company_phone', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_company_settings-address"><?php _e( 'Company Address' ) ?></label></th>
				<td>
					<?php $form->add_text_area( 'company_address', array( 'rows' => 5, 'cols' => 30 ) ); ?>
				</td>
			</tr>
			<?php do_action( 'it_cart_buddy_general_settings_before_settings-currency', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Currency Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_settings-default_currency"><?php _e( 'Default Currency' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'default_currency', $this->get_default_currency_options() ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_settings-currency_symbol_position"><?php _e( 'Symbol Position' ) ?></label></th>
				<td>
					<?php 
					$symbol_positions = array( 'before' => __( 'Before: $10.00', 'LION' ), 'after' => __( 'After: 10.00$', 'LION' ) );
					$form->add_drop_down( 'currency_symbol_position', $symbol_positions ); ?>
					<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_currency_thousands_separator"><?php _e( 'Thousands Separator' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency_thousands_separator', array( 'class' => 'small-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_currency_decimals_separator"><?php _e( 'Decimals Separator' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency_decimals_separator', array( 'class' => 'small-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<?php do_action( 'it_cart_buddy_general_settings_table_bottom', $form ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
		<?php echo do_action( 'it_cart_buddy_general_settings_form_bottom', $form ); ?>
	<?php $form->end_form(); ?>
	<?php echo do_action( 'it_cart_buddy_general_settings_page_bottom' ); ?>
</div>
<?php
