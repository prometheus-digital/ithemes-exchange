/**
 * jQuery used by the Billing Address Purchase Requirement on the Checkout Page
 * @since 1.3.0
*/
jQuery( function() {
	// Switch to edit address view when link is clicked
	jQuery(document).on('click', 'a.it-exchange-purchase-requirement-edit-billing', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-billing-address-options').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-billing-address-edit').removeClass('it-exchange-hidden');
	});

	// Switch to existing address view when clancel link is clicked
	jQuery(document).on('click', 'a.it-exchange-billing-address-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-billing-address-options').removeClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-billing-address-edit').addClass('it-exchange-hidden');
	});

	// Init country state sync
	var iteCountryStatesSyncOptions = {
		statesWrapper: '.it-exchange-state',
		stateFieldID:  '#it-exchange-billing-address-state',
		templatePart:  'content-checkout/elements/purchase-requirements/billing-address/elements/state'
	};
	jQuery('#it-exchange-billing-address-country').itCountryStatesSync(iteCountryStatesSyncOptions);
});
