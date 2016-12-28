(function ( api, Common, $, _, APIConfig, ProfileConfig ) {
	"use strict";

	$( document ).ready( function () {
		setTimeout( function () {
			app.start();
		}, 100 );
	} );

	var app = {

		Views        : {},
		TokensView   : {},
		AddressesView: {},

		start: function () {

			var customer = new api.Models.Customer( ProfileConfig.customer );
			var tokens = new api.Collections.PaymentTokens( ProfileConfig.tokens, {
				parent: customer
			} );

			app.TokensView = new app.Views.ManageTokens( {
				collection: tokens
			} );
			app.TokensView.inject( '.it-exchange-manage-tokens-container' );

			var defCountries = $.Deferred(), defStates = $.Deferred();

			$.get( Common.getRestUrl( 'datasets/countries' ), ( function ( response ) {
				if ( !response.data ) {
					return defCountries.reject();
				}

				var html = '';

				for ( var countryCode in response.data ) {
					if ( response.data.hasOwnProperty( countryCode ) ) {
						html += '<option value="' + countryCode + '">' + response.data[countryCode] + '</option>';
					}
				}

				defCountries.resolve( html );
			}).bind( this ) );

			$.get( Common.getRestUrl( 'datasets/states', { country: 'all' } ), ( function ( response ) {

				if ( !response.data ) {
					return defStates.reject();
				}

				var countries = response.data;
				var statesHtml = {};

				for ( var countryCode in countries ) {
					var html = '';

					if ( countries.hasOwnProperty( countryCode ) ) {
						var states = countries[countryCode];

						for ( var stateCode in states ) {
							if ( states.hasOwnProperty( stateCode ) ) {
								html += '<option value="' + stateCode + '">' + states[stateCode] + '</option>';
							}
						}

						statesHtml[countryCode] = html;
					}
				}

				defStates.resolve( statesHtml );
			}).bind( this ) );

			if ( ProfileConfig.billing ) {
				customer.billingAddress().set( ProfileConfig.billing );
			}

			if ( ProfileConfig.shipping ) {
				customer.shippingAddress().set( ProfileConfig.shipping );
			}

			app.AddressesView = new app.Views.ManageAddresses( {
				model    : customer,
				countries: defCountries,
				states   : defStates,
			} );
			app.AddressesView.inject( '.it-exchange-customer-addresses-container' );
		}
	};

	app.Views.ManageAddresses = api.View.extend( {

		template: wp.template( 'it-exchange-customer-addresses' ),

		countries : '',
		states    : {},
		addNewView: null,

		initialize: function ( options ) {

			var b, s;

			if ( b = this.model.billingAddress() ) {
				this.views.add(
					'.it-exchange-customer-addresses-container',
					new app.Views.ManageAddressesAddress( {
						model : b,
						type  : 'billing',
						parent: this,
					} )
				);
			}

			if ( s = this.model.shippingAddress() ) {
				this.views.add(
					'.it-exchange-customer-addresses-container',
					new app.Views.ManageAddressesAddress( {
						model : s,
						type  : 'shipping',
						parent: this,
					} )
				);
			}

			var addresses = this.model.addresses();

			this.listenTo( addresses, 'add', function ( address ) {

				var options = {};

				if ( this.addNewView ) {
					options.at = this.views.get( '.it-exchange-customer-addresses-container' ).length - 1;
				}

				this.views.add(
					'.it-exchange-customer-addresses-container', new app.Views.ManageAddressesAddress( {
						model : address,
						parent: this,
					} ),
					options
				);
			} );

			addresses.fetch();

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();

			$.when( options.countries, options.states ).done( (function ( countries, states ) {
				this.countries = countries;
				this.states = states;
				this.trigger( 'exchange.datasetsLoaded', countries, states );
				this.views.add( '.it-exchange-customer-addresses-container', this.addNewView = new app.Views.CreateAddress( {
					collection: addresses,
					countries : countries,
					states    : states,
				} ) );
			}).bind( this ) );
		},

		render: function () {
			var attr = { i18n: ProfileConfig.i18n };
			this.$el.html( this.template( attr ) );
			this.views.render();
		},
	} );

	app.Views.ManageAddressesAddress = api.View.extend( {

		className: 'it-exchange-customer-address',
		template : wp.template( 'it-exchange-customer-addresses-address' ),

		events: {
			'click .it-exchange-customer-addresses-address-action--edit'  : 'onEditClicked',
			'click .it-exchange-customer-addresses-address-action--delete': 'onDeleteClicked',
		},

		type  : '',
		parent: null,
		editor: null,

		initialize: function ( options ) {
			this.type = options.type;
			this.parent = options.parent;

			this.listenTo( this.model, 'change', this.render );

			if ( this.parent.countries && !_.isEmpty( this.parent.states ) ) {
				this.initializeEditor( this.parent.countries, this.parent.states );
			} else {
				this.listenTo( this.parent, 'exchange.datasetsLoaded', this.onEditReady );
			}
		},

		onEditReady: function ( countries, states ) {
			this.initializeEditor( countries, states );
		},

		initializeEditor: function ( countries, states ) {

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).show();
			this.editor = new api.Views.AddressForm( {
				model    : this.model,
				countries: countries,
				states   : states,
			} );
			this.views.add( '.it-exchange-customer-addresses-edit-address-container', this.editor );

			this.listenTo( this.editor, 'exchange.closedFromSave', this.onEditorSaved );
			this.listenTo( this.editor, 'exchange.closedFromCancel', this.closeEditor );
		},

		onEditClicked: function ( e ) {
			this.openEditor();
		},

		onDeleteClicked: function () {
			this.model.destroy( {
				success: (function () {
					this.remove();
				}).bind( this )
			} );

			this.$el.css( 'opacity', '.2' );
		},

		openEditor: function () {
			this.$( '.it-exchange-customer-addresses-edit-address-container' ).show();
			this.$( '.it-exchange-customer-addresses-view-address-container' ).hide();
			this.$el.addClass( 'editing' );
		},

		closeEditor: function () {
			this.$( '.it-exchange-customer-addresses-edit-address-container' ).hide();
			this.$( '.it-exchange-customer-addresses-view-address-container' ).show();
			this.$el.removeClass( 'editing' );
		},

		onEditorSaved: function ( saved ) {

			this.closeEditor();

			if ( _.isEmpty( saved ) ) {
				return;
			}

			var $editAction = this.$( '.it-exchange-customer-addresses-address-action--edit' );

			$editAction.hide();

			this.listenToOnce( this.model, 'sync', function () {
				$editAction.show();
			} );
		},

		render: function () {
			this.statesLoaded = this.countriesLoaded = false;

			var attr = this.model.toJSON();
			attr.formattedAddress = this.model.formatted();
			attr.i18n = ProfileConfig.i18n;

			if ( this.type ) {
				attr.renderedLabel = attr.i18n[this.type + 'Label'];

				if ( this.model.get( 'label' ) ) {
					attr.renderedLabel += ' – ' + this.model.get( 'label' );
				}
			} else {
				attr.renderedLabel = this.model.get( 'label' );
			}

			this.$el.html( this.template( attr ) );
			this.views.render();

			if ( !this.editor ) {
				this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();
			}

			if ( this.type ) {
				this.$( '.it-exchange-customer-addresses-address-action--delete' ).hide();
			}
		},

		_removeElement: function () {
			this.$el.fadeOut( 1000, (function () {
				this.$el.remove();
			}).bind( this ) );
		},
	} );

	app.Views.CreateAddress = api.View.extend( {
		template : wp.template( 'it-exchange-customer-addresses-create-address' ),
		className: 'it-exchange-add-customer-address',

		events: {
			'click .it-exchange-customer-addresses-address-action--create': 'onCreateClicked'
		},

		editor: null,

		initialize: function ( options ) {
			this.editor = new api.Views.AddressForm( {
				collection: this.collection,
				countries : options.countries,
				states    : options.states,
			} );

			this.views.add( '.it-exchange-customer-addresses-create-address-form-container', this.editor );

			this.listenTo( this.editor, 'exchange.closedFromCreate', this.onCreated );
			this.listenTo( this.editor, 'exchange.closedFromCancel', this.onCancel );
		},

		onCreateClicked: function () {
			this.openEditor();
		},

		onCreated: function () {
			this.closeEditor();
			this.editor.render();
		},

		onCancel: function () {
			this.closeEditor();
			this.editor.render();
		},

		openEditor: function () {
			this.$( '.it-exchange-customer-addresses-address-action--create' ).hide();
			this.$( '.it-exchange-customer-addresses-create-address-form-container' ).show();
			this.$el.css( 'border', '1px #ddd solid' );
			this.$el.addClass( 'editing' );
		},

		closeEditor: function () {
			this.$( '.it-exchange-customer-addresses-address-action--create' ).show();
			this.$( '.it-exchange-customer-addresses-create-address-form-container' ).hide();
			this.$el.css( 'border', 'none' );
			this.$el.removeClass( 'editing' );
		},

		render: function () {
			var attr = { i18n: ProfileConfig.i18n };
			this.$el.html( this.template( attr ) );
			this.views.render();
		},
	} );

	app.Views.ManageTokens = api.View.extend( {

		manager: null,

		initialize: function () {
			this.manager = new api.Views.PaymentTokensManager( { collection: this.collection } );
			this.views.add( this.manager );
		},
	} );

})( window.ITExchangeAPI, window.ExchangeCommon, jQuery, window._, window.ITExchangeRESTConfig, window.ITExchangeProfileConfig );