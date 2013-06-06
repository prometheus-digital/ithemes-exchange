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
				<th scope="row"><label for="company-name"><?php _e( 'Company Name', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-name', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'The name used in customer receipts.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-tax-id"><?php _e( 'Company Tax ID', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-tax-id', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-email"><?php _e( 'Company Email', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-email', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-phone"><?php _e( 'Company Phone', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-phone', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-address"><?php _e( 'Company Address', 'LION' ) ?></label></th>
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
				<th scope="row"><label for="default-currency"><?php _e( 'Default Currency', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-symbol-position"><?php _e( 'Symbol Position', 'LION' ) ?></label></th>
				<td>
					<?php 
					$symbol_positions = array( 'before' => __( 'Before: $10.00', 'LION' ), 'after' => __( 'After: 10.00$', 'LION' ) );
					$form->add_drop_down( 'currency-symbol-position', $symbol_positions ); ?>
					<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-thousands-separator"><?php _e( 'Thousands Separator', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-thousands-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-decimals-separator"><?php _e( 'Decimals Separator', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-decimals-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when display prices?', 'LION' ); ?></span>
				</td>
			</tr>
            <?php do_action( 'it_exchange_general_settings_before_settings_registration', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Customer Registration Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="site-registration"><?php _e( 'Customer Registration', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_radio( 'site-registration', array( 'value' => 'it' ) ); ?>
                	<label for="site-registration-it"><?php _e( 'Use Exchange Registration Only', 'LION' ) ?></label>
                    <br />
					<?php $form->add_radio( 'site-registration', array( 'value' => 'wp' ) ); ?>
                	<label for="site-registration-wp"><?php _e( 'Use WordPress Registration Setting', 'LION' ) ?></label>
                    <br />
                    <span class="description"><?php _e( 'Will require you to update your WordPress general settings.', 'LION' ); ?></span>
				</td>
			</tr>
			<?php do_action( 'it_exchange_general_settings_table_bottom', $form ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
		<?php do_action( 'it_exchange_general_settings_form_bottom', $form ); ?>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_general_settings_page_bottom' ); ?>
</div>