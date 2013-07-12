
<!-- We could also add do action start here. -->
<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'accepting', 'type=cart' ) ) : ?>
	<div class="cart-apply-coupons">
		<?php it_exchange( 'coupons', 'apply', 'type=cart' ); ?>
		<?php it_exchange( 'cart', 'update', 'label=' . __( 'Apply Coupon', 'LION' ) ); ?>
	</div>
<?php endif; ?>
<!-- We could also add do action end here. -->