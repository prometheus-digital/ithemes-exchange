<?php
/**
 * This file contains functions for interacting with the addon
 * @since 1.0.0
 * @package IT_Exchange
*/

/**
 * Get shipping for cart
 *
 * @since 1.0.0
 *
*/
function it_exchange_addon_get_shipping_for_cart( $format_price=true ) {

	// Grab the tax rate
	$options  = it_exchange_get_option( 'addon_shipping_general' );
	$tax_rate = empty( $options['default-tax-rate'] ) ? 1 : (float) $options['default-tax-rate'];
	$process_after_discounts = ! empty( $options['calculate-after-discounts'] );

	// Grab the cart subtotal or the cart total depending on the process after discounts option
	$cart_total = it_exchange_get_cart_subtotal( false );

	if ( $process_after_discounts )
		$cart_total -= it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );

	// Calculate shipping
	$cart_shipping = $cart_total * ( $tax_rate / 100 );

	$shipping = apply_filters( 'it_exchange_addon_get_shipping_for_cart', $cart_shipping );
	if ( $format_price )
		$shipping = it_exchange_format_price( $shipping );
	return $shipping;
}

/**
 * Returns the value of an address field for the address form.
 *
 * @since 1.0.0
 *
 * @param string $field       the form field we are looking for the value
 * @param int    $customer_id the wp ID of the customer
 *
 * @return string
*/
function it_exchange_addon_shipping_address_value( $field, $customer_id=false ) {
	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
	$saved_address = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
	$cart_address = it_exchange_addon_shipping_get_cart_address();	

	$value = empty( $saved_address[$field] ) ? '' : $saved_address[$field];
	$value = empty( $cart_address[$field] ) ? $value : $cart_address[$field];
	echo 'value="' . esc_attr( $value ) . '" ';
}

/** @todo finish this **/
function it_exchange_addon_shipping_get_formatted_address() {
	$customer_id = it_exchange_get_current_customer_id();
	$address    = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
	$formatted  = $address['name'] . '<br />';
	$formatted .= $address['address1'];
	$formatted .= empty( $address['address2'] ) ? '' : '<br />' . $address['address2'];
	$formatted .= '<br />' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];

	return $formatted;
}

/**
 * Clears the shipping address value when the cart is emptied
 *
 * @since 1.1.0
 *
 * @return void
*/
function it_exchange_addon_shipping_clear_cart_address() {
	it_exchange_remove_cart_data( 'shipping-address' );
}
add_action( 'it_exchange_empty_shopping_cart', 'it_exchange_addon_shipping_clear_cart_address' );

/**
 * Adjusts the cart total
 *
 * @since 1.0.0
 *
 * @param $total the total passed to us by Exchange.
 * @return
*/
function it_exchange_addon_shipping_modify_total( $total ) {
	$shipping = it_exchange_addon_get_shipping_for_cart( false );
	return $total + $shipping;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_addon_shipping_modify_total' );

/**
 * Enqueue css for settings page
 *
 * @since 1.1.0
 *
 * @return void
*/
function it_exchange_addon_shipping_enqueue_admin_css() {
	$current_screen = get_current_screen();
	if ( ! empty( $current_screen->base ) && 'exchange_page_it-exchange-addons' == $current_screen->base && ! empty( $_GET['add-on-settings'] ) && 'shipping' == $_GET['add-on-settings'] )
		wp_enqueue_style( 'it-exchange-addon-shipping-settings', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/settings.css' );
}
add_action( 'admin_print_styles', 'it_exchange_addon_shipping_enqueue_admin_css' );

/**
 * Enqueue SW Javascript
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_addon_shipping_enqueue_sw_js() {
	wp_enqueue_script( 'it-exchange-addon-shipping-sw-js', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) ) . '/js/super-widget.js' );
}
add_action( 'it_exchange_enqueue_super_widget_scripts', 'it_exchange_addon_shipping_enqueue_sw_js' );

/**
 * Enqueues frontend javascript needed on checkout page
 *
 *
 * @since 1.2.0
 *
 * @return void
*/
function it_exchange_addon_shipping_frontend_js() {
	// Load Registration purchase requirement JS if not logged in and on checkout page.
	if ( it_exchange_is_page( 'checkout' ) && ! is_user_logged_in() )
		wp_enqueue_script( 'it-exchange-shipping-purchase-requirement', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/js/checkout.js' ), array( 'jquery' ), false, true );
}

/**
 * Returns the enabled shipping types
 *
 * @since 1.2.2
 *
 * @return array
*/
function it_exchange_get_enabled_shipping_methods() {
	return array( 'exchange-standard-shipping' => 'Exchange ' . __( 'Standard Shipping', 'LION' ) );
}

/**
 * This function hooks into the AJAX call generated in general settings for country/states sync
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_addon_shipping_update_general_setting_state_field() {
	$base_country = empty( $_POST['ite_base_country_ajax'] ) ? false : $_POST['ite_base_country_ajax'];
	$base_state   = empty( $_POST['ite_base_state_ajax'] ) ? '' : $_POST['ite_base_state_ajax'];
	$states       = it_exchange_get_data_set( 'states', array( 'country' => $base_country ) );

	if ( empty( $states ) ) {
		?>
		<input type="text" id="product-ships-from-states" name="addon-shipping-general-product-ships-from-states" maxlength="3" placeholder="<?php _e( 'State', 'LION' ); ?>" class="small-text" value="<?php esc_attr_e( $base_state ); ?>" />&nbsp;
		<?php $open_tag = '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">'; ?>
		<span class="description"><?php printf( __( 'Please use the 2-3 character %sISO 3166-2 Country Subdivision Code%s', 'LION' ), $open_tag, '</a>' ); ?></span>
		<?php
	} else {
		?>
		<select id="product-ships-from-states" name="addon-shipping-general-product-ships-from-states">
		<?php
		foreach( (array) $states as $key => $value ) {
			?><option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $base_state ); ?>><?php esc_html_e( $value ); ?></option><?php
		}
		?></select><?php
	}
	die();
}
add_action( 'it_exchange_admin_country_states_sync_for_addon-shipping-general', 'it_exchange_addon_shipping_update_general_setting_state_field' );
