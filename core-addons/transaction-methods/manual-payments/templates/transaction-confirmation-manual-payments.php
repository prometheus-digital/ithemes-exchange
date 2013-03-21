<?php
/**
 * Confirmation page for a manual payment transaction
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/
$args = it_cart_buddy_get_template_part_args( 'transaction-confirmation-manual-payments' );
$transaction_id = empty( $args['transaction_id'] ) ? 0 : $args['transaction_id'];
?>
<h3>Details</h3>
<div>
	<?php echo it_cart_buddy_manual_transactions_get_instructions( $transaction_id ); ?>
	<p>Transaction Order #: <?php esc_attr_e( $transaction_id ); ?></p>
	<?php do_action( 'it_cart_buddy_manual_transactions_purchase_confirmation', $transaction_id ); ?>
</div>
