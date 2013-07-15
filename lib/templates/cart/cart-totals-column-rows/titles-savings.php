<?php
/**
 * Savings row for titles column in cart totals.
*/
?>
<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
	<p><?php _e( 'Savings', 'LION' ); ?></p>
<?php endif; ?>
