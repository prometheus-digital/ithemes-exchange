<?php
/**
 * Default template part for the product on the
 * transaction confirmation template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/content-confirmation/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_confirmation_before_product_element' ); ?>
<div class="it-exchange-transaction-product">
	<div class="it-exchange-transaction-product-details">
	<?php do_action( 'it_exchange_content_confirmation_before_product_featured_image' ); ?>
	<?php it_exchange( 'transaction', 'featured-image' ); ?>
	<?php do_action( 'it_exchange_content_confirmation_after_product_featured_image' ); ?>

	<?php do_action( 'it_exchange_content_confirmation_before_product_title' ); ?>
	<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title', 'wrap' => 'h3' ) ); ?>
	<?php do_action( 'it_exchange_content_confirmation_after_product_title' ); ?>
    
	<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
		<?php do_action( 'it_exchange_content_confirmation_before_product_downloads' ); ?>
		<div class="it-exchange-transaction-product-downloads">
        	<h4><?php _e( 'Downloads', 'LION' ); ?></h4>
			<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
				<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an appoved status, you will receive a followup email with your download links.', 'LION' ); ?></p>
			<?php endif; ?>
			<?php while( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
				<div class="it-exchange-transaction-product-download">
					<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
						<ul class="it-exchange-downloads-data">
							<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
								<li class="it-exchange-download-data">
                                    <h5 class="it-exchange-transaction-product-download-title">
                                        <?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?>
                                    </h5>
									<?php if ( ! it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) || it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
										<?php if ( it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
											<span>
												<a class="button" href="<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'download-url' ) ); ?>"><?php _e( 'Download', 'LION' ); ?></a>
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
		<?php do_action( 'it_exchange_content_confirmation_after_product_downloads' ); ?>
	<?php endif; ?>
    </div>
    
	<?php do_action( 'it_exchange_content_confirmation_before_product_cart_object' ); ?>
    <div class="it-exchange-transaction-product-cart-object">
		<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_count' ) ); ?>
		<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_base_price' ) ); ?>
    </div>
	<?php do_action( 'it_exchange_content_confirmation_after_product_cart_object' ); ?>
</div>
<?php do_action( 'it_exchange_content_confirmation_after_product_element' ); ?>
