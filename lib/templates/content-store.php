<?php if ( it_exchange( 'store', 'has-products' ) ) : ?>
	<?php while( it_exchange( 'store', 'products' ) ) : ?>
		<?php it_exchange( 'product', 'permalink', 'format=html' ); ?><br />
	<?php endwhile; ?>
<?php endif; ?>
