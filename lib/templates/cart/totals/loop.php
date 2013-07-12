
<?php do_action( 'it_exchange_cart_totals_start' ); ?>
	<div class="it-exchange-table-row">
		<!--
			NOTE Change this function name something like to it_exchange_get_cart_totals_details()
		-->
		<?php foreach ( it_exchange_get_cart_totals_column_rows() as $column ) : ?>
			<?php it_exchange_get_template_part( 'cart/totals/details/' . $column ); ?>
		<?php endforeach; ?>
	</div>
<?php do_action( 'it_exchange_cart_totals_end' ); ?>