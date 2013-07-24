<?php
/**
 * The default template part for the product featured-image in
 * the store-product template part's product-images loop
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

<?php if ( it_exchange( 'product', 'has-featured-image' ) ) : ?>
	<?php do_action( 'it_exchange_store_product_images_before_featured-image' ); ?>
	<a class="it-exchange-product-feature-image" href="<?php it_exchange( 'product', 'permalink', array( 'format' => 'url' ) ); ?>">
		<?php it_exchange( 'product', 'featured-image', array( 'size' => 'large' ) ); ?>
	</a>
	<?php do_action( 'it_exchange_store_product_images_after_featured-image' ); ?>
<?php endif; ?>