<?php
/**
 * The default transaction-info loop for the content-purchases.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-purchases/loops/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_purchases_before_transaction_info_loop' ); ?>
<?php do_action( 'it_exchange_content_purchases_begin_transaction_info_loop' ); ?>
<?php foreach ( it_exchange_get_template_part_elements( 'content_purchases', 'transaction_fields', array( 'transaction-date', 'transaction-status', 'transaction-total' ) ) as $detail ) : ?>
	<?php it_exchange_get_template_part( 'content-purchases/elements/' . $detail ); ?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_purchases_end_transaction_info_loop' ); ?>
<?php do_action( 'it_exchange_content_purchases_after_transaction_info_loop' ); ?>
