
<?php do_action( 'it_exchange_cart_totals_total_start' ); ?>
	<div class="it-exchange-cart-totals-title it-exchange-cart-total it-exchange-table-column">
		<div class="it-exchange-table-column-inner">
			<?php _e( 'Total', 'LION' ); ?>
		</div>
	</div>
	<div class="it-exchange-cart-totals-title it-exchange-cart-total it-exchange-table-column">
		<div class="it-exchange-table-column-inner">
			<?php it_exchange( 'cart', 'total' ); ?>
		</div>
	</div>
<?php do_action( 'it_exchange_cart_totals_total_end' ); ?>