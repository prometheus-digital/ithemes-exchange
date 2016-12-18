/**
 * Any events need to be connected with jQuery(document).on([event], [selector], [function/callback];
 *
*/

jQuery.ajaxSetup({
    cache: false
});

jQuery(function(){

	// Register Clear Cart event
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-empty-cart', function(event) {
		event.preventDefault();
		// itExchangeSWOnProductPage is a JS var set in lib/super-widget/class.super-widget.php. It contains an ID or is false.
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
		itExchange.hooks.doAction( 'itExchangeSW.cartEmptied' );
	});

	// Register Remove Product from Cart event
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.remove-cart-item', function(event) {
		event.preventDefault();
		var product  = jQuery(this).data('cart-product-id');
		itExchangeSWRemoveItemFromCart( product );
		itExchange.hooks.doAction( 'itExchangeSW.itemRemovedFromCart', product );
	});

	// Register Update Quantity event
	jQuery( '.it-exchange-super-widget' ).on('input keyup change', 'input.product-cart-quantity', function(event) {
		event.preventDefault();
		var product  = jQuery( this ).data('cart-product-id');
		var quantity = jQuery( this ).val();
		itExchangeSWUpdateQuantity(product, quantity);
		itExchange.hooks.doAction( 'itExchangeSW.updateQuantityClicked', product, quantity );
	});

	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-update-cart-quantity', function(event) {
		event.preventDefault();
		jQuery( '.it-exchange-super-widget input.product-cart-quantity', jQuery(this).closest('.it-exchange-super-widget') ).each( function() {
			var product  = jQuery( this ).data('cart-product-id');
			var quantity = jQuery( this ).val();
			itExchangeSWUpdateQuantity(product, quantity);
			itExchange.hooks.doAction( 'itExchangeSW.updateQuantityClicked', product, quantity );
		});
		itExchangeGetSuperWidgetState( 'checkout' );
	});

	// Register View Checkout link
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-checkout-cart', function(event) {
		if ( ! jQuery(this).hasClass( 'no-sw-js' ) ) {
			event.preventDefault();
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
			itExchange.hooks.doAction( 'itExchangeSW.checkoutCartClicked', itExchangeSWOnProductPage );
		}
	});

	// Register Buy Now event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-buy-now', function(event) {
		event.preventDefault();
		var quantity         = jQuery(this).children('.product-purchase-quantity').length ? jQuery(this).children('.product-purchase-quantity').val() : 1;
		var product          = jQuery(this).children('.buy-now-product-id').val();
		var additionalFields = jQuery( ':input', this ).serializeArray();
		itExchangeSWBuyNow( product, quantity, additionalFields );
		itExchange.hooks.doAction( 'itExchangeSW.buyNowtClicked', quantity, product, additionalFields );
	});

	// Register Add to Cart event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-add-to-cart', function(event) {
		event.preventDefault();
		var quantity         = jQuery(this).children('.product-purchase-quantity').length ? jQuery(this).children('.product-purchase-quantity').val() : 1;
		var product          = jQuery(this).children('.add-to-cart-product-id').attr('value');
		var additionalFields = jQuery( ':input', this ).serializeArray();
		itExchangeSWAddToCart( product, quantity, additionalFields );
		itExchange.hooks.doAction( 'itExchangeSW.addToCartClicked', quantity, product, additionalFields );
	});

	// Register the edit shipping method event from the checkout view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-edit-shipping-method', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'shipping-method' );
		itExchange.hooks.doAction( 'itExchangeSW.editShippingMethodClicked' );
	});

	// Register the cancel shipping method event from the edit method view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-super-widget-shipping-method-cancel-action', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'checkout' );
		itExchange.hooks.doAction( 'itExchangeSW.cancelEditShippingMethodClicked' );
	});

    // Register Change shipping method event
    jQuery( '.it-exchange-super-widget' ).on('change', '.it-exchange-shipping-method-select', function(event) {
        var value = jQuery(this).val();
		itExchangeUpdateShippingMethod( value );
		itExchange.hooks.doAction( 'itExchangeSW.shippingMethodChanged', value );
    });

	/******************************************************
	 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
	 ******************************************************/
	// Register Apply Coupon event for Basic Coupons Add-on
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-update-cart-coupon', function(event) {
	//jQuery(document).on('submit', 'form.it-exchange-sw-update-cart', function(event) {
		event.preventDefault();
		var coupon = jQuery('.apply-coupon', jQuery(this).closest('.it-exchange-super-widget') ).val();
		itExchangeSWApplyCoupon(coupon);
	});

	// Register Remove Coupon event for Basic Coupons Add-on
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.remove-coupon', function(event) {
		event.preventDefault();
		var coupon  = jQuery(this).data('coupon-code');
		itExchangeSWRemoveCoupon(coupon);
	});

	// Register Add / View Coupons Event
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.sw-cart-focus-coupon', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'coupon' );
	});

	// Register Edit / View Quantity Event
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.sw-cart-focus-quantity', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'quantity' );
	});

	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-cancel-login-link', function(event) {
		event.preventDefault();
		if ( itExchangeSWMultiItemCart )
			if ( itExchangeSWOnProductPage )
				itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
			else
				itExchangeGetSuperWidgetState( 'cart' );
		else
			itExchangeSWEmptyCart( itExchangeSWOnProductPage );
	});

	// Register the Register Link event (switching to the register state from the login state)
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-register-link', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'registration' );
		itExchange.hooks.doAction( 'itExchangeSW.registerLinkClicked' );
	});

	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-cancel-register-link', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'login' );
		itExchange.hooks.doAction( 'itExchangeSW.loginLinkClicked' );
	});

	// Register login form submit event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-log-in', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		jQuery(this).find('.it-exchange-login-button').find('input').hide().after('<div style="margin-top:15px;"><div style="float:left;">'+exchangeSWL10n.processingAction+'</div><div class="spinner"></div></div>');
		itExchangeSWLogIn(data);
		itExchange.hooks.doAction( 'itExchangeSW.loginRequested' );
	});

	// Register registration submit event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-register', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		jQuery(this).children('#it-exchange-register-customer').hide().after('<div><div style="float:left;">'+exchangeSWL10n.processingAction+'</div><div class="spinner"></div></div>');
		itExchangeSWRegister(data);
		itExchange.hooks.doAction( 'itExchangeSW.registerRequested' );
	});

	// Register the cancel event from the edit shipping address view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-shipping-address-requirement-cancel', function(event) {
		event.preventDefault();
		if ( itExchangeCartShippingAddress ) {
			itExchangeGetSuperWidgetState( 'checkout' );
		} else {
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
		}
		itExchange.hooks.doAction( 'itExchangeSW.shippingAddressRequirementCancelLinkClicked' );
	});

	// Register the edit event from the edit shipping address  in the checkout view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-edit-shipping-address', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'shipping-address' );
		itExchange.hooks.doAction( 'itExchangeSW.editShippingAddressLinkClicked' );
	});

	// Register Shipping Address submit event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-shipping-address', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		itExchangeSWShippingAddress(data);
		itExchange.hooks.doAction( 'itExchangeSW.editShippingAddressSubmitted' );
	});

	// Register the cancel event from the edit billing address view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-billing-address-requirement-cancel', function(event) {
		event.preventDefault();
		if ( itExchangeCartBillingAddress )
			itExchangeGetSuperWidgetState( 'checkout' );
		else
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
	});

	// Register the edit event from the edit billing address  in the checkout view
	jQuery( '.it-exchange-super-widget' ).on('click', 'a.it-exchange-sw-edit-billing-address', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'billing-address' );
	});

	// Register Billing Address submit event
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-billing-address', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		itExchangeSWBillingAddress(data);
	});

	// Submit the purchase dialog
	jQuery( '.it-exchange-super-widget' ).on('submit', 'form.it-exchange-sw-purchase-dialog', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		var $submit = jQuery(':submit', jQuery(this));
		$submit.attr('value', exchangeSWL10n.processingPaymentLabel).attr('disabled','disabled');
		itExchangeSWSubmitPurchaseDialog(data);
	});

	// Register the edit event from the edit customer order note in the checkout view
	jQuery( '.it-exchange-super-widget' ).on( 'click', 'a.it-exchange-sw-edit-customer-order-note', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'customer-order-note' );
	});

	// Register the submit event from the edit customer order note view
	jQuery( '.it-exchange-super-widget' ).on( 'click', '.it-exchange-customer-order-note-cancel', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'checkout' );
	});

	// Register the cancel event from the edit customer order note view
	jQuery( '.it-exchange-super-widget' ).on( 'submit', '.it-exchange-sw-customer-order-note-form', function(event) {
		event.preventDefault();
		itExchangeSWSubmitNote( jQuery( "#customer-order-note" ).val() );
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
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=get-state&state=' + state + productArg + focusArg, function(data) {
		jQuery('.it-exchange-super-widget').filter(':visible').html(data);
		itExchangeSWState = state;

		if ( 'checkout' == state )
			itExchangeInitSWPurchaseDialogs();

		if ( 'shipping-address' == state || ( 'checkout' == state && ! itExchangeCartShippingAddress ) ) {
			var shippingSyncOptions = {
				statesWrapper: '.it-exchange-state',
				stateFieldID:  '#it-exchange-shipping-address-state',
				templatePart:  'super-widget-shipping-address/elements/state',
				autoCompleteState: true
			};
			itExchangeInitSWCountryStateSync('#it-exchange-shipping-address-country', shippingSyncOptions);
		}

		if ( 'billing-address' == state || ( 'checkout' == state && ! itExchangeCartBillingAddress ) ) {
			var billingSyncOptions = {
				statesWrapper: '.it-exchange-state',
				stateFieldID:  '#it-exchange-billing-address-state',
				templatePart:  'super-widget-billing-address/elements/state',
				autoCompleteState: true
			};
			itExchangeInitSWCountryStateSync('#it-exchange-billing-address-country', billingSyncOptions);
		}
		itExchange.hooks.doAction( 'itExchangeSW.stateUpdated' );
	});

}

