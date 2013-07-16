<?php
/**
 * The default template part for the product super widget in
 * the content-product template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_product_info_before_super-widget' ); ?>
<?php it_exchange( 'product', 'super-widget' ); ?>
<?php do_action( 'it_exchange_content_product_info_after_super-widget' ); ?>
