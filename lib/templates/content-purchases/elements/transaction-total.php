<?php
/**
 * The default template part for the transaction total in
 * the content-purchases template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-purchases/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_purchases_before_transaction_total_element' ); ?>
<span class="it-exchange-purchase-total"><?php it_exchange( 'transaction', 'total' ); ?></span>
<?php do_action( 'it_exchange_content_purchases_after_transaction_total_element' ); ?>