/**
 * Makes an ajax request to buy a product and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWBuyNow( product, quantity, additionalFields ) {
	additionalFieldsString = '';
	jQuery.each(additionalFields, function(index,field) {
		if ( typeof field.name != 'undefined' && typeof field.value != 'undefined' && field.name != 'it-exchange-action' && field.name != 'it-exchange-buy-now' && field.name != '_wpnonce' && field.name != '_wp_http_referer' ) {
			additionalFieldsString = '&' + field.name + '=' + field.value;
		}
	});
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=buy-now&sw-product=' + product + '&sw-quantity=' + quantity + additionalFieldsString, function(data) {
		itExchangeGetSuperWidgetState( 'checkout', product );

		itExchange.hooks.doAction( 'itExchangeSW.BuyNow' );
	});
}

/**
 * Makes an ajax request to add a product to the cart and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWAddToCart( product, quantity, additionalFields ) {
	var additionalFieldsString = '';
	jQuery.each(additionalFields, function(index,field) {
		if ( typeof field.name != 'undefined' && typeof field.value != 'undefined' && field.name != 'it-exchange-action' && field.name != 'it-exchange-add-product-to-cart' && field.name != '_wpnonce' && field.name != '_wp_http_referer' ) {
			additionalFieldsString = '&' + field.name + '=' + field.value;
		}
	});
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=add-to-cart&sw-product=' + product + '&sw-quantity=' + quantity + additionalFieldsString, function(data) {
		if ( itExchangeSWMultiItemCart ) {
			itExchangeGetSuperWidgetState( 'cart', product );
		} else {
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		}

		itExchange.hooks.doAction( 'itExchangeSW.addToCart' );
	});
}

/**
 * Makes an ajax request that changes the selected shipping method and then refresh the shipping-method state
*/
function itExchangeUpdateShippingMethod( value ) {
	jQuery.get(itExchangeSWAjaxURL+'&sw-action=update-shipping-method&sw-shipping-method='+value, function(response) {
		if ( response )
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		else
			itExchangeGetSuperWidgetState( 'shipping-method' );

		itExchange.hooks.doAction( 'itExchangeSW.UpdateShippingMethod' );
	});
}

