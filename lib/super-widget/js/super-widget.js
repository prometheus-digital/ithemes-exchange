/**
 * Any events need to be connected with jQuery(document).on([event], [selector], [function/callback];
 *
*/
jQuery(function(){

	// Test menu for changing states
	jQuery(document).on('click', 'a.it-exchange-test-load-state-via-ajax', function(event){
		event.preventDefault();
		var state = jQuery(this).data('it-exchange-sw-state');
		itExchangeGetSuperWidgetState( state );
	});

	// Register Buy Now event
	jQuery(document).on('submit', 'form.it-exchange-sw-purchase-options', function(event) {
		event.preventDefault();
		var quantity = jQuery(this).children('.product-purchase-quantity').val();
		var product  = jQuery(this).children('.buy-now-product-id').attr('value');
		itExchangeSWBuyNow( product, quantity );
	});

	// Register Clear Cart event
	jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-empty-cart', function(event) {
		event.preventDefault();
		// itExchangeSWOnProductPage is a JS var set in lib/super-widget/class.super-widget.php. It contains an ID or is false.
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
	});

	// Register Add / View Coupons Event
	jQuery(document).on('click', '.it-exchange-super-widget a.sw-cart-focus-coupons', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'coupon' );
	});

	// Register Edit / View Quantity Event
	jQuery(document).on('click', '.it-exchange-super-widget a.sw-cart-focus-quantity', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'quantity' );
	});


});

/**
 * Loads a template part for the widget
*/
function itExchangeGetSuperWidgetState( state, product, focus ) {
	var productArg = '';
	var focusArg = '';

	// Set product if needed
	if ( product )
		productArg = '&sw-product=' + product;

	// Set focus if needed
	if ( 'coupon' == focus )
		focusArg = '&ite-sw-cart-focus=coupon';
	if ( 'quantity' == focus )
		focusArg = '&ite-sw-cart-focus=quantity';

	// Make call for new state HTML
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=get-state&state=' + state + productArg + focusArg, function(data) {
		jQuery('.it-exchange-super-widget').html(data);
		itExchangeSWState = state;
	});
}

/**
 * Makes an ajax request to add a product to the cart and cycle through to the checkout
*/
function itExchangeSWBuyNow( product, quantity ) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=buy-now&sw-product=' + product + '&sw-quantity=' + quantity, function(data) {
		itExchangeGetSuperWidgetState( 'checkout' );
	});
}

/**
 * Changes view back to cart with an optional focus on the coupons or the quantity
*/
function itExchangeSWViewCart(focus) {
	itExchangeGetSuperWidgetState( 'cart', false, focus );
}

/**
 * Makes an ajax request to empty the cart
*/
function itExchangeSWEmptyCart( product ) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=empty-cart', function(data) {
		itExchangeGetSuperWidgetState( 'product', product );
	});
}
