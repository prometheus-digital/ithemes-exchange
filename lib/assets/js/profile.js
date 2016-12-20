(function ( api, Common, $, _, APIConfig, ProfileConfig ) {
	"use strict";

	$( document ).ready( function () {
		app.start();
	} );

	var app = {

		Views: {},
		View : {},

		start: function () {

			var tokens = new api.Collections.PaymentTokens( ProfileConfig.tokens, {
				parent: new api.Models.Customer( { id: ProfileConfig.customer } )
			} );

			app.View = new app.Views.Manage( {
				tokens: tokens
			} );
			app.View.inject( '.it-exchange-manage-tokens-container' );
		}
	};

	app.Views.Manage = api.View.extend( {

		manager: null,

		initialize: function ( options ) {
			this.manager = new api.Views.PaymentTokensManager( { collection: options.tokens } );
			this.views.add( this.manager );
		},
	} );

})( window.ITExchangeAPI, window.ExchangeCommon, jQuery, window._, window.ITExchangeRESTConfig, window.ITExchangeProfileConfig );