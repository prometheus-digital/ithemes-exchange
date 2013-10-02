<?php
/**
 * This is the default template part for the core shipping-method
 * purchase requirement element in the content-checkout
 * template part.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/purchase-requirements directory
 * located in your theme.
*/

// Don't show anything if shipping-method requirement exists and hasn't been met
if ( in_array( 'customer-has-shipping-address', it_exchange_get_pending_purchase_requirements() ) )
	return;

$current_method = it_exchange_get_cart_data( 'shipping-method' );
$current_method = empty( $current_method[0] ) ? false : $current_method[0];
?>
<?php do_action( 'it_exchange_content_checkout_shipping_method_purchase_requirement_before_element' ); ?>
<div class="it-exchange-checkout-shipping-method-purchase-requirement">
<script type="text/javascript">
function itExchangeUpdateCheckoutShippingMethod( value ) {
	var ITExchangeCheckoutRefreshAjaxURL = '<?php echo esc_js( site_url() ); ?>/?ite-checkout-refresh=1';
	jQuery.post(ITExchangeCheckoutRefreshAjaxURL, {'shipping-method':value}, function(response) {
		if (response) {
			jQuery('.entry-content').html(response);
			jQuery.event.trigger({
				type: "itExchangeCheckoutReloaded"
			});
		}   
	}); 
}
function itExchangeUpdateCheckoutMultipleShippingMethod( cartProductID, value ) {
	var ITExchangeCheckoutRefreshAjaxURL = '<?php echo esc_js( site_url() ); ?>/?ite-checkout-refresh=1';

	jQuery.post(ITExchangeCheckoutRefreshAjaxURL, {'cart-product-id':cartProductID, 'shipping-method':value}, function(response) {
		if (response) {
			jQuery('.entry-content').html(response);
			jQuery.event.trigger({
				type: "itExchangeCheckoutReloaded"
			});
		}   
	}); 
}
</script>
	<h3><?php _e( 'Shipping Method', 'LION' ); ?></h3>
	<?php
	$cart_methods                      = it_exchange_get_available_shipping_methods_for_cart();
	$cart_product_methods              = it_exchange_get_available_shipping_methods_for_cart_products();
	$multiple_shipping_methods_allowed = false;

	if ( ( count( $cart_methods ) === 1 && count( $cart_product_methods ) === 1 ) || count( $cart_product_methods ) === 1 ) {
		$method = reset($cart_methods);
		it_exchange_update_cart_data( 'shipping-method', $method->slug );
		echo $method->label . ' (' . it_exchange_get_cart_shipping_cost() . ')';
	} else {
		?>
		<form method="post" action="">
		<select name="it-exchange-shipping-method" onchange="itExchangeUpdateCheckoutShippingMethod( jQuery(this).val() )">
		<?php
		$options = '<option value="0">' . __( 'Select a shipping method', 'LION' );
		foreach( $cart_methods as $method ) {
			$options .= '<option value="' . esc_attr( $method->slug ) . '" ' . selected( $current_method, $method->slug, false ) . '>' . $method->label . ' (' . it_exchange_get_cart_shipping_cost( $method->slug ) . ')</option>';
		}
		if ( (array) it_exchange_get_cart_products() > 1 ) {
			$cart_products_with_shipping = 0;
			foreach( (array) it_exchange_get_cart_products() as $cart_product ) {
				if ( it_exchange_product_has_feature( $cart_product['product_id'], 'shipping' ) )
					$cart_products_with_shipping++;
			}
			if ( $cart_products_with_shipping > 1 && count( $cart_product_methods ) > 1 ) {
				$multiple_shipping_methods_allowed = true;
				$options .= '<option value="multiple-methods" ' . selected( $current_method, 'multiple-methods', false ) . '>' . __( 'Use multiple shipping methods', 'LION' ) . '</option>';
			}
		}

		echo $options;
		?>
		</select>
		</form>
		<?php
	}

	if ( 'multiple-methods' == $current_method && $multiple_shipping_methods_allowed ) :
		?>
		<div class="it-exchange-itemized-checkout-methods">
			<?php
			foreach( (array) it_exchange_get_cart_products() as $product ) {
				if ( ! it_exchange_product_has_feature( $product['product_id'], 'shipping' ) )
					continue;

				echo it_exchange_get_cart_product_title( $product ) . ': ';
				$selected_multiple_method = it_exchange_get_multiple_shipping_method_for_cart_product( $product['product_cart_id'] );
				$enabled_shipping_methods = (array) it_exchange_get_enabled_shipping_methods_for_product( it_exchange_get_product( $product['product_id'] ) );

				if ( count( $enabled_shipping_methods ) > 1 ) {
					?>
					<select name="it-exchange-shipping-method-for-<?php esc_attr_e( $product['product_cart_id'] ); ?>" onchange="itExchangeUpdateCheckoutMultipleShippingMethod( '<?php esc_attr_e( $product['product_cart_id'] ); ?>', jQuery(this).val() )">
						<?php foreach( $enabled_shipping_methods as $product_method ) : ?>
							<?php if ( empty( $product_method->slug ) ) continue; ?>
							<option value="<?php esc_attr_e( $product_method->slug ); ?>" <?php selected( $selected_multiple_method, $product_method->slug ); ?>>
								<?php echo $product_method->label; ?> 
								(<?php echo it_exchange_get_shipping_method_cost_for_cart_item( $product_method->slug, $product, true ); ?>)
							</option>
						<?php endforeach; ?>
					</select><br />
					<?php
				} else {
					$product_method = reset( $enabled_shipping_methods );
					it_exchange_update_multiple_shipping_method_for_cart_product( $product['product_cart_id'], $product_method->slug );
					echo $product_method->label . ' (' . it_exchange_get_shipping_method_cost_for_cart_item( $product_method->slug, $product, true ) . ')<br />';
				}
			}
			?>
		</div>
		<?php
	endif;
	?>
</div>
<?php do_action( 'it_exchange_content_checkout_shipping_method_purchase_requirement_after_element' ); ?>
