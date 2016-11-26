/**
 * This gets loaded on the checkout page.
 */
// Bind to page load
jQuery( document ).ready( 'itExchangeInitPurchaseDialogs' );

// Bind our init to the custom jQuery trigger that fires when the Checkout page is reloaded.
jQuery( document ).on( 'itExchangeCheckoutReloaded', itExchangeInitPurchaseDialogs );

// Function to init
function itExchangeInitPurchaseDialogs() {
	// Hide all dialogs
	jQuery( '.it-exchange-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	jQuery( '.it-exchange-purchase-dialog-trigger' ).on( 'click', function ( event ) {
		event.preventDefault();
		var addon_slug = jQuery( this ).data( 'addon-slug' );
		jQuery( '.it-exchange-purchase-dialog-trigger' ).hide();
		jQuery( 'form', '.it-exchange-checkout-transaction-methods' ).hide();
		jQuery( 'form', '.it-exchange-purchase-dialog-' + addon_slug ).show();
		jQuery( '.it-exchange-purchase-dialog-' + addon_slug ).show();
	} );

	// Open any dialog that has errors, hide the rest of the buttons
	jQuery( '.it-exchange-purchase-dialog-trigger' ).filter( '.has-errors' ).trigger( 'click' );

	// Cancel
	jQuery( '.it-exchange-purchase-dialog-cancel' ).on( 'click', function ( event ) {
		event.preventDefault();
		jQuery( '.it-exchange-purchase-dialog' ).hide();
		jQuery( '.it-exchange-purchase-dialog-trigger' ).show();
		jQuery( 'form', '.it-exchange-checkout-transaction-methods' ).show();
	} );

	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-month"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-year"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-code"]' ).payment( 'formatCardCVC' );

	var ccNumbers = jQuery( 'input[name="it-exchange-purchase-dialog-cc-number"]' );
	ccNumbers.payment( 'formatCardNumber' );

	ccNumbers.each( function () {

		var $this = jQuery( this );

		$this.it_exchange_detect_credit_card_type( {
			'element': '#' + $this.attr( 'id' )
		} );
	} );

	jQuery( document ).on( 'click', '.it-exchange-credit-card-selector input', function () {
		var $this = jQuery( this );
		var $visualCC = jQuery( '.it-exchange-visual-cc-wrap', $this.closest( '.it-exchange-purchase-dialog' ) );

		if ( $this.is( ':checked' ) && $this.val() === 'new_method' ) {
			$visualCC.show();
		} else {
			$visualCC.hide();
		}
	} );

	if ( itExchange && itExchange.hooks ) {
		itExchange.hooks.addAction( 'itExchangeSW.preSubmitPurchaseDialog', function ( method, form, deferred ) {

			if ( ITExchangeAPI && ITExchangeAPI.canTokenize( method ) ) {

				var card = {
					number: jQuery( 'input[name="it-exchange-purchase-dialog-cc-number"]', form ).val(),
					year  : jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-year"]', form ).val(),
					month : jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-month"]', form ).val(),
					cvc   : jQuery( 'input[name="it-exchange-purchase-dialog-cc-code"]', form ).val(),
					name  : jQuery( 'input[name="it-exchange-purchase-dialog-cc-first-name"]', form ).val() + ' ' +
					jQuery( 'input[name="it-exchange-purchase-dialog-cc-last-name"]', form ).val(),
				};

				ITExchangeAPI.tokenize( method, 'card', card ).done( function ( tokenize ) {
					deferred.resolve( {
						to_tokenize: tokenize,
						_wpnonce   : jQuery( 'input[name="_wpnonce"]', form ).val(),

						'it-exchange-transaction-method': jQuery( 'input[name="it-exchange-transaction-method"]', form ).val()
					} )
				} );
			} else {
				deferred.resolve();
			}
		} );
	}
}

// Finally, since its printed half way through - call it as well.
itExchangeInitPurchaseDialogs();
