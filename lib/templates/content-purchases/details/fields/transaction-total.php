<?php
/**
 * The default template part for the transaction total in
 * the content-purchases template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_product_info_before_total' ); ?>
<span class="it-exchange-purchase-total"><strong><?php it_exchange( 'transaction', 'total' ); ?></strong></span>
<?php do_action( 'it_exchange_content_product_info_after_total' ); ?>