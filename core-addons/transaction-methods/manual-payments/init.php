<?php
/**
 * Manual Payments Transaction Method
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

/**
 * Prints the settings page for this transaction method
 *
 * @since 0.3.6
 * @return void
*/
function it_cart_buddy_print_manual_payments_settings() {
	$values['manual_payments_title']        = 'Check';
	$values['manual_payments_instructions'] = 'Thank you for your purchase. Your order is pending until we receive payment.';
	?>
	<div class="wrap">
		<h2>Manual Payment Settings</h2>
		<?php echo do_action( 'it_cart_buddy_manual_payments_settings_page_top' ); ?>
		<form action='' method='post'>
			<?php echo do_action( 'it_cart_buddy_manual_payments_settings_form_top' ); ?>
			<table class="form-table">
				<?php do_action( 'it_cart_buddy_manual_payments_settings_top' ); ?>
				<tr valign="top">
					<th scope="row"><label for="it_cart_buddy_manual_payments_title"><?php _e( 'Title' ) ?></label></th>
					<td>
						<input type="text" name="it_cart_buddy_manual_payments_title" value="<?php esc_attr_e( $values['manual_payments_title'] ); ?>" class="normal-text" />
						<br /><span class="description"><?php _e( 'What would you like to title this payment option? eg: Check', 'LION' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="it_cart_buddy_manual_payments_instructions"><?php _e( 'Instructions after purchase' ) ?></label></th>
					<td>
						<textarea name="it_cart_buddy_manual_payments_instructions"><?php echo $values['manual_payments_instructions']; ?></textarea>
						<br /><span class="description"><?php _e( 'Use this field to give your customers instructions for payment after purchase.', 'LION' ); ?></span>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php
}
