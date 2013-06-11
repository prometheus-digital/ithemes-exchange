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
										<div class="transaction-product-download-titile">
											<?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?>
										</div>
										<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
											<div class="transaction-product-download-hashes">
												<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
													<div class="transaction-product-download-hash">
														<div class="it-exchange-transaction-product-download-hash-hash">
															<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'hash' ) ); ?>
														</div>
														<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'expires' ) ) ) : ?>
															<div class="it-exchange-transaction-product-download-hash-expiration-date">
																<?php _e( 'Expires on', 'LION' ); ?> <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'expiration-date' ) ); ?>
															</div>
														<?php else : ?>
															<div class="it-exchange-transaction-product-download-hash-expiration-date">
																<?php _e( 'No expiration date', 'LION' ); ?>
															</div>
														<?php endif; ?>
														<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) ) : ?>
															<div class="it-exchange-transaction-product-download-hash-download-limit">
																<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'downloads-remaining' ) ); ?> <?php _e( 'download(s) remaining', 'LION' ); ?> 
															</div>
														<?php else : ?>
															<div class="it-exchange-transaction-product-download-hash-download-limit">
																<?php _e( 'Unlimited downloads', 'LION' ); ?>
															</div>
														<?php endif; ?>
														<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
															<a href="<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'download-url' ) ); ?>"><?php _e( 'Download Now', 'LION' ); ?></a>
														<?php endif; ?>
													</div>
												<?php endwhile; ?>
											</div>
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
<?php endif; ?>
