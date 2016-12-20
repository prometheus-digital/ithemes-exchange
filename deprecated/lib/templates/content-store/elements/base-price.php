<?php
/**
 * The default template part for the product base-price in
 * the store-product template part's product-info loop.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/store-product/elements
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_store_before_base_price_element' ); ?>
<span class="it-exchange-base-price"><?php it_exchange( 'product', 'base-price', array( 'format' => 'text' ) ); ?></span>
<?php do_action( 'it_exchange_content_store_info_after_base_price_element' ); ?>