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
	<?php $GLOBALS['it_exchange']['downloads_found'] = false; ?>
	<?php do_action( 'it_exchange_content_downloads_before_transactions_loop' ); ?>
	<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
		<?php do_action( 'it_exchange_content_downloads_begin_transactions_loop' ); ?>
		<?php it_exchange_get_template_part( 'content-downloads/loops/product-loop' ); ?>
		<?php do_action( 'it_exchange_content_downloads_end_transactions_loop' ); ?>
    <?php endwhile; ?>
	<?php do_action( 'it_exchange_content_downloads_after_transactions_loop' ); ?>
	<?php if ( empty( $GLOBALS['it_exchange']['downloads_found'] ) ) : ?>
		<p><?php _e( 'No downloads found.', 'LION' ); ?></p>
	<?php endif; ?>
<?php else: ?>
	<p><?php _e( 'No downloads found.', 'LION' ); ?></p>
<?php endif; ?>
