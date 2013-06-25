<?php
/**
 * The default template for displaying a customer downloads.
 */
?>
<div id="it-exchange-downloads">
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php it_exchange( 'customer', 'menu' ); ?>

	<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
		<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
			<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
				<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
					<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
						<div class="downloads-wrapper">
							<?php while ( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
								<div class="download">
									<div class="download-product">
										<a href="<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'confirmation-url' ) ); ?>" class="button">
											<?php _e( 'Transaction', 'LION' ); ?>
										</a>
									</div>
									<div class="download-info">
										<h4><?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?></h4>
										<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
											<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
												<p> 
													<?php _e( 'The status for this transaction does not grant access to downlodable files. Once the transaction is updated to an appoved status, you will receive a follup email with your download     links.', 'LION' ); ?>
												</p>
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
						</div>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>
