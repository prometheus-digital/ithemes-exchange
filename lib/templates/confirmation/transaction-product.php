<?php
/**
 * Default template part for the product on the transaction confirmation template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_confirmation_template_part_transaction_product_top' ); ?>
<div class="transaction-product">
	<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title', 'wrap' => 'h3' ) ); ?>
	<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
		<div class="transaction-product-downloads">
			<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
				<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an appoved status, you will receive a followup email with your download links.', 'LION' ); ?></p>
			<?php endif; ?>
			<?php while( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
				<div class="transaction-product-download">
					<h4 class="transaction-product-download-title">
						<?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?>
					</h4>
					<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
						<ul class="transaction-product-download-hashes">
							<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
								<li>
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
									<?php if ( ! it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) || it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
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
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
</div>
<?php do_action( 'it_exchange_confirmation_template_part_transaction_product_bottom' ); ?>
