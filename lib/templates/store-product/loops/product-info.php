<?php
/**
 * The default product-info loop for the store-product.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_store_product_before_product_info_loop' ); ?>
<?php foreach( it_exchange_get_template_part_slugs( 'store_product', 'product_features', array( 'title', 'base-price', 'permalink' ) ) as $detail ): ?>
	<?php it_exchange_get_template_part( 'store-product/details/' . $detail ); ?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_store_product_after_product_info_loop' ); ?>
