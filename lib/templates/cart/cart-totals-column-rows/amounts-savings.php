<?php
/**
 * Savings row for amounts column in cart totals.
*/
?>
<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
	<p class="cart-discount"><?php it_exchange( 'coupons', 'total-discount', 'type=cart' ); ?></p>
<?php endif; ?>
