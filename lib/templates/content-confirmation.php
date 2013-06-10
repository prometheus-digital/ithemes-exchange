<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
	<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
		<?php it_exchange( 'transaction', 'date' ); ?><br />
		<?php it_exchange( 'transaction', 'status' ); ?><br />
		<?php it_exchange( 'transaction', 'total' ); ?><br />
		<?php it_exchange( 'transaction', 'instructions' ); ?><br />

		<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
			<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
				<?php it_exchange( 'product', 'title' ); ?>
			<?php endwhile; ?>
		<?php endif; ?>
	<?php endwhile; ?>
<?php endif; ?>
