<?php
/**
 * This file prints the Page Settings tab in the admin
 *
 * @scine 0.3.7
 * @package IT_Cart_Buddy
*/
?>
<div class="wrap">
	<?php
	screen_icon( 'page' );
	$this->print_general_settings_tabs();
	echo do_action( 'it_cart_buddy_general_settings_page_page_top' );
	$form->start_form( $form_options, 'cart-buddy-page-settings' );
	echo do_action( 'it_cart_buddy_general_settings_page_form_top' );
	?>
	<table class="form-table">
		<?php do_action( 'it_cart_buddy_general_settings_page_top' ); ?>
		<tr valign="top">
			<th scope="row"><strong>Page Settings</strong></th>
			<td></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it_cart_buddy_page_cart"><?php _e( 'Cart' ) ?></label></th>
			<td>
				<?php $form->add_drop_down( 'cart', $this->get_default_page_options() ); ?>
				<br /><span class="description"><?php _e( 'What page will contain the shopping cart?', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it_cart_buddy_page_checkout"><?php _e( 'Checkout' ) ?></label></th>
			<td>
				<?php $form->add_drop_down( 'checkout', $this->get_default_page_options() ); ?>
				<br /><span class="description"><?php _e( 'What page will contain the shopping checkout form?', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it_cart_buddy_page_confirmation"><?php _e( 'Confirmation' ) ?></label></th>
			<td>
				<?php $form->add_drop_down( 'confirmation', $this->get_default_page_options() ); ?>
				<br /><span class="description"><?php _e( 'What page will contain the purchase confirmation?', 'LION' ); ?></span>
			</td>
		</tr>
		<?php do_action( 'it_cart_buddy_general_settings_page_table_bottom' ); ?>
	</table>
	<?php wp_nonce_field( 'save-page-settings', 'cart-buddy-page-settings' ); ?>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
	<?php
	do_action( 'it_cart_buddy_general_settings_page_form_bottom' );
	$form->end_form();
	do_action( 'it_cart_buddy_general_settings_page_page_bottom' );
	?>
</div>
<?php
