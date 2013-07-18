<?php
/**
 * The default transaction-info loop for the content-purchases.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_purchases_before_transaction_info_loop' ); ?>
<?php foreach( it_exchange_get_template_part_slugs( 'content_pruchases', 'transaction_fields', array( 'transaction-date', 'transaction-status', 'transaction-total' ) ) as $detail ): ?>
	<?php it_exchange_get_template_part( 'content-purchases/details/transaction-fields/' . $detail ); ?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_purchases_after_transaction_info_loop' ); ?>