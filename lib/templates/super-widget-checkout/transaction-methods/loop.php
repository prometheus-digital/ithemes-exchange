<div class="payment-methods-wrapper">
	<?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
		<p><?php _e( 'No payment add-ons enabled.', 'LION' ); ?></p>
	<?php else : ?>
		<?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
			<?php it_exchange( 'transaction-method', 'make-payment' ); ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>
