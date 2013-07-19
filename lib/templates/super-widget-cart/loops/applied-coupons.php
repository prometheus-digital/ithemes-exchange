<?php do_action( 'it_exchange_super_widget_cart_before_applied_coupons' ); ?>
<ul class="applied-coupons">
	<?php do_action( 'it_exchange_super_widget_cart_begin_applied_coupons' ); ?>
	<?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
		<li class="coupon">
			<?php it_exchange( 'coupons', 'code' ); ?> &ndash; <?php it_exchange( 'coupons', 'discount-label' ); ?>&nbsp;<?php it_exchange( 'coupons', 'remove', array( 'type' => 'cart' ) ); ?>
		</li>
	<?php endwhile; ?>
	<?php do_action( 'it_exchange_super_widget_cart_end_applied_coupons' ); ?>
</ul>
<?php do_action( 'it_exchange_super_widget_cart_after_applied_coupons' ); ?>
