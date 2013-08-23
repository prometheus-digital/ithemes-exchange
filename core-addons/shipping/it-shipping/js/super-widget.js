/**
 * Any events need to be connected with jQuery(document).on([event], [selector], [function/callback];
 *
*/
jQuery(function(){

	// Register Submit Address action
	jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-addon-shipping-continue', function(event) {
		event.preventDefault();
		var addressObject = {};
		var thisWidget = jQuery(this).parent('.it-exchange-sw-processing-shipping-checkout');
		addressObject.shippingName     = jQuery('.it-exchange-addon-shipping-name', thisWidget ).val();
		addressObject.shippingAddress1 = jQuery('.it-exchange-addon-shipping-address-1', thisWidget ).val();
		addressObject.shippingAddress2 = jQuery('.it-exchange-addon-shipping-address-2', thisWidget ).val();
		addressObject.shippingCity     = jQuery('.it-exchange-addon-shipping-city', thisWidget ).val();
		addressObject.shippingState    = jQuery('.it-exchange-addon-shipping-state', thisWidget ).val();
		addressObject.shippingCountry  = jQuery('.it-exchange-addon-shipping-country', thisWidget ).val();
		addressObject.shippingZip      = jQuery('.it-exchange-addon-shipping-zip', thisWidget ).val();
		addressObject.shippingCustomer = jQuery('.it-exchange-addon-shipping-customer', thisWidget ).val();
		itExchangeShippingSWContinue( addressObject );
	}); 

	// Register action to update shipping
	jQuery(document).on('click', '.it-exchange-super-widget a.update-shipping-address', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'shipping' );
	});
});

/**
 * Attempts to add the Shipping Data to the cart Session
 * Sets the requirement to met if successful
 * @since 1.0.0
*/
function itExchangeShippingSWContinue( addressObject ) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=update-shipping', addressObject, function(data) {
		if ( '0' === data )
			itExchangeGetSuperWidgetState( 'shipping' );
		else
			itExchangeGetSuperWidgetState( 'checkout' );
	}); 
}
