<?php
/**
 * The default template part for the download's download expiration in
 * the content-downloads template part's download-hash loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'expires' ) ) ) : ?>
	<?php do_action( 'it_exchange_content_downloads_details_before_download-expiration-date' ); ?>
	<span class="download-expiration">
		<?php _e( 'Expires on', 'LION' ); ?> <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'expiration-date' ) ); ?>
	</span>
	<?php do_action( 'it_exchange_content_downloads_details_after_download-expiration-date' ); ?>
<?php else : ?>
	<?php do_action( 'it_exchange_content_downloads_details_before_download-expiration-date' ); ?>
	<span class="download-expiration">
		<?php _e( 'No expiration date', 'LION' ); ?>
	</span>
	<?php do_action( 'it_exchange_content_downloads_details_after_download-expiration-date' ); ?>
<?php endif; ?>