/******************************************************
 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
 ******************************************************/
/**
 * Makes AJAX request to Apply a coupon to the cart
*/
function itExchangeSWApplyCoupon(coupon) {
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=apply-coupon&sw-coupon-type=cart&sw-coupon-code=' + coupon, function(data) {
		if ( 'levelup' == data ) {
			jQuery('.it-exchange-super-widget').filter(':visible').html(
				'<div class="nes-super-widget"><iframe width="420" height="315" src="//www.youtube.com/embed/4gWQn0Qo1Bo?autoplay=1&start=17" frameborder="0" allowfullscreen></iframe></div>'
			);
			setTimeout( function() { itExchangeGetSuperWidgetState( 'checkout' ) }, 25000 );
		} else {
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		}

		itExchange.hooks.doAction( 'itExchangeSW.applyCoupon', coupon );
	});
}
/**
 * Remove a coupon from the cart
*/
function itExchangeSWRemoveCoupon(coupon) {
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=remove-coupon&sw-coupon-type=cart&sw-coupon-code=' + coupon, function(data) {
		itExchangeGetSuperWidgetState( 'checkout' );

		itExchange.hooks.doAction( 'itExchangeSW.removeCoupon', coupon );
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
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=empty-cart', function(data) {
		if ( itExchangeSWOnProductPage )
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
		else
			itExchangeGetSuperWidgetState( 'cart' );

		itExchange.hooks.doAction( 'itExchangeSWEmptyCart' );
	});
}

