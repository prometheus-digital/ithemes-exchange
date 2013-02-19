<?php
/**
 * This file contains the HTML for the add-on settings
 *
 * @since 0.3.6
 * @package IT_Cart_Buddy
*/
?>  
<div class="wrap">
	<h2>Manual Payment Settings</h2>
	<?php echo do_action( 'it_cart_buddy_manual_payments_settings_page_top' ); ?>
	<?php $form->start_form( $form_options, 'cart-buddy-manual-payments-settings' ); ?>
		<?php echo do_action( 'it_cart_buddy_manual_payments_settings_form_top' ); ?>
		<table class="form-table">
			<?php do_action( 'it_cart_buddy_manual_payments_settings_top' ); ?>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_manual_payments_title"><?php _e( 'Title' ) ?></label></th>
				<td>
					<input type="text" name="it_cart_buddy_manual_payments_title" value="<?php esc_attr_e( $values['title'] ); ?>" class="normal-text" />
					<br /><span class="description"><?php _e( 'What would you like to title this payment option? eg: Check', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_manual_payments_instructions"><?php _e( 'Instructions after purchase' ) ?></label></th>
				<td>
					<textarea cols="50" rows="5" name="it_cart_buddy_manual_payments_instructions"><?php echo esc_html( $values['instructions'] ); ?></textarea>
					<br /><span class="description"><?php _e( 'Use this field to give your customers instructions for payment after purchase.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="it_cart_buddy_manual_payments_default_status"><?php _e( 'Default Payment Status' ) ?></label></th>
				<td>
					<select name="it_cart_buddy_manual_payments_default_status" id="it_cart_buddy_manual_payments_default_status">
						<?php IT_Cart_Buddy_Manual_Payments_Add_On::print_default_status_options( $values['default_status'] ); ?>
					</select>
				</td>
			</tr>
		</table>
	</form>
</div>
<?php
