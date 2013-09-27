/**
 * jQuery used by the Shipping Address Purchase Requirement on the Checkout Page
 * @since 1.3.0
*/
jQuery( function() {
	// Switch to edit address view when link is clicked
	jQuery(document).on('click', 'a.it-exchange-purchase-requirement-edit-shipping', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-shipping-address-options').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-shipping-address-edit').removeClass('it-exchange-hidden');
	});

	// Switch to existing address view when clancel link is clicked
	jQuery(document).on('click', 'a.it-exchange-shipping-address-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-shipping-address-options').removeClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-shipping-address-edit').addClass('it-exchange-hidden');
	});

	// Init country state sync
	var iteCountryStatesSyncOptions = {
		statesWrapper: '.it-exchange-state',
		stateFieldID:  '#it-exchange-shipping-address-state',
		templatePart:  'content-checkout/elements/purchase-requirements/shipping-address/elements/state'
	};
	jQuery('#it-exchange-shipping-address-country').itCountryStatesSync(iteCountryStatesSyncOptions);
});
