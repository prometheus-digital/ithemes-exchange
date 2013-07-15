
<?php do_action( 'it_exchange_cart_totals_subtotal_start' ); ?>
	<div class="it-exchange-cart-totals-title it-exchange-cart-subtotal it-exchange-table-column">
		<div class="it-exchange-table-column-inner">
			<?php _e( 'Subtotal', 'LION' ); ?>
		</div>
	</div>
	<div class="it-exchange-cart-totals-title it-exchange-cart-subtotal it-exchange-table-column">
		<div class="it-exchange-table-column-inner">
			<?php it_exchange( 'cart', 'subtotal' ) ?>
		</div>
	</div>
<?php do_action( 'it_exchange_cart_totals_subtotal_end' ); ?>