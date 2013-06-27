<?php
/**
 * The default template for displaying the iThemes Exchange store
 */
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div id="it-exchange-store">
	<ul class="it-exchange-products">
	
		<?php if ( it_exchange( 'store', 'has-products' ) ) : ?>
			<?php while( it_exchange( 'store', 'products' ) ) : ?>
				<li class="it-exchange-product">
					<a class="it-exchange-product-permalink" href="<?php it_exchange( 'product', 'permalink', array( 'format' => 'url') ); ?>">
						<?php if ( it_exchange( 'product', 'has-featured-image' ) ) : ?>
							<?php it_exchange( 'product', 'featured-image', array( 'size' => 'large' ) ); ?>
						<?php endif; ?>
					</a>
					<div class="it-exchange-product-details">
						<?php it_exchange( 'product', 'title' ); ?>
						<?php it_exchange( 'product', 'baseprice' ); ?>
						<a class="it-exchange-product-details-link" href="<?php it_exchange( 'product', 'permalink', array( 'format' => 'url') ); ?>">View Details</a>
					</div>
				</li>
			<?php endwhile; ?>
		<?php else : ?>
			<p>No Products Found</p>
		<?php endif; ?>
		
	</ul>
</div>
