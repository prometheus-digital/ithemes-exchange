
<?php do_action( 'it_exchange_cart_coupons_loop_start' ); ?>
	<?php while ( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
		<div class="it-exchange-table-row">
			<?php foreach ( it_exchange_get_cart_coupon_columns() as $column ) : ?>
				<?php it_exchange_get_template_part( 'cart/coupons/details/' . $column ); ?>
			<?php endforeach; ?>
		</div>
	<?php endwhile; ?>
<?php do_action( 'it_exchange_cart_coupons_loop_end' ); ?>