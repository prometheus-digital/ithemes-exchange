<?php
/**
 * This is the default template part for the cart items loop
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_cart_items_before_loop' ); ?>
<?php /* Theme devs are encouraged NOT to place code here */ ?>
<?php while ( it_exchange( 'cart', 'cart-items' ) ) : ?>
	<?php /* Theme devs are encouraged NOT to place code here */ ?>
	<?php do_action( 'it_exchange_cart_items_begin_loop' ); ?>

	<?php do_action( 'it_exchange_cart_items_before_table_row' ); ?>
	<?php /* Theme devs are encouraged NOT to place code here */ ?>
	<div class="it-exchange-table-row">
		<?php /* Theme devs are encouraged NOT to place code here */ ?>
		<?php do_action( 'it_exchange_cart_items_begin_table_row' ); ?>

		<?php foreach ( it_exchange_get_cart_item_columns() as $columns ) : ?>
			<?php
			/**
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_cart_item_columns filter
			 * and adding the appropriate template file to their theme or add-on
			 */
			it_exchange_get_template_part( 'cart/items/details/' . $columns );
			?>
		<?php endforeach; ?>
		<?php /* Theme devs are encouraged NOT to place code here */ ?>
		<?php do_action( 'it_exchange_cart_items_end_table_row' ); ?>
		<?php /* Theme devs are encouraged NOT to place code here */ ?>
	</div>
	<?php /* Theme devs are encouraged NOT to place code here */ ?>
	<?php do_action( 'it_exchange_cart_items_after_table_row' ); ?>

	<?php do_action( 'it_exchange_cart_items_end_loop' ); ?>
	<?php /* Theme devs are encouraged NOT to place code here */ ?>
<?php endwhile; ?>
<?php /* Theme devs are encouraged NOT to place code here */ ?>
<?php do_action( 'it_exchange_content_cart_items_after_loop' ); ?>
