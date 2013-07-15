
<?php do_action( 'it_exchange_cart_totals_savings_start' ); ?>
	<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
		<div class="it-exchange-cart-totals-title it-exchange-cart-savings it-exchange-table-column">
			<div class="it-exchange-table-column-inner">
				<?php _e( 'Savings', 'LION' ); ?>
			</div>
		</div>
		<div class="it-exchange-cart-totals-title it-exchange-cart-savings it-exchange-table-column">
			<div class="it-exchange-table-column-inner">
				<?php it_exchange( 'coupons', 'total-discount', array( 'type' => 'cart' ) ); ?>
			</div>
		</div>
	<?php endif ?>
<?php do_action( 'it_exchange_cart_totals_savings_end' ); ?>