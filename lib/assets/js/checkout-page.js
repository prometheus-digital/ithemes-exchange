(function ( $, api, config ) {

	var orderNotesForm = $( ".it-exchange-customer-order-notes-form" );
	var orderNotesSummary = $( ".it-exchange-customer-order-notes-summary" );

	$( ".it-exchange-edit-customer-order-notes" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.hide();
		orderNotesForm.show();
	} );

	$( ".it-exchange-customer-order-note-cancel" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.show();
		orderNotesForm.hide();
	} );

	var app = window.ITExchangeCheckout = {

		Views: {},

		additionalInfo: null,
		methodViews   : {},

		start: function ( cart ) {

			if ( cart.customer() ) {
				cart.customer().tokens().fetch();
			}

			app.additionalInfo = new ( api.View.extend( {
				template: function () {return ''}
			} ) );
			app.additionalInfo.inject( '#it-exchange-checkout-transaction-methods-additional-info-container' );

			cart.getPurchaseMethods().forEach( function ( purchaseMethod ) {
				var view = new app.Views.PurchaseMethod( {
					cart      : cart,
					model     : purchaseMethod,
					addView   : app.addAdditionalInfoView,
					removeView: app.removeAdditionalInfoView,
				} );

				app.methodViews[purchaseMethod.id] = view;
				view.render();

				$( "#it-exchange-transaction-method-container-" + purchaseMethod.id ).append( view.$el );
			} );

			$( document ).on( 'click', 'button.it-exchange-checkout-purchase-method-button', function () {
				$( '.it-exchange-checkout-transaction-methods' ).hide();
			} );

			$( document ).on( 'click', '.it-exchange-checkout-cancel-purchase-method', function () {
				$( '.it-exchange-checkout-transaction-methods' ).show();
			} );
		},

		addAdditionalInfoView: function ( view ) {
			app.additionalInfo.views.add( view );
		},

		removeAdditionalInfoView: function ( view ) {
			app.additionalInfo.views.unset( view );
		},
	};

	app.Views.PurchaseMethod = api.Views.PurchaseMethod.extend( {
		tagName: 'div',

	} );

	api.loadCart().done( function () {
		var cart = new api.Models.Cart( config.cart );
		var methods = cart.getPurchaseMethods();
		methods.reset( config.methods );

		app.start( cart );
	} );

})( jQuery, ITExchangeAPI, ITExchangeRESTCheckoutConfig );
