<?php
/**
 * The default template part for the product permalink in
 * the store-product template part's product-info loop
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

<?php do_action( 'it_exchange_store_product_info_before_permalink' ); ?>
<a class="it-exchange-product-permalink" href="<?php it_exchange( 'product', 'permalink', array( 'format' => 'url') ); ?>">
	<?php _e( 'View Details', 'LION' ); ?>
</a>
<?php do_action( 'it_exchange_store_product_info_after_permalink' ); ?>