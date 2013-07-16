<?php
/**
 * The default template part for the product description in
 * the content-product template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'product', 'has-description' ) ) : ?>
	<?php do_action( 'it_exchange_content_product_info_before_description' ); ?>
	<div class="product-description">
		<?php it_exchange( 'product', 'description' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_product_info_after_description' ); ?>
<?php endif; ?>
