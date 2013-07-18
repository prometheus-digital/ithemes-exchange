<?php
/**
 * The default template part for the product count in
 * the content-purchases template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_product_info_before_count' ); ?>
<span class="item-price">- <?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'count' ) ); ?></span>
<?php do_action( 'it_exchange_content_product_info_after_count' ); ?>