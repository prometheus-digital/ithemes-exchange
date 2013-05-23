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
		console.log(itExchangeSWOnProductPage);
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
	});


});

/**
 * Loads a template part for the widget
*/
function itExchangeGetSuperWidgetState( state, product ) {
	var productArg = '';
	if ( product )
		productArg = '&sw-product=' + product;
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=get-state&state=' + state + productArg, function(data) {
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
 * Makes an ajax request to empty the cart
*/
function itExchangeSWEmptyCart( product ) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=empty-cart', function(data) {
		itExchangeGetSuperWidgetState( 'product', product );
	});
}
