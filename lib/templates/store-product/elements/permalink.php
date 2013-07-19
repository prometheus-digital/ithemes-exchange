<?php
/**
 * The default template part for the product permalink in
 * the store-product template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_store_product_info_before_permalink' ); ?>
<a class="it-exchange-product-details-link" href="<?php it_exchange( 'product', 'permalink', array( 'format' => 'url') ); ?>">
	<?php _e( 'View Details', 'LION' ); ?>
</a>
<?php do_action( 'it_exchange_store_product_info_after_permalink' ); ?>
