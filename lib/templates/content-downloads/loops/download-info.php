<?php
/**
 * The default download-info loop for the content-downloads.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
<?php do_action( 'it_exchange_content_downloads_before_download_info_loop' ); ?>
	<div class="downloads-wrapper">
	<?php do_action( 'it_exchange_content_downloads_fields_begin_download_info_loop' ); ?>
		<?php while ( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
			<div class="download">
				<?php it_exchange_get_template_part( 'content-downloads/details/fields/confirmation-url' ); ?>
				<div class="download-info">
					<h4><?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?></h4>
					<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
						<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
							<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'LION' ); ?></p>
						<?php endif; ?>
						<ul class="transaction-product-download-hashes">
						<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
							<li class="transaction-product-download-hash">
								<code class="download-hash">
									<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'hash' ) ); ?>
								</code>
								<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'expires' ) ) ) : ?>
									<span class="download-expiration">
										<?php _e( 'Expires on', 'LION' ); ?> <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'expiration-date' ) ); ?>
									</span>
								<?php else : ?>
									<span class="download-expiration">
										<?php _e( 'No expiration date', 'LION' ); ?>
									</span>
								<?php endif; ?>
								<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) ) : ?>
									<span class="download-limit">
										<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'downloads-remaining' ) ); ?> <?php _e( 'download(s) remaining', 'LION' ); ?>
									</span>
								<?php else : ?>
									<span class="download-limit">
										<?php _e( 'Unlimited downloads', 'LION' ); ?>
									</span>
								<?php endif; ?>
								<?php if ( !it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) || it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
									<?php if ( it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
										<span>
											<a href="<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'download-url' ) ); ?>"><?php _e( 'Download Now', 'LION' ); ?></a>
										</span>
									<?php endif; ?>
								<?php endif; ?>
								</li>
							<?php endwhile; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		<?php endwhile; ?>
   	<?php do_action( 'it_exchange_content_downloads_fields_end_download_info_loop' ); ?>
	</div>
<?php do_action( 'it_exchange_content_downloads_fields_after_download_info_loop' ); ?>
<?php endif; ?>