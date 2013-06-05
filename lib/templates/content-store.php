<?php it_exchange_get_template_part( 'messages' ); ?>
<?php if ( it_exchange( 'store', 'has-products' ) ) : ?>
	<?php while( it_exchange( 'store', 'products' ) ) : ?>
		<?php it_exchange( 'product', 'permalink', 'format=html' ); ?><br />
	<?php endwhile; ?>
<?php endif; ?>
