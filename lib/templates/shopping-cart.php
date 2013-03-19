<?php
/**
 * Default Shopping Cart Template Part
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/
$form_action        = it_cart_buddy_get_page_url( 'cart' );
$update_cart_action = it_cart_buddy_get_action_var( 'update_cart' );
$checkout_action    = it_cart_buddy_get_action_var( 'proceed_to_checkout' );
$empty_cart_action  = it_cart_buddy_get_action_var( 'empty_cart' );
$table_columns      = it_cart_buddy_get_cart_table_columns();
$cart_products      = it_cart_buddy_get_cart_products();
?>
<div id="it-cart-buddy-shopping-cart">
	<?php if ( $cart_products ) : ?>
		<form action="<?php esc_url( $form_action );?> " method="post">
			<table id="it-cart-buddy-shopping-cart-table">
				<?php do_action( 'it-cart-buddy-shopping-cart-table-top' ); ?>
				<thead>
					<tr class="it-cart-buddy-shopping-cart-table-header-row">
					<?php foreach( (array) $table_columns as $key => $value ) : ?>
						<th id="table_header_<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $value ); ?></th>
					<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php do_action( 'it-cart-buddy-shopping-cart-table-rows-top' ); ?>
					<?php foreach( $cart_products as $itemized_product_hash => $cart_product ) : ?>
						<tr>
						<?php foreach( (array) $table_columns as $key => $label ) : ?> 
							<td class="it-cart-buddy-shopping-cart-data-<?php esc_attr_e( $key ); ?>">
								<?php echo it_cart_buddy_get_cart_table_product_data( $key, $cart_product ); ?>
							</td>
						<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					<?php do_action( 'it-cart-buddy-shopping-cart-table-rows-bottom' ); ?>
				</tbody>
				<?php do_action( 'it-cart-buddy-shopping-cart-table-bottom' ); ?>
			</table>

			<?php do_action( 'it-cart-buddy-shopping-cart-form-above-actions' ); ?>
			<div id="it-cart-buddy-cart-actions">
				<?php do_action( 'it-cart-buddy-shopping-cart-actions-top' ); ?>
				<input type="submit" name="<?php esc_attr_e( $update_cart_action ); ?>"  value="<?php _e( 'Update Cart', 'LION' ); ?>" />
				&nbsp;&nbsp;<input type="submit" name="<?php esc_attr_e( $checkout_action ); ?>"  value="<?php _e( 'Checkout', 'LION' ); ?>" />
				&nbsp;&nbsp;<input type="submit" name="<?php esc_attr_e( $empty_cart_action ); ?>"  value="<?php _e( 'Empty Cart', 'LION' ); ?>" />
				<?php do_action( 'it-cart-buddy-shopping-cart-actions-bottom' ); ?>
			</div>

			<?php do_action( 'it-cart-buddy-shopping-cart-form-above-totals' ); ?>
			<div id="it-cart-buddy-cart-totals">
				<p><?php _e( 'Cart Subtotal', 'LION' ); ?>&nbsp; $<?php esc_html_e( it_cart_buddy_get_cart_subtotal() ); ?></p>
			</div>
			<?php do_action( 'it-cart-buddy-shopping-cart-form-bottom' ); ?>
			<?php wp_nonce_field( 'it_cart_buddy_cart_action-' . session_id() ); ?>
		</form>
	<?php else: ?>
		<?php _e( 'Your cart is empty', 'LION' ); ?></p>
	<?php endif; ?>
</div>