/**
 * Makes an ajax request to remove an item from the cart
*/
function itExchangeSWRemoveItemFromCart( product ) {
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=remove-from-cart&sw-cart-product=' + product, function(data) {
		if ( itExchangeSWMultiItemCart ) {
			if ( itExchangeSWOnProductPage )
				itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
			else
				itExchangeGetSuperWidgetState( 'cart' );
		} else {
			itExchangeGetSuperWidgetState( 'checkout' );
		}

		itExchange.hooks.doAction( 'itExchangeSW.removeItemFromCart', product );
	});
}

/**
 * Update Quantity
*/
function itExchangeSWUpdateQuantity(product, quantity) {
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=update-quantity&sw-cart-product=' + product + '&sw-quantity=' + quantity );
}

/**
 * Log the user in
*/
function itExchangeSWLogIn(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=login', data, function(data) {
		if ( '0' === data ) {
			itExchangeIsUserLoggedIn = '';
			itExchangeGetSuperWidgetState( 'login' );
		} else {
			itExchangeIsUserLoggedIn = '1';
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );

			itExchange.hooks.doAction( 'itExchangeSW.userLoggedIn' );
		}
	});
}

/**
 * Register a new users
*/
function itExchangeSWRegister(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=register', data, function(data) {
		if ( '0' === data ) {
			itExchangeGetSuperWidgetState('registration');
		} else {
			itExchangeGetSuperWidgetState('checkout', itExchangeSWOnProductPage);

			itExchange.hooks.doAction( 'itExchangeSW.userRegistered' );
		}
	});
}

/**
 * Update the shipping address
*/
function itExchangeSWShippingAddress(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=update-shipping', data, function(data) {
		itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
	});
}

/**
 * Update the billing address
*/
function itExchangeSWBillingAddress(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=update-billing', data, function(data) {
		itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
	});
}

/**
 * Submit the purchase dialog
*/
function itExchangeSWSubmitPurchaseDialog(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=submit-purchase-dialog', data, function(data) {
		if (0 != data) {
			window.location.href = data;
		} else {
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
			itExchangeInitSWPurchaseDialogs();
		}
	}).fail( function() {
		itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		itExchangeInitSWPurchaseDialogs();
	});
}

/**
 * Submit the order note from the customer.
 *
 * @since 1.34
 *
 * @param note
 */
function itExchangeSWSubmitNote( note ) {
	jQuery.post( itExchangeSWAjaxURL + '&sw-action=customer-order-note', {note: note}, function( response ) {
		itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
	});
}

/**
 * Init purchase dialog JS
*/
function itExchangeInitSWPurchaseDialogs() {

	// Hide all dialogs
	jQuery('.it-exchange-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	jQuery( '.it-exchange-purchase-dialog-trigger' ).on( 'click', function(event) {
		event.preventDefault();
		var addon_slug = jQuery(this).data('addon-slug');
		jQuery('.it-exchange-purchase-dialog-trigger', jQuery(this).closest('.it-exchange-super-widget')).hide();
		jQuery('form', jQuery(this).closest('.payment-methods-wrapper')).not('.it-exchange-purchase-dialog-' + addon_slug).hide();
		jQuery('.it-exchange-purchase-dialog-' + addon_slug, jQuery(this).closest('.payment-methods-wrapper') ).show();
	});

	// Open any dialog that has errors, hide the rest of the buttons
	jQuery('.it-exchange-purchase-dialog-trigger').filter('.has-errors').trigger('click');

	// Cancel
	jQuery( '.it-exchange-purchase-dialog-cancel' ).on( 'click', function(event) {
		event.preventDefault();
		jQuery('.it-exchange-purchase-dialog', jQuery(this).closest('.it-exchange-super-widget') ).hide();
		jQuery('.it-exchange-purchase-dialog-trigger', jQuery(this).closest('.it-exchange-super-widget')).show();
		jQuery('form', '.payment-methods-wrapper', jQuery(this).closest('.it-exchange-super-widget')).show();
	});

	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-month"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-year"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-code"]' ).payment( 'formatCardCVC' );

	var ccNumbers = jQuery( 'input[name="it-exchange-purchase-dialog-cc-number"]' );
	ccNumbers.payment( 'formatCardNumber' );

	ccNumbers.each(function() {

		var $this = jQuery( this );

		$this.it_exchange_detect_credit_card_type({
			'element' : '#' + $this.attr('id')
		});
	});
}

/**
 * Inits the sync plugin for country/state fields for billing address
*/
function itExchangeInitSWCountryStateSync(countryElement, options) {
	jQuery(countryElement, jQuery('.it-exchange-super-widget').filter(':visible')).itCountryStatesSync(options).selectToAutocomplete();
	jQuery(options.stateFieldID, jQuery('.it-exchange-super-widget').filter(':visible')).selectToAutocomplete();
}
