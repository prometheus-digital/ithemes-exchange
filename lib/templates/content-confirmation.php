<div id="it-exchange-confirmation">
	<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
		<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
			<?php it_exchange( 'transaction', 'date' ); ?><br />
			<?php it_exchange( 'transaction', 'status' ); ?><br />
			<?php it_exchange( 'transaction', 'total' ); ?><br />
			<?php it_exchange( 'transaction', 'instructions' ); ?><br />
			
			<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
				<div class="transaction-products">
					<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
						<div class="transaction-product">
							<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title', 'wrap' => 'h3' ) ); ?>
							<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
								<div class="transaction-product-downloads">
									<?php while( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
										<div class="transaction-product-download">
											<h4 class="transaction-product-download-titile">
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
															<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
																<span>
																	<a href="<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'download-url' ) ); ?>"><?php _e( 'Download Now', 'LION' ); ?></a>
																</span>
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
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php else : ?>
		<p class="error"><?php _e( 'No transactions found.', 'LION' ); ?></p>
	<?php endif; ?>
</div>