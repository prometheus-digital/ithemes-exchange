<?php
/**
 * The default product-info loop for the
 * content-product.php template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-product/loops
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_product_before_product_info_loop' ); ?>
<?php foreach ( it_exchange_get_template_part_elements( 'content_product', 'product_info_loop', array( 'base-price', 'description' ) ) as $detail ) : ?>
	<?php it_exchange_get_template_part( 'content-product/elements/' . $detail ); ?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_product_after_product_info_loop' ); ?>