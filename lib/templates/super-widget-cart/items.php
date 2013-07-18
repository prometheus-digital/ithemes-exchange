<div class="cart-items-wrapper">
	<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
		<?php it_exchange_get_template_part( 'super-widget-cart/items/loop' ); ?>
	<?php endwhile; ?>
	
	<?php if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ): ?>
		<div class="cart-discount">
			<?php it_exchange_get_template_part( 'super-widget-cart/discounts/loop' ); ?>
		</div>
	<?php endif; ?>
</div>
