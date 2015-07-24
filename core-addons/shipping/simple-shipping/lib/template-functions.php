<?php
/**
 * Adds shipping-address as a valid super-widget state
 *
 * @since unknown
 *
 * @param array $valid_states existing valid states
 * @return array
*/
function it_exchange_simple_shipping_modify_valid_sw_states( $valid_states ) {
	$valid_states[] = 'shipping-address';
	return $valid_states;
}
add_filter( 'it_exchange_super_widget_valid_states', 'it_exchange_simple_shipping_modify_valid_sw_states' );

function it_exchange_addon_simple_shipping_replace_order_table_tag_before_total_row( $email_obj, $options ) {
	?>
	<tr>
		<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?></td>
		<td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_shipping_total( $email_obj->transaction_id ); ?></td>
	</tr>
	<?php
}
add_action( 'it_exchange_replace_order_table_tag_before_total_row', 'it_exchange_addon_simple_shipping_replace_order_table_tag_before_total_row', 10, 2 );