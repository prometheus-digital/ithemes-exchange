<p>Confirmation page goes here</p>
<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
	<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
		<?php it_exchange( 'transaction', 'status' ); ?>
	<?php endwhile; ?>
<?php endif; ?>
