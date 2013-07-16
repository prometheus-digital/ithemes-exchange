<?php
/**
 * The default product loop for the content-purchases.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
<?php do_action( 'it_exchange_content_downloads_before_transactions_loop' ); ?>
	<?php do_action( 'it_exchange_content_downloads_fields_begin_transactions_loop' ); ?>
	<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
    <?php it_exchange_get_template_part( 'content-downloads/loops/product-loop' ); ?>
    <?php endwhile; ?>
   	<?php do_action( 'it_exchange_content_downloads_fields_end_transactions_loop' ); ?>
<?php do_action( 'it_exchange_content_downloads_fields_after_transactions_loop' ); ?>
<?php endif; ?>
