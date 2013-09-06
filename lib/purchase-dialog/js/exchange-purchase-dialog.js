/**
 * This gets loaded on the checkout page. 
*/
jQuery( document ).ready( function( $ ) {
	// Hide all dialogs
	$('.it-exchange-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	$( '.it-exchange-purchase-dialog-trigger' ).on( 'click', function(event) {
		event.preventDefault();
		var addon_slug = $(this).data('addon-slug');
		$('.it-exchange-purchase-dialog-trigger').hide();
		$('form', '.it-exchange-checkout-transaction-methods').hide();
		$('form', '.it-exchange-purchase-dialog-' + addon_slug).show();
		$('.it-exchange-purchase-dialog-' + addon_slug ).show();
	});

	// Open any dialog that has errors, hide the rest of the buttons
	$('.it-exchange-purchase-dialog-trigger').filter('.has-errors').trigger('click');

	// Cancel
	$( '.it-exchange-purchase-dialog-cancel' ).on( 'click', function(event) {
		event.preventDefault();
		$('.it-exchange-purchase-dialog' ).hide();
		$('.it-exchange-purchase-dialog-trigger').show();
		$('form', '.it-exchange-checkout-transaction-methods').show();
	});
});
