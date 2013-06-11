<?php
/**
 * The default template for displaying a customer it-exchange-purchases.
 */
?>
<div class="it-exchange-purchases-wrapper">
	<?php it_exchange( 'customer', 'menu' ); ?>

	<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
		<div class="it-exchange-purchase">
			<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>    
				<div class="it-exchange-purchase-top">
					<span class="it-exchange-purchase-date"><strong><?php it_exchange( 'transaction', 'date' ); ?></strong></span> 
					<span class="it-exchange-purchase-status">- <?php it_exchange( 'transaction', 'status' ); ?></span> 
					<span class="it-exchange-purchase-total"><strong><?php it_exchange( 'transaction', 'total' ); ?></strong></span>
				</div>
				<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
					<div class="it-exchange-purchase-items">
						<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
							<div class="item-info">
								<div class="item-thumbnail">
									<!-- This will be replaced with a theme API call at some point during beta -->
									<img src="http://placehold.it/150x150"/>
								</div>
								<div class="item-data">
									<h4>
										<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title' ) ); ?> 
										<span class="item-price">- <?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_subtotal' ) ); ?></span>
										<span class="item-quantity">- <?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'count' ) ); ?></span>
									</h4>
									<p><?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'description' ) ); ?></p>
								</div>
							</div>
						<?php endwhile; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endwhile; ?>
	<?php endif; ?>
</div>
