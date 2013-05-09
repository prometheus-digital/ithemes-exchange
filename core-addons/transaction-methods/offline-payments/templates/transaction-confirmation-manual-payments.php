<?php
/**
 * Confirmation page for a manual payment transaction
 * @since 0.3.8
 * @package IT_Exchange
*/
$args = it_exchange_get_template_part_args( 'transaction-confirmation-offline-payments' );
$transaction_id = empty( $args['transaction_id'] ) ? 0 : $args['transaction_id'];
?>
<h3>Details</h3>
<div>
	<?php echo it_exchange_manual_transactions_get_instructions( $transaction_id ); ?>
	<p>Transaction Order #: <?php esc_attr_e( $transaction_id ); ?></p>
	<?php do_action( 'it_exchange_manual_transactions_purchase_confirmation', $transaction_id ); ?>
</div>
