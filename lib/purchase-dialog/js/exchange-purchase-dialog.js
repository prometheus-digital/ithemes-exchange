jQuery( document ).ready( function( $ ) {

	// Hide all dialogs
	$('.it-exchange-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	$( '.it-exchange-purchase-dialog-trigger' ).on( 'click', function(event) {

		//itExchangePurchaseDialogDoOverlay();

		event.preventDefault();
		var addon_slug = $(this).data('addon-slug');
		$('.it-exchange-purchase-dialog-trigger').hide();
		$('form', '.it-exchange-checkout-transaction-methods').hide();
		$('form', '#it-exchange-purchase-dialog-' + addon_slug).show();
		$('#it-exchange-purchase-dialog-' + addon_slug ).show();
		console.log('#it-exchange-purchase-dialog-' + addon_slug);
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

function itExchangePurchaseDialogDoOverlay() {
var docHeight = jQuery(document).height();

   jQuery("body").append("<div id='overlay'></div>");

   jQuery("#overlay")
      .height(docHeight)
      .css({
         'opacity' : 0.4,
         'position': 'absolute',
         'top': 0,
         'left': 0,
         'background-color': 'black',
         'width': '100%',
         'z-index': 5000
      });
}
