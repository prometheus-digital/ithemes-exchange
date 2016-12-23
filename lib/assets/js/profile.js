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

			var customer = new api.Models.Customer( { id: ProfileConfig.customer } );
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

			customer.fetch( { embed: true } ).done( function () {
				app.AddressesView = new app.Views.ManageAddresses( {
					model    : customer,
					countries: defCountries,
					states   : defStates
				} );
				app.AddressesView.inject( '.it-exchange-manage-address-container' );
			} );
		}
	};

	app.Views.ManageAddresses = api.View.extend( {

		template: wp.template( 'it-exchange-manage-addresses' ),

		countries: '',
		states   : {},

		initialize: function ( options ) {

			var b, s;

			if ( b = this.model.billingAddress() ) {
				this.views.add(
					'.it-exchange-customer-addressses-container',
					new app.Views.ManageAddressesAddress( {
						model : b,
						type  : 'billing',
						parent: this,
					} )
				);
			}

			if ( s = this.model.shippingAddress() ) {
				this.views.add(
					'.it-exchange-customer-addressses-container',
					new app.Views.ManageAddressesAddress( {
						model : s,
						type  : 'shipping',
						parent: this,
					} )
				);
			}

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();

			$.when( options.countries, options.states ).done( (function ( countries, states ) {
				this.countries = countries;
				this.states = states;
				this.$( '.it-exchange-customer-addresses-address-action--edit' ).show();
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
		template : wp.template( 'it-exchange-manage-addresses-address' ),
		events   : {
			'click .it-exchange-customer-addresses-address-action--edit'  : 'onEditClicked',
			'click .it-exchange-customer-addresses-address-action--save'  : 'onSaveClicked',
			'click .it-exchange-customer-addresses-address-action--cancel': 'closeEditor',
			'change .it-exchange-address-container--country select'       : 'onCountryChange',
		},

		countriesLoaded: false,
		statesLoaded   : false,
		type           : '',
		parent         : null,

		initialize: function ( options ) {
			this.type = options.type;
			this.parent = options.parent;

			this.listenTo( this.model, 'change', this.render );
		},

		onCountryChange: function ( e ) {
			var country = this.$( '.it-exchange-address-container--country select :selected' ).val();
			this.loadStatesSelect( country );
		},

		onEditClicked: function ( e ) {
			this.loadCountriesSelect();
			this.loadStatesSelect();
			this.openEditor();
		},

		onSaveClicked: function ( e ) {

			var save = {}, val, fields = ['first-name', 'last-name', 'address1', 'address2', 'state', 'country', 'zip', 'city'];

			for ( var i = 0; i < fields.length; i++ ) {
				var field = fields[i], $input;

				switch ( field ) {
					case 'country':
						$input = this.$( '.it-exchange-address-container--country select :selected' );
						break;

					case 'state':
						$input = this.$( '.it-exchange-address-container--state select :selected' );

						if ( !$input.length ) {
							$input = this.$( '.it-exchange-address-container--state input' );
						}

						break;
					default:
						$input = this.$( '.it-exchange-address-container--' + field + ' :input' );
						break;

				}

				val = $input.val();

				if ( this.model.get( field ) != val ) {
					save[field] = val;
				}
			}

			this.closeEditor();

			if ( !_.isEmpty( save ) ) {
				this.model.save( save );
			}
		},

		openEditor: function () {
			this.$( '.it-exchange-customer-addresses-edit-address-container' ).show();
			this.$( 'p' ).hide();

			this.$( '.it-exchange-customer-addresses-address-action--cancel' ).show();
			this.$( '.it-exchange-customer-addresses-address-action--save' ).show();

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).hide();
			this.$( '.it-exchange-customer-addresses-address-action--primary' ).hide();

			if ( !this.type ) {
				this.$( '.it-exchange-customer-addresses-address-action--delete' ).hide();
			}
		},

		closeEditor: function () {
			this.$( '.it-exchange-customer-addresses-edit-address-container' ).hide();
			this.$( 'p' ).show();

			this.$( '.it-exchange-customer-addresses-address-action--edit' ).show();
			this.$( '.it-exchange-customer-addresses-address-action--primary' ).show();

			if ( !this.type ) {
				this.$( '.it-exchange-customer-addresses-address-action--delete' ).show();
			}

			this.$( '.it-exchange-customer-addresses-address-action--cancel' ).hide();
			this.$( '.it-exchange-customer-addresses-address-action--save' ).hide();
		},

		render: function () {
			var attr = this.model.toJSON();
			attr.formattedAddress = this.model.formatted();
			attr.i18n = ProfileConfig.i18n;

			if ( this.type ) {
				attr.i18n.addressLabel = attr.i18n[this.type + 'Label'];
			}

			this.$el.html( this.template( attr ) );
			this.loadCountriesSelect();
			this.loadStatesSelect();

			if ( this.type ) {
				this.$( '.it-exchange-customer-addresses-address-action--delete' ).hide();
			}
		},

		/**
		 * Load the countries selector.
		 *
		 * @since 2.0.0
		 */
		loadCountriesSelect: function () {

			if ( !this.parent.countries || this.countriesLoaded ) {
				return;
			}

			this.countriesLoaded = true;
			this.$( '.it-exchange-address-container--country select' ).html( this.parent.countries );
			this.$( '.it-exchange-address-container--country select option[value="' + this.model.get( 'country' ) + '"]' ).prop( 'selected', true );
			this.$( '.it-exchange-address-container--country select' ).selectToAutocomplete();
		},

		/**
		 * Load the state input or select.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} [country]
		 */
		loadStatesSelect: function ( country ) {

			if ( _.isEmpty( this.parent.states ) ) {
				return;
			}

			country = country || this.model.get( 'country' );

			if ( this.statesLoaded == country ) {
				return;
			}

			var states = this.parent.states[country];

			var current =
				this.$( '.it-exchange-address-container--state select :selected' ).val() ||
				this.$( '.it-exchange-address-container--state input' ).val() ||
				this.model.get( 'state' );

			this.$( '.it-exchange-address-container--state :input' ).remove();

			if ( states ) {
				this.$( '.it-exchange-address-container--state' ).append( '<select>' + states + '</select>' );
				var $select = this.$( '.it-exchange-address-container--state select' );

				if ( current ) {
					var opt = $select.find( 'option[value="' + current + '"]' );

					if ( !opt.length ) {
						opt = $select.find( 'option:contains(' + current + ')' );
					}

					if ( opt.length ) {
						opt.prop( 'selected', true );
					}
				}

				$select.selectToAutocomplete();
			} else {
				var $input = $( '<input type="text">' );

				if ( current ) {
					$input.val( current );
				}

				this.$( '.it-exchange-address-container--state' ).append( $input );
			}

			this.statesLoaded = country;
		}
	} );

	app.Views.ManageTokens = api.View.extend( {

		manager: null,

		initialize: function () {
			this.manager = new api.Views.PaymentTokensManager( { collection: this.collection } );
			this.views.add( this.manager );
		},
	} );

})( window.ITExchangeAPI, window.ExchangeCommon, jQuery, window._, window.ITExchangeRESTConfig, window.ITExchangeProfileConfig );