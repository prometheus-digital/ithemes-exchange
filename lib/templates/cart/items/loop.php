

<?php do_action( 'it_exchange_cart_items_loop_start' ); ?>
	<?php while ( it_exchange( 'cart', 'cart-items' ) ) : ?>
		<div class="it-exchange-table-row">
			<?php foreach ( it_exchange_get_cart_item_columns() as $columns ) : ?>
				<?php it_exchange_get_template_part( 'cart/items/details/' . $columns ); ?>
			<?php endforeach; ?>
		</div>
	<?php endwhile; ?>
<?php do_action( 'it_exchange_cart_items_loop_end' ); ?>