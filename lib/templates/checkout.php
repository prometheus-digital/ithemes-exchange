<?php
/**
 * This file generates the checkout page
 * @since 0.3.8
 * @package IT_Exchange
*/
$checkout_url  = it_exchange_get_page_url( 'checkout' );
$cart_products = it_exchange_get_cart_products();

do_action( 'it_exchange_checkout_html_top' );

// Display the Login form if user is not logged in
if ( ! is_user_logged_in() )
	echo it_exchange_get_customer_login_form();
?>
<form action="<?php esc_attr_e( $checkout_url ); ?>" method="post" >
	<?php do_action( 'it_exchange_checkout_form_top' ); ?>

	<?php /** @todo Convert this to templat **/
	echo it_exchange_get_cart_checkout_customer_form_fields();
	?>

	<div id="it_exchange_checkout_order_summary">
		<h3><?php _e( 'Order Summary', 'LION' ); ?></h3>
		<table>
			<?php do_action( 'it_exchange_checkout_order_summary_table_bottom' ); ?>
			<?php /** @todo: Make table columns filterable like cart **/ ?>
			<thead>
				<tr>
					<th>Product</th>
					<th>Quantity</th>
					<th>Totals</th>
				</tr>
			</thead>
			<tbody>
				<?php do_action( 'it_exchange_checkout_order_summary_table_body_top' ); ?>
				<?php foreach( (array) $cart_products as $product ) : ?>
					<?php /** @todo: Make table row data filterable like cart **/ ?>
					<tr>
						<td><?php echo it_exchange_get_cart_product_title( $product ); ?></td>
						<td><?php echo it_exchange_get_cart_product_quantity( $product ); ?></td>
						<td><?php echo it_exchange_get_cart_product_subtotal( $product ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php do_action( 'it_exchange_before_cart_subtotal_table_row' ); ?>

				<tr>
					<td colspan=2><?php  _e( 'Cart Subtotal', 'LION' ); ?></td>
					<td><?php echo it_exchange_get_cart_subtotal(); ?></td>
				</tr>

				<?php do_action( 'it_exchange_before_cart_total_table_row' ); ?>

				<tr>
					<td colspan=2><?php _e( 'Order Total', 'LION' ); ?></td>
					<td><?php echo it_exchange_get_cart_total(); ?></td>
				</tr>
				<?php do_action( 'it_exchange_checkout_order_summary_table_body_bottom' ); ?>
			</tbody>
			<?php do_action( 'it_exchange_checkout_order_summary_table_bottom' ); ?>
		</table>
	</div>

	<h3><?php _e( 'Payment Method', 'LION' ); ?></h3>
	<div id="it-exchange-checkout-place-order-form">
		<?php if ( ! $transaction_methods = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) ) ) : ?>
			<p><?php _e( 'No payment add-ons enabled!', 'LION' ); ?></p>
		<?php else : ?>
			<?php if ( 1 === count( $transaction_methods ) ) : ?>
				<?php $method = reset( $transaction_methods ); ?>
				<p>
					<?php echo it_exchange_get_transaction_method_name( $method['slug'] ); ?>
				</p>
				<input type="hidden" name="<?php esc_attr_e( it_exchange_get_field_name( 'transaction_method' ) ); ?>" value="<?php esc_attr_e( $method['slug'] ); ?>" />
			<?php else : ?>
				<p>
					<?php _e( 'Choose a payment method', 'LION' ); ?>
					<?php foreach( (array) $transaction_methods as $method ) : ?>
						<br />
						<label for="transaction-method-<?php esc_attr_e( $method['slug'] ); ?>">
							<input type="radio" id="transaction-method-<?php esc_attr_e( $method['slug'] ); ?>" name="<?php esc_attr_e( it_exchange_get_field_name( 'transaction_method' ) ); ?>" value="<?php esc_attr_e( $method['slug'] ); ?>" />
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html( it_exchange_get_transaction_method_name( $method['slug'] ) ); ?>
						</label>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<p>
			<input type="hidden" name="<?php esc_attr_e( it_exchange_get_field_name( 'purchase_cart' ) ); ?>" value=1 />
			<input type="submit" name="it-exchange-place-order" value="<?php _e( 'Place Order', 'LION' ); ?>" />
			&nbsp;<a href="<?php esc_attr_e( it_exchange_get_page_url( 'cart' ) ); ?>"><?php _e( 'Back to cart', 'LION' ); ?></a>
		</p>
	</div>
	<?php wp_nonce_field( 'it-exchange-checkout-action-' . session_id() ); ?>
	<?php do_action( 'it_exchange_checkout_form_bottom' ); ?>
</form>
<?php do_action( 'it_exchange_checkout_html_bottom' ); ?>
