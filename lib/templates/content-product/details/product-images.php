<?php
/**
 * The default template part for the product images in
 * one of the the content-product template part's loops
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'product', 'has-images' ) ) : ?>
	<?php do_action( 'it_exchange_content_product_images_before_product-images' ); ?>
	<div class="product-column product-images">
		<div class="product-column-inner">
			<?php it_exchange( 'product', 'gallery' ); ?>
		</div>
	</div>
	<?php do_action( 'it_exchange_content_product_images_after_product-images' ); ?>
<?php endif; ?>
