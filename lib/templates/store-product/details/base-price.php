<?php
/**
 * The default template part for the product base-price in
 * the store-product template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_store_product_info_before_base-price' ); ?>
<?php it_exchange( 'product', 'baseprice' ); ?>
<?php do_action( 'it_exchange_store_product_info_after_base-price' ); ?>
