<?php
/**
 * The default product-images loop for the store-product.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_store_product_before_product_images_loop' ); ?>
<?php foreach( it_exchange_get_store_product_feature_details( array( 'featured-image' ) ) as $detail ): ?>
	<?php it_exchange_get_template_part( 'store-product/details/' . $detail ); ?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_store_product_after_product_images_loop' ); ?>
