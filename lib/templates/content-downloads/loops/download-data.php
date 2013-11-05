<?php
/**
 * The default download-data loop for the
 * content-downloads.php template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-download/loops
 * directory located in your theme.
*/
?>

<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
	<div class="it-exchange-notice">
		<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'LION' ); ?></p>
	</div>
<?php else: ?>
	<?php do_action( 'it_exchange_content_downloads_before_download_data_loop' ); ?>
	<ul class="it-exchange-downloads-data">
		<?php do_action( 'it_exchange_content_downloads_begin_download_data_loop' ); ?>
		<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
			<?php it_exchange_set_global( 'downloads_found', true ); ?>
			<li class="it-exchange-download-data">
				<?php foreach( it_exchange_get_template_part_elements( 'content_downloads', 'download_meta', array( 'download-hash', 'download-expiration' ) ) as $detail ): ?>
					<?php it_exchange_get_template_part( 'content-downloads/elements/' . $detail ); ?>
				<?php endforeach; ?>
			</li>
			<?php do_action( 'it_exchange_content_downloads_end_download_data_loop' ); ?>
		<?php endwhile; ?>
	</ul>
	<?php do_action( 'it_exchange_content_downloads_after_download_data_loop' ); ?>
<?php endif; ?>