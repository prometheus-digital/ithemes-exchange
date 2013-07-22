<?php
/**
 * The default template part for the product subtotal in
 * the content-purchases template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>

<?php do_action( 'it_exchange_content_product_info_before_subtotal' ); ?>
<span class="it-exchange-item-subtotal">- <?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_subtotal' ) ); ?></span>
<?php do_action( 'it_exchange_content_product_info_after_subtotal' ); ?>