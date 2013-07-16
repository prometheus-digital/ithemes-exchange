<?php
/**
 * The default template part for the transaction date in
 * the content-purchases template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_product_info_before_date' ); ?>
<span class="it-exchange-purchase-date"><strong><?php it_exchange( 'transaction', 'date' ); ?></strong></span> 
<?php do_action( 'it_exchange_content_product_info_after_date' ); ?>
