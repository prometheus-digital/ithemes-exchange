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


	// Register Add to Cart event
	jQuery(document).on('submit', 'form.it-exchange-sw-add-to-cart', function(event) {
		event.preventDefault();
		var quantity = 0 != jQuery(this).children('.product-purchase-quantity').length ? jQuery(this).children('.product-purchase-quantity').val() : 1;
		var product  = jQuery(this).children('.add-to-cart-product-id').attr('value');
		itExchangeSWAddToCart( product, quantity );
	});

	// Register Clear Cart event
	jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-empty-cart', function(event) {
		event.preventDefault();
		// itExchangeSWOnProductPage is a JS var set in lib/super-widget/class.super-widget.php. It contains an ID or is false.
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
	});

	// Register Update Quantity event
	jQuery(document).on('click', '.it-exchange-super-widget input.it-exchange-update-quantity-button', function(event) {
		event.preventDefault();
		var product  = jQuery('.product-cart-quantity', jQuery(this).closest('.it-exchange-super-widget') ).data('cart-product-id');
		var quantity = jQuery('.product-cart-quantity', jQuery(this).closest('.it-exchange-super-widget') ).val();
		itExchangeSWUpdateQuantity(product, quantity);
	});

	// Register View Checkout link
	jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-checkout-cart', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'checkout' );
	});

	/****************************************************** 
	 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
	 ******************************************************/
	// Register Apply Coupon event for Basic Coupons Add-on
	jQuery(document).on('click', '.it-exchange-super-widget input.it-exchange-apply-coupon-button', function(event) {
	//jQuery(document).on('submit', 'form.it-exchange-sw-update-cart', function(event) {
		event.preventDefault();
		var coupon = jQuery('.apply-coupon', jQuery(this).closest('.it-exchange-super-widget') ).val();
		itExchangeSWApplyCoupon(coupon);
	});

	// Register Remove Coupon event for Basic Coupons Add-on
	jQuery(document).on('click', '.it-exchange-super-widget a.remove-coupon', function(event) {
		event.preventDefault();
		var coupon  = jQuery(this).data('coupon-code');
		itExchangeSWRemoveCoupon(coupon);
	});

	// Only register these events if we don't have multi-item carts enabled
	if ( ! ITExchangeSWMultiItemCart ) {
		
		// Register Buy Now event - if multi-item cart is enabled, by now should go to checkout page.
		jQuery(document).on('submit', 'form.it-exchange-sw-buy-now', function(event) {
			event.preventDefault();
			var quantity = jQuery(this).children('.product-purchase-quantity').val();
			var product  = jQuery(this).children('.buy-now-product-id').val();
			itExchangeSWBuyNow( product, quantity );
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

		// Register the Register Link event (switching to the register state from the login state)
		jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-sw-register-link', function(event) {
			event.preventDefault();
			itExchangeGetSuperWidgetState( 'registration' );
		});

		// Register login form submit event
		jQuery(document).on('submit', 'form.it-exchange-sw-log-in', function(event) {
			event.preventDefault();
			var un       = jQuery('#user_login', this).val();
			var pw       = jQuery('#user_pass', this).val();
			var remember = jQuery('#rememberme', this).is(':checked');
			itExchangeSWLogIn(un, pw, remember);
		});

		// Register registration submit event
		jQuery(document).on('submit', 'form.it-exchange-sw-register', function(event) {
			event.preventDefault();
			var data = {};
			data.un = jQuery('#user_login', this).val();
			data.fn = jQuery('#first_name', this).val();
			data.ln = jQuery('#last_name', this).val();
			data.em = jQuery('#email', this).val();
			data.p1 = jQuery('#pass1', this).val();
			data.p2 = jQuery('#pass2', this).val();
			itExchangeSWRegister(data);
		});
	}

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
 * Makes an ajax request to buy a product and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWBuyNow( product, quantity ) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=buy-now&sw-product=' + product + '&sw-quantity=' + quantity, function(data) {
		if ( itExchangeIsUserLoggedIn )
			itExchangeGetSuperWidgetState( 'checkout' );
		else
			itExchangeGetSuperWidgetState( 'login' );
	});
}

/**
 * Makes an ajax request to add a product to the cart and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWAddToCart( product, quantity ) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=add-to-cart&sw-product=' + product + '&sw-quantity=' + quantity, function(data) {
		if ( itExchangeIsUserLoggedIn )
			itExchangeGetSuperWidgetState( 'checkout' );
		else
			itExchangeGetSuperWidgetState( 'login' );
	});
}

/****************************************************** 
 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
 ******************************************************/
/**
 * Makes AJAX request to Apply a coupon to the cart
*/
function itExchangeSWApplyCoupon(coupon) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=apply-coupon&sw-coupon-type=cart&sw-coupon-code=' + coupon, function(data) {
		itExchangeGetSuperWidgetState( 'checkout' );
	});
}
/**
 * Remove a coupon from the cart
*/
function itExchangeSWRemoveCoupon(coupon) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=remove-coupon&sw-coupon-type=cart&sw-coupon-code=' + coupon, function(data) {
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

/**
 * Update Quantity
*/
function itExchangeSWUpdateQuantity(product, quantity) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=update-quantity&sw-cart-product=' + product + '&sw-quantity=' + quantity, function(data) {
		itExchangeGetSuperWidgetState( 'checkout' );
	});
}

/**
 * Log the user in
*/
function itExchangeSWLogIn(user, pass, remember) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=login&sw-un=' + user + '&sw-p1=' + pass + '&sw-remember=' + remember, function(data) {
		if ( '0' === data )
			itExchangeGetSuperWidgetState( 'login' );
		else
			itExchangeGetSuperWidgetState( 'checkout' );
	});
}

/**
 * Register a new users
*/
function itExchangeSWRegister(data) {
	jQuery.get( itExchangeSWAjaxURL+'&no_debug=1&sw-action=register&sw-un=' + data.un 
		+ '&sw-fn=' + data.fn
		+ '&sw-ln=' + data.ln
		+ '&sw-em=' + data.em
		+ '&sw-p1=' + data.p1
		+ '&sw-p2=' + data.p2, function(data) {
		if ( '0' === data )
			itExchangeGetSuperWidgetState( 'registration' );
		else
			itExchangeGetSuperWidgetState( 'checkout' );
	});
}
