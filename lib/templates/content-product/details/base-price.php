<?php
/**
 * The default template part for the product base price in
 * the content-product template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'product', 'has-base-price' ) ) : ?>
	<?php do_action( 'it_exchange_content_product_info_before_base-price' ); ?>
	<div class="product-price">
		<p><?php it_exchange( 'product', 'base-price' ); ?></p>
	</div>
	<?php do_action( 'it_exchange_content_product_info_after_base-price' ); ?>
<?php endif; ?>
