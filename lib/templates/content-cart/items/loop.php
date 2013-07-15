<?php
/**
 * This is the default template part for the cart items loop
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_items_before_loop' ); ?>
<?php while ( it_exchange( 'cart', 'cart-items' ) ) : ?>
	<?php do_action( 'it_exchange_content_cart_items_begin_loop' ); ?>

	<?php do_action( 'it_exchange_content_cart_items_before_table_row' ); ?>
	<div class="it-exchange-table-row">
		<?php do_action( 'it_exchange_content_cart_items_begin_table_row' ); ?>

		<?php foreach ( it_exchange_get_content_cart_item_details() as $detail ) : ?>
			<?php
			/**
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_content_cart_item_details filter
			 * and adding the appropriate template file to their theme or add-on
			 */
			it_exchange_get_template_part( 'content-cart/items/details/' . $detail );
			?>
		<?php endforeach; ?>
		<?php do_action( 'it_exchange_content_cart_items_end_table_row' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_items_after_table_row' ); ?>

	<?php do_action( 'it_exchange_content_cart_items_end_loop' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_content_content_cart_items_after_loop' ); ?>
