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

			if ( ProfileConfig.tokens.length ) {
				var tokens = new api.Collections.PaymentTokens( ProfileConfig.tokens, {
					parent: customer
				} );

				app.TokensView = new app.Views.ManageTokens( {
					collection: tokens
				} );
				app.TokensView.inject( '.it-exchange-manage-tokens-container' );
			}

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

		// Promises that resolve to country options and state options.
		countries: null,
		states   : null,

		billingView : null,
		shippingView: null,
		addNewView  : null,

		initialize: function ( options ) {

			this.countries = options.countries;
			this.states = options.states;

			var b, s;

			if ( b = this.model.billingAddress() ) {
				this.billingView = this.addAddressView( b, 'billing' );
			}

			if ( s = this.model.shippingAddress() ) {
				if ( b === s ) {
					s = s.clone();
				}

				this.shippingView = this.addAddressView( s, 'shipping' );
			}

			var addresses = this.model.addresses();

			this.listenTo( addresses, 'add', (function ( address ) {this.addAddressView( address )}).bind( this ) );

			addresses.fetch();

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();

			$.when( this.countries, this.states ).done( (function ( countries, states ) {
				this.views.add( '.it-exchange-customer-addresses-container', this.addNewView = new app.Views.CreateAddress( {
					collection: addresses,
					countries : countries,
					states    : states,
				} ) );
			}).bind( this ) );
		},

		/**
		 * When a new primary address is selected for a given type,
		 * adjust the existing primary views.
		 *
		 * @since 2.0.0
		 *
		 * @param {app.Views.ManageAddressesAddress} newPrimaryView
		 * @param {String} type Primray address type, either 'billing' or 'shipping'.
		 */
		onNewPrimary: function ( newPrimaryView, type ) {

			var key = type + 'View';

			var currentView = this[key];
			this[key] = newPrimaryView;

			if ( !currentView ) {
				return;
			}

			currentView.type = '';
			currentView.setupContextMenu();
			currentView.render();
		},

		/**
		 * Add an address view.
		 *
		 * @since 2.0.0
		 *
		 * @param {api.Models.Address} model Address model.
		 * @param {String} [type] Address type. Either 'billing' or 'shipping' or empty.
		 *
		 * @returns {app.Views.ManageAddressesAddress}
		 */
		addAddressView: function ( model, type ) {

			type = type || '';

			var view = new app.Views.ManageAddressesAddress( {
				model    : model,
				type     : type,
				countries: this.countries,
				states   : this.states,
			} );

			this.listenTo( this.view, 'exchange.newPrimarySelected', this.onNewPrimary );

			var options = {};

			if ( this.addNewView && !type.length ) {
				options.at = this.views.get( '.it-exchange-customer-addresses-container' ).length - 1;
			}

			this.views.add( '.it-exchange-customer-addresses-container', view, options );

			return view;
		},

		render: function () {
			var attr = { i18n: ProfileConfig.i18n };
			this.$el.html( this.template( attr ) );
			this.views.render();
		},
	} );

	app.Views.ManageAddressesAddress = api.View.extend( {

		className     : 'it-exchange-customer-address',
		template      : wp.template( 'it-exchange-customer-addresses-address' ),
		animateRemoval: true,

		events: {
			'click .it-exchange-customer-addresses-address-action--edit'  : 'onEditClicked',
			'click .it-exchange-customer-addresses-address-action--delete': 'onDeleteClicked',
		},

		type  : '',
		editor: null,

		initialize: function ( options ) {
			this.type = options.type;

			if ( !this.type.length && this.model.parentModel ) {
				this.setupContextMenu();
			}

			this.listenTo( this.model, 'change', this.render );

			$.when( options.countries, options.states ).done( (function ( countries, states ) {
				this.initializeEditor( countries, states );
			} ).bind( this ) );

			api.View.prototype.initialize.apply( this, arguments );
		},

		setupContextMenu: function () {
			this.$el.contextMenu( {
				selector: '.it-exchange-customer-addresses-address-action--primary',
				trigger : 'left',
				items   : {
					billing : { name: ProfileConfig.i18n.billingLabel },
					shipping: { name: ProfileConfig.i18n.shippingLabel },
				},
				callback: ( function ( key ) {
					this.model.parentModel.save( key + '_address', this.model.id, {
						success: (function () {
							this.type = key;
							this.render();
							this.trigger( 'exchange.newPrimarySelected', this, key );
						}).bind( this )
					} );
				}).bind( this ),
			} );
		},
		initializeEditor: function ( countries, states ) {

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).show();
			this.editor = new api.Views.AddressForm( {
				model    : this.model,
				countries: countries,
				states   : states,
				type     : this.type,
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
				wait   : true,
				success: (function () {
					this.remove();
				}).bind( this )
			} );
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

			if ( this.type.length ) {
				this.$el.addClass( 'it-exchange-customer-address--primary' );
				attr.renderedLabel = attr.i18n[this.type + 'Label'];

				if ( !attr.renderedLabel ) {
					attr.renderedLabel = this.model.get( 'label' ) || '';
				} else if ( this.model.get( 'label' ) ) {
					attr.renderedLabel += ' – ' + this.model.get( 'label' );
				}
			} else {
				this.$el.removeClass( 'it-exchange-customer-address--primary' );
				attr.renderedLabel = this.model.get( 'label' );
			}

			this.$el.html( this.template( attr ) );
			this.views.render();

			if ( !this.editor ) {
				this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();
			}

			if ( this.type.length ) {
				this.$( '.it-exchange-customer-addresses-address-action--delete' ).hide();
				this.$( '.it-exchange-customer-addresses-address-action--primary' ).prop( 'disabled', true );
			}
		},
	} );

	app.Views.CreateAddress = api.View.extend( {
		template      : wp.template( 'it-exchange-customer-addresses-create-address' ),
		className     : 'it-exchange-add-customer-address',
		animateRemoval: true,

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

			api.View.prototype.initialize.apply( this, arguments );
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

})( window.itExchange.api, window.itExchange.common, jQuery, window._, window.ITExchangeRESTConfig, window.ITExchangeProfileConfig );