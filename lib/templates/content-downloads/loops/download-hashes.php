<?php
/**
 * The default download-hashes loop for the content-downloads.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
	<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'LION' ); ?></p>
<?php else: ?>
	<?php do_action( 'it_exchange_content_downloads_before_download_hashes_loop' ); ?>
	<ul class="transaction-product-download-hashes">
		<?php do_action( 'it_exchange_content_downloads_begin_download_hashes_loop' ); ?>
		<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
			<?php $GLOBALS['it_exchange']['downloads_found'] = true; ?>
			<li class="transaction-product-download-hash">
				<?php foreach( it_exchange_get_content_downloads_field_details( array( 'download-hash', 'download-expiration' ) ) as $detail ): ?>
					<?php it_exchange_get_template_part( 'content-downloads/details/' . $detail ); ?>
				<?php endforeach; ?>
			</li>
			<?php do_action( 'it_exchange_content_downloads_end_download_hashes_loop' ); ?>
		<?php endwhile; ?>
	</ul>
	<?php do_action( 'it_exchange_content_downloads_after_download_hashes_loop' ); ?>
<?php endif; ?>
