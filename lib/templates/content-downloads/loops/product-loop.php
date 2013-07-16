<?php
/**
 * The default product loop for the content-purchases.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
<?php do_action( 'it_exchange_content_downloads_before_products_loop' ); ?>
	<?php do_action( 'it_exchange_content_downloads_fields_begin_products_loop' ); ?>
    <?php while( it_exchange( 'transaction', 'products' ) ) : ?>
    <?php it_exchange_get_template_part( 'content-downloads/loops/download-info' ); ?>
    <?php endwhile; ?>
   	<?php do_action( 'it_exchange_content_downloads_fields_end_products_loop' ); ?>
<?php do_action( 'it_exchange_content_downloads_fields_after_products_loop' ); ?>
<?php endif; ?>
