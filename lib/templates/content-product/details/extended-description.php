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
	<?php do_action( 'it_exchange_content_product_advanced_before_extended-description' ); ?>
	<?php if ( it_exchange( 'product', 'has-extended-description' ) ) : ?>
		<div class="extended-description advanced-item">
			<?php it_exchange( 'product', 'extended-description' ); ?>
		</div>
	<?php endif; ?>
	<?php do_action( 'it_exchange_content_product_advanced_after_extended-description' ); ?>
<?php endif; ?>
