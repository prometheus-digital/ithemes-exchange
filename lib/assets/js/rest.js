(function ( Schemas, ExchangeCommon, $, _, Backbone, wp, Config ) {
	"use strict";

	var i18n = Config.i18n;

	var app = {

		/**
		 * Overrides Backbone's Sync method to add context and nonce support.
		 *
		 * @param method
		 * @param model
		 * @param options
		 * @returns {*|Promise}
		 */
		sync: function ( method, model, options ) {

			if ( method === 'read' && model.context ) {
				var url = _.result( model, 'url' );

				if ( url && model.context != 'view' ) {
					url += '?context=' + model.context;
					options.url = url;
				}
			}

			var beforeSend = options.beforeSend;

			options.beforeSend = function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );

				// This isn't exactly clean, but it is the easiest.
				if ( app.Models.Cart && model instanceof app.Models.Cart && model.guestEmail.length ) {
					xhr.setRequestHeader( 'Authorization', 'ITHEMES-EXCHANGE-GUEST email="' + model.guestEmail + '"' );
				} else if ( app.Collections.CartItems && model instanceof app.Collections.CartItems && model.parent.guestEmail.length ) {
					xhr.setRequestHeader( 'Authorization', 'ITHEMES-EXCHANGE-GUEST email="' + model.parent.guestEmail + '"' );
				} else if ( app.Collections.PurchaseMethods && model instanceof app.Collections.PurchaseMethods && model.parent.guestEmail.length ) {
					xhr.setRequestHeader( 'Authorization', 'ITHEMES-EXCHANGE-GUEST email="' + model.parent.guestEmail + '"' );
				}

				if ( beforeSend ) {
					return beforeSend.apply( this, arguments );
				}
			};

			if ( model.filters && method === 'read' && !options._isGreedy ) {

				options.data = options.data || {};

				for ( var filter in model.filters ) {
					if ( model.filters.hasOwnProperty( filter ) ) {
						options.data[filter] = model.filters[filter].value;
					}
				}
			}

			if ( options.embed ) {
				options.data = options.data || {};
				options.data._embed = 1;
			}

			if ( model instanceof Backbone.PageableCollection ) {
				return Backbone.PageableCollection.prototype.sync( method, model, options );
			}

			if ( model instanceof Backbone.Model ) {
				return Backbone.Model.prototype.sync.call( model, method, model, options );
			}

			return Backbone.sync( method, model, options );
		},

		/**
		 * Tokenize an payment source in JS.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} gateway Gateway id. Ex. 'stripe'.
		 * @param {String} type Type of payment source to tokenize. 'card' or 'bank'.
		 * @param {*} tokenize Source data to tokenize.
		 * @param {*} [tokenize.address} Address to send to the gateway. Helps with verification purposes.
		 *
		 * @returns {*}
		 */
		tokenize: function ( gateway, type, tokenize ) {
			if ( !ITExchangeTokenizers || !ITExchangeTokenizers[gateway] ) {
				return false;
			}

			return ITExchangeTokenizers[gateway].fn( type, tokenize );
		},

		/**
		 * Can a payment source be tokenized in JS.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} gateway
		 *
		 * @returns {Boolean}
		 */
		canTokenize: function ( gateway ) {
			return ITExchangeTokenizers && ITExchangeTokenizers[gateway];
		},

		Models     : {},
		Collections: {},
		Views      : {},
		currentCart: null,
	};

	app.Model = Backbone.DeepModel.extend( {
		context     : 'view',
		defaultCache: true,

		initialize: function ( attributes, options ) {
			this.sync = app.sync;

			if ( options && options.context ) {
				this.context = options.context;
			}

			if ( this.collection && this.collection.parent instanceof app.Model ) {
				this.parentModel = this.collection.parent;
			}
		},

		/**
		 * Get an attribute.
		 *
		 * If a property is not available in the current context, will make a synchronous request to retrieve the value.
		 *
		 * @param attr {string} Attribute name. Supports dot notation for nested attributes.
		 *
		 * @returns {*}
		 */
		get: function ( attr ) {

			if ( attr !== this.idAttribute && !getNested( this.attributes, attr, true ) ) {
				if ( this.collection && this.collection.schema ) {

					var availableInContext, property, schema = this.getSchema();

					if ( schema.properties[attr] ) {
						availableInContext = schema.properties[attr].context;
					} else { // Nested Properties

						// Transform status.label to status.properties.label
						var exploded = attr.split( '.' );
						var imploded = '';

						for ( var i = 0; i < exploded.length; i++ ) {
							imploded += exploded[i];

							if ( i !== (exploded.length - 1 ) ) {
								imploded += '.properties.';
							}
						}

						if ( property = getNested( schema.properties, imploded ) ) {
							availableInContext = property.context;
						}
					}

					if ( !availableInContext ) {
						return;
					}

					if ( this.context && availableInContext.indexOf( this.context ) != -1 ) {
						return;
					}

					this.context = availableInContext[0];
					this.fetch( { async: false } );
				}
			}

			return Backbone.DeepModel.prototype.get.apply( this, arguments );
		},

		/**
		 * Override the fetch method to support embeds.
		 *
		 * @since 2.0.0
		 *
		 * @param {*} [options]
		 *
		 * @returns {*}
		 */
		fetch: function ( options ) {

			options = options || {};

			if ( this.defaultCache && typeof options.cache === 'undefined' ) {
				options.cache = true;
			}

			return Backbone.Model.prototype.fetch.call( this, options );
		},

		parse: function ( response, options ) {

			if ( !response || typeof response !== 'object' ) {
				return response;
			}

			var embeds = response._embedded;
			delete response._embedded;

			if ( embeds ) {
				this.handleEmbedded( embeds );
			}

			return response;
		},

		/**
		 * Parse the _embedded property of an object response to initialize the corresponding models and collections.
		 *
		 * @since 2.0.0
		 *
		 * @param {Object} embeds
		 */
		handleEmbedded: function ( embeds ) {
			_.each( embeds, (function ( data, slug ) {

				var proto = Object.getPrototypeOf( this );
				var key = '_' + ExchangeCommon.kebabToCamel( slug ), val = proto[key];

				if ( !val ) {
					return;
				}

				data = data[0];

				if ( _.isString( val ) ) {
					this[key] = new (ExchangeCommon.getFunctionByName( val, window ))( data, { parent: this } );

					return;
				}

				if ( !_.isFunction( val ) ) {
					return;
				}

				if ( val.prototype instanceof Backbone.Model ) {
					this[key] = new val( data, { parent: this } );
				} else if ( val.prototype instanceof Backbone.Collection ) {
					this[key] = new val( data, { parent: this } );
				} else {
					this[key] = val( data, this );
				}
			}).bind( this ) );
		},

		/**
		 * Update this model from a set of values obtained through an external API call.
		 *
		 * @since 2.0.0
		 *
		 * @param {Object} attributes
		 * @param {Object} [options]
		 */
		updateOutOfBand: function ( attributes, options ) {

			if ( attributes._embedded ) {
				this.handleEmbedded( attributes._embedded );
				delete attributes._embedded;
			}

			delete attributes._links;

			var current = this.changedAttributes(), toSet = {};

			if ( current === false ) {
				toSet = attributes;
			} else {
				for ( var attribute in attributes ) {
					if ( attributes.hasOwnProperty( attribute ) && !current.hasOwnProperty( attribute ) ) {
						toSet[attribute] = attributes[attribute];
					}
				}
			}

			var backupChanged = _.clone( this.changed );

			this.set( toSet, options || {} );

			// Don't keep track of the changes for these properties since they are already persisted on the server.
			this.changed = backupChanged;
		},

		toJSON: function () {
			var json = Backbone.DeepModel.prototype.toJSON.apply( this, arguments );
			delete json._links;

			return json;
		},

		save: function () {
			this.trigger( 'saving', this );

			return Backbone.Model.prototype.save.apply( this, arguments );
		},

		destroy: function () {
			this.trigger( 'destroying', this );

			return Backbone.Model.prototype.destroy.apply( this, arguments );
		},

		/**
		 * Fetch the model if has not yet been filled.
		 *
		 * @since 2.0.0
		 *
		 * @param {*} [options]
		 *
		 * @returns {*}
		 */
		fetchIfEmpty: function ( options ) {

			if ( _.keys( this.attributes ).length > 1 && !this.isNew() ) {
				return $.when();
			}

			return this.fetch( options );
		},

		/**
		 * Get the schema.
		 *
		 * @since 2.0.0
		 *
		 * @returns {object}
		 */
		getSchema: function () {
			if ( this.schema ) {
				return this.schema;
			}

			if ( this.collection && this.collection.schema ) {
				return this.collection.schema;
			}

			return null;
		},

		/**
		 * Get a link URL.
		 *
		 * Will return the first link if multiple exist.
		 *
		 * @since 2.0.0
		 *
		 * @param rel {string}
		 *
		 * @returns {string}
		 */
		getLinkUrl: function ( rel ) {

			var links = this.get( '_links' );

			if ( !links ) {
				return '';
			}

			if ( !links[rel] || !links[rel].length ) {
				return '';
			}

			return links[rel][0]['href'];
		},
	} );

	app.Collection = Backbone.Collection.extend( {

		context     : 'view',
		schema      : null,
		baseUrl     : ExchangeCommon.getRestUrl( '', {}, false ),
		filters     : {},
		schemaID    : '',
		defaultCache: true,

		initialize: function ( models, options ) {

			options = options || {};

			if ( options.schema ) {
				this.setSchema( options.schema );
			}

			if ( options.parent ) {
				this.parent = options.parent;
			}

			if ( options && options.context ) {
				this.context = options.context;
			}

			this.sync = app.sync;

			if ( this.schema === null ) {

				var url = _.result( this, 'url' );

				if ( url && url.length ) {
					getSchema( url, this.schemaID, false ).done( (function ( schema ) {
						this.setSchema( schema );
					}).bind( this ) );
				}
			}
		},

		url: function () {

			var base;

			if ( this.hasOwnProperty( 'parent' ) ) {

				if ( !this.parent ) {
					throw new Error( 'parent property must be set.' );
				}

				base = this.parent.url() + '/';
			} else {
				base = this.baseUrl;
			}

			return base + this.route;
		},

		/**
		 * Override the fetch method to support caching.
		 *
		 * @since 2.0.0
		 *
		 * @param {*} [options]
		 *
		 * @returns {*}
		 */
		fetch: function ( options ) {

			options = options || {};

			if ( this.defaultCache && typeof options.cache === 'undefined' ) {
				options.cache = true;
			}

			return Backbone.Collection.prototype.fetch.call( this, options );
		},

		/**
		 * Set the schema for this collection.
		 *
		 * Will auto-expand $ref attributes.
		 *
		 * @since 2.0.0
		 *
		 * @param schema {object}
		 */
		setSchema: function ( schema ) {

			for ( var property in schema.properties ) {

				if ( !schema.properties.hasOwnProperty( property ) ) {
					continue;
				}

				var prop_schema = schema.properties[property];

				if ( !prop_schema['$ref'] ) {
					continue;
				}

				// #/definitions/object_title
				var ref = prop_schema['$ref'],
					split = ref.split( '/' );

				if ( split.length !== 3 ) {
					continue;
				}

				var search = split[1],
					title = split[2];

				if ( !schema[search] || !schema[search][title] ) {
					continue;
				}

				schema.properties[property].properties = schema[search][title].properties;
			}

			this.schema = schema;
		},

		/**
		 * Add a filter.
		 *
		 * This does NOT auto update the collection. Use the filter method.
		 *
		 * @param name  {String}
		 * @param value {*}
		 *
		 * @returns {app.Collection}
		 */
		addFilter: function ( name, value ) {
			this.filters[name] = {
				value: value
			};

			return this;
		},

		/**
		 * Remove a filter.
		 *
		 * @param name {String}
		 *
		 * @returns {app.Collection}
		 */
		removeFilter: function ( name ) {
			delete this.filters[name];

			return this;
		},

		/**
		 * Filter this collection.
		 *
		 * @param [filters] {Object} Specify additional filters to apply.
		 * @param [options] {Object} Options passed to Backbone.fetch
		 * @param [options.reset] {Boolean} Whether to reset the collection. Defaults to true.
		 * @param [options.local] {Boolean} Whether to filter the local collection if it is non-empty. Defaults to false.
		 * @param [options.greedy] {Boolean} If this model caches by default
		 *
		 * @returns {*} Promise
		 */
		doFilter: function ( filters, options ) {

			filters = filters || {};
			options = options || {};

			for ( var filter in filters ) {
				if ( filters.hasOwnProperty( filter ) ) {
					this.addFilter( filter, filters[filter] );
				}
			}

			if ( typeof options.reset === 'undefined' ) {
				options.reset = true;
			}

			this.trigger( 'filter', this, options );

			if ( options.local && options.reset && this.length > 0 ) {
				this._doFilterLocal( options );

				return $.when( this.models );
			}

			var greedy = options.greedy && options.local && options.reset && ( options.cache || ( typeof options.cache === 'undefined' && this.defaultCache ) );

			options._isGreedy = greedy;

			var success = options.success;

			options.success = (function () {

				if ( greedy ) {
					this._doFilterLocal( options );
				} else {
					this.trigger( 'filtered', this, options );
				}

				if ( success ) {
					return success.apply( this, arguments );
				}
			}).bind( this );

			return this.fetch( options );
		},

		_doFilterLocal: function ( options ) {
			var first = this.at( 0 );
			var where = {};
			_.each( this.filters, function ( value, key ) {

				if ( _.isObject( first.get( key ) ) ) {
					if ( first.has( key + '.slug' ) ) {
						key += '.slug';
					} else if ( first.has( key + '.raw' ) ) {
						key += '.raw';
					}
				}

				if ( _.isString( key ) ) {
					where[key] = value.value;
				}
			} );

			this.reset( this.where( where ) );
			this.trigger( 'filtered', this, options );
		}
	} );

	app.PageableCollection = app.Collection.extend( {
		queryParams: {
			currentPage: 'page',
			pageSize   : 'per_page',
		},
	} );
	app.PageableCollection = app.PageableCollection.extend( Backbone.PageableCollection.prototype );
	app.PageableCollection.prototype.parseState = function ( resp, queryParams, state, options ) {
		return {
			totalRecords: parseInt( options.xhr.getResponseHeader( 'X-WP-Total' ) )
		};
	};
	app.PageableCollection.prototype.getPage = function ( index, options ) {

		options = options || { fetch: false };

		if ( options && options.filter ) {

			options.data = options.data || {};

			_.each( this.filters, function ( value, key ) {
				options.data[key] = value.value;
			} );
		}

		if ( options.embed ) {
			options.data = options.data || {};
			options.data._embed = 1;
		}

		return Backbone.PageableCollection.prototype.getPage.call( this, index, arguments );
	};

	app.Models.Transaction = app.Model.extend( {

		_refunds     : 'itExchange.api.Collections.Refunds',
		_customer    : 'itExchange.api.Models.Customer',
		_paymentToken: 'itExchange.api.Models.PaymentToken',

		initialize: function ( attributes, options ) {
			app.Model.prototype.initialize.apply( this, arguments );
		},

		/**
		 * Send the receipt to the customer's email address.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} [email]
		 *
		 * @returns {XMLHttpRequest}
		 */
		sendReceipt: function ( email ) {
			return $.ajax( {
				method    : 'POST',
				url       : _.result( this, 'url' ) + '/send_receipt',
				data      : {
					email: email,
				},
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );
				},
			} );
		},

		/**
		 * Get all activity of this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.TransactionActivity}
		 */
		activity: function () {
			if ( !this._activity ) {
				this._activity = new app.Collections.TransactionActivity( [], {
					parent : this,
					context: this.context,
				} );
			}

			return this._activity;
		},

		/**
		 * Get all the refunds available for this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @return {app.Collections.Refunds}
		 */
		refunds: function () {
			if ( this._refunds instanceof app.Collections.Refunds == false ) {
				this._refunds = new app.Collections.Refunds( [], {
					parent : this,
					context: this.context,
				} );
			}

			return this._refunds;
		},

		/**
		 * Get the customer who purchased this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Customer}
		 */
		customer: function () {

			if ( !this.get( 'customer' ) ) {
				return null;
			}

			if ( this._customer instanceof app.Models.Customer == false ) {
				this._customer = new app.Models.Customer( {
					id: this.get( 'customer' )
				} );
			}

			return this._customer;
		},

		/**
		 * Get the payment token used to pay for this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.models.PaymentToken}
		 */
		paymentToken: function () {

			if ( !this.get( 'payment_token' ) ) {
				return null;
			}

			if ( this._paymentToken instanceof app.Models.PaymentToken == false ) {
				this._paymentToken = new app.Models.PaymentToken( {
					id: this.get( 'payment_token' )
				} );
			}

			return this._paymentToken;
		},

		/**
		 * Get the parent transaction.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Transaction}
		 */
		parent: function () {
			if ( !this.get( 'parent' ) ) {
				return null;
			}

			return new app.Models.Transaction( { id: this.get( 'parent' ) } );
		},

		/**
		 * Get the children of this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @param [async] {boolean} Whether to retrieve the children asynchronously. Defaults to true.
		 *
		 * @returns {app.Collections.Transactions}|{Promise}
		 */
		children: function ( async ) {

			async = typeof async === 'undefined' ? true : async;

			if ( !this._children ) {
				this._children = new app.Collections.Transactions( [], {} );
				var xhr = this._children.fetch( { async: async, data: { parent: this.id } } );

				if ( async ) {
					var deferred = $.Deferred();

					xhr.success( ( function () {
						deferred.resolve( this._children );
					}).bind( this ) );

					xhr.fail( function ( xhr ) {
						alert( ExchangeCommon.getErrorFromXhr( xhr ) );
					} );

					return deferred.promise();
				}
			}

			return async ? $.when( this._children ) : this._children;
		},

		descriptionOrDefault: function () {
			return this.get( 'description' ) || '(none)';
		},

		orderDateFormatted: function () {
			return ExchangeCommon.formatDate( this.get( 'order_date' ) );
		},

		totalFormatted: function () {
			return ExchangeCommon.formatPrice( this.get( 'total' ) );
		},

		subtotalFormatted: function () {
			return ExchangeCommon.formatPrice( this.get( 'subtotal' ) );
		},

		totalBeforeRefundsFormatted: function () {
			return ExchangeCommon.formatPrice( this.get( 'total_before_refunds' ) );
		}
	} );

	app.Collections.Transactions = app.PageableCollection.extend( {
		model: app.Models.Transaction,
		route: 'transactions',
	} );

	app.Models.TransactionActivity = app.Model.extend( {
		parentModel: null,
	} );

	app.Collections.TransactionActivity = app.PageableCollection.extend( {
		model : app.Models.TransactionActivity,
		parent: null,
		route : 'activity',
	} );

	app.Models.Refund = app.Model.extend( {
		parentModel: null,

		/**
		 * Get the refund amount as a formatted string.
		 *
		 * @since 2.0.0
		 *
		 * @returns {String}
		 */
		amountFormatted: function () {
			return ExchangeCommon.formatPrice( this.get( 'amount' ) );
		}
	} );

	app.Collections.Refunds = app.Collection.extend( {
		model   : app.Models.Refund,
		parent  : null,
		route   : 'refunds',
		schemaID: 'refund',

		/**
		 * Get the total amount refunded.
		 *
		 * @since 2.0.0
		 *
		 * @returns {number}
		 */
		total: function () {
			var total = 0;

			this.each( function ( refund ) {
				total += refund.get( 'amount' );
			} );

			return total;
		}
	} );

	app.Models.Customer = app.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'customers', {}, false ),
		_tokens: 'itExchange.api.Collections.PaymentTokens',

		_billingAddress: function ( data, model ) {
			var address = new app.Models.Address( data, {
				collection: model.addresses()
			} );

			model.addresses().add( address );

			return address;
		},

		_shippingAddress: function ( data, model ) {
			var address = new app.Models.Address( data, {
				collection: model.addresses()
			} );

			model.addresses().add( address );

			return address;
		},

		/**
		 * Get a collection of the tokens this customer has.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.PaymentTokens}
		 */
		tokens: function () {
			if ( this._tokens instanceof app.Collections.PaymentTokens == false ) {
				this._tokens = new app.Collections.PaymentTokens( [], {
					parent : this,
					context: this.context,
				} );
			}

			return this._tokens;
		},

		/**
		 * Get the customer's transactions.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.Transactions}
		 */
		transactions: function () {

			if ( !this._transactions ) {
				this._transactions = new app.Collections.Transactions();
				this._transactions.addFilter( 'customer', this.id );
			}

			return this._transactions;
		},

		/**
		 * Get this customer's primary billing address.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Address}
		 */
		billingAddress: function () {

			var id = this.get( 'billing_address' );

			if ( !id ) {
				return null;
			}

			if ( this._billingAddress instanceof app.Models.Address == false || this._billingAddress.id != id ) {

				this._billingAddress = this.addresses().get( id );

				if ( !this._billingAddress ) {
					this._billingAddress = new app.Models.Address( { id: id }, {
						collection: this.addresses(),
						context   : this.context,
					} );
					this.addresses().add( this._billingAddress );
				}
			}

			return this._billingAddress;
		},

		/**
		 * Get this customer's primary shipping address.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Address}
		 */
		shippingAddress: function () {

			var id = this.get( 'shipping_address' );

			if ( !id ) {
				return null;
			}

			if ( this._shippingAddress instanceof app.Models.Address == false || this._shippingAddress.id != id ) {

				this._shippingAddress = this.addresses().get( id );

				if ( !this._shippingAddress ) {
					this._shippingAddress = new app.Models.Address( { id: id }, {
						collection: this.addresses(),
						context   : this.context,
					} );
					this.addresses().add( this._shippingAddress );
				}
			}

			return this._shippingAddress;
		},

		/**
		 * Get all of this customer's addresses.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.Addresses}
		 */
		addresses: function () {
			if ( !this._addresses ) {
				this._addresses = new app.Collections.Addresses( [], {
					parent : this,
					context: this.context,
				} );
			}

			return this._addresses;
		},
	} );

	app.Models.Address = app.Model.extend( {
		parentModel: null,

		/**
		 * Return a formatted version of this address.
		 *
		 * @since 2.0.0
		 *
		 * @returns {String}
		 */
		formatted: function () {
			return ExchangeCommon.formatAddress( this.toJSON() );
		}
	} );

	app.Collections.Addresses = app.Collection.extend( {
		model   : app.Models.Address,
		parent  : null,
		route   : 'addresses',
		schemaID: 'address',
	} );

	app.Models.PaymentToken = app.Model.extend( {
		parentModel: null,

		/**
		 * Is this payment token's expiration date editable.
		 *
		 * @since 2.0.0
		 *
		 * @returns {Boolean}
		 */
		isExpirationEditable: function () {

			if ( !this.get( 'expiration' ) ) {
				return false;
			}

			if ( !this.get( 'expiration.editable' ) ) {
				return false;
			}

			return true;
		}
	} );

	app.Collections.PaymentTokens = app.Collection.extend( {
		model   : app.Models.PaymentToken,
		parent  : null,
		route   : 'tokens',
		schemaID: 'payment-token',

		/**
		 * Get the primary payment token for a given gateway.
		 *
		 * @param gateway string The gateway slug.
		 *
		 * @returns {*}
		 */
		getPrimary: function ( gateway ) {
			return this.findWhere( { primary: true, gateway: gateway } );
		}
	} );

	app.Models.Product = app.Model.extend( {
		parentModel: null,

		'_https://api.w.org/featuredmedia': 'itExchange.api.Models.wp.Image',

		/**
		 * Get a collection of the tokens this customer has.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.wp.Image}
		 */
		featuredMedia: function () {
			if ( this['_https://api.w.org/featuredmedia'] instanceof app.Models.wp.Image == false ) {
				this['_https://api.w.org/featuredmedia'] = new app.Models.wp.Image(
					{ id: this.get( 'featured_media' ) }, { context: this.context, }
				);
			}

			return this['_https://api.w.org/featuredmedia'];
		},
	} );

	app.Collections.Products = app.PageableCollection.extend( {
		model   : app.Models.Product,
		parent  : null,
		route   : 'products',
		schemaID: 'product',
	} );

	app.Models.wp = {};

	/**
	 * @typedef {Object} wp.Image.Size
	 * @property {String} wp.image.Size.source_url
	 * @property {Number} wp.image.Size.width
	 * @property {Number} wp.image.Size.height
	 */
	app.Models.wp.Image = app.Model.extend( {
		parentModel: null,
		urlRoot    : ExchangeCommon.config.wpRestUrl + '/wp/v2/media/',

		/**
		 * Retrieve image details for a given size.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} size
		 *
		 * @returns {wp.Image.Size}
		 */
		details: function ( size ) {

			var sizes = this.get( 'media_details.sizes' );

			if ( !sizes ) {
				return (void 0);
			}

			if ( !sizes[size] ) {
				return (void 0);
			}

			return sizes[size];
		}
	} );

	app.loadCart = function ( create, email ) {

		create = create || false;
		email = email || '';

		var deferred = $.Deferred();

		/**
		 * @property products {app.Collections.CartItems}
		 * @property coupons {app.Collections.CartItems}
		 * @property fees {app.Collections.CartItems}
		 */
		app.Models.Cart = app.Model.extend( {
			urlRoot         : ExchangeCommon.getRestUrl( 'carts', {}, false ),
			sortedItems     : {},
			_allItems       : (void 0),
			_purchaseMethods: 'itExchange.api.Collections.PurchaseMethods',
			_customer       : 'itExchange.api.Models.Customer',
			guestEmail      : '',
			defaultCache    : false,

			initialize: function ( data, opts ) {
				this.sortedItems = _.chain( this.get( 'items' ) ).groupBy( 'type' ).value();
				this.on( 'change:total_lines', this.updateTotalLines, this );
				this.on( 'change:items', this.updateItems, this );

				this.allItems().on( 'updated', this.estimateTotalOnChange, this );

				app.Model.prototype.initialize.apply( this, arguments );
			},

			updateTotalLines: function () {
				var totals = this.get( 'total_lines' ), collection = this.lineItemTotals(), total, totalModel;

				for ( var i = 0; i < totals.length; i++ ) {
					total = totals[i];

					totalModel = collection.get( total.slug );

					if ( totalModel ) {
						totalModel.set( total );
					} else {
						totalModel = new app.Models.LineItemTotal( total, {
							parent: this
						} );
						collection.add( totalModel );
					}
				}
			},

			updateItems: function () {

				this.sortedItems = _.chain( this.get( 'items' ) ).groupBy( 'type' ).value();

				var items = this.get( 'items' ), allItems = this._allItems, item, itemModel, collection;

				for ( var i = 0; i < items.length; i++ ) {
					item = items[i];

					if ( item.summary_only ) {
						continue;
					}

					collection = this['_' + item.type + 's'];

					if ( !collection ) {
						continue;
					}

					itemModel = collection.get( item.id );

					if ( itemModel ) {
						// update the stored model properties
						itemModel.set( item );
					} else {
						itemModel = collection.add( item );
					}

					if ( allItems ) {
						allItems.add( itemModel );
					}
				}
			},

			estimateTotalOnChange: function () {

				var total = this.estimateTotals();

				this.set( 'subtotal', total.subtotal );
				this.set( 'total', total.total );
			},

			/**
			 * Estimate the totals based on the line items.
			 *
			 * @since 2.0.0
			 *
			 * @returns {{subtotal: number, total: number}}
			 */
			estimateTotals: function () {

				var subtotal = 0, total = 0;

				this.allItems().forEach( function ( item ) {
					subtotal += item.get( 'total' );
				} );

				total = subtotal;

				this.lineItemTotals().forEach( function ( lineTotal ) {
					total += lineTotal.get( 'total' );
				} );

				return {
					subtotal: subtotal,
					total   : total
				}
			},

			/**
			 * Get the available purchase methods for this cart.
			 *
			 * @since 2.0.0
			 *
			 * @returns {app.Collections.PurchaseMethods}
			 */
			purchaseMethods: function () {

				if ( this._purchaseMethods instanceof app.Collections.PurchaseMethods == false ) {
					this._purchaseMethods = new app.Collections.PurchaseMethods( [], {
						parent: this,
					} );
				}

				return this._purchaseMethods;
			},

			/**
			 * Purchase the cart.
			 *
			 * @sine 2.0.0
			 *
			 * @param {String} gateway Select gateway method.
			 * @param {*} data Additional data required to make the transaction.
			 * @param {String} data.nonce Nonce
			 * @param {Integer} [data.token] If the purchase method uses tokens, pass the Payment Token id here.
			 * @param {String|*} [data.tokenize] The data that should automatically be tokenized.
			 * @param {Object} [data.card] If paying via a credit card that cannot be tokenized.
			 *
			 * @returns {*} Promise that resolves to a Transaction model.
			 */
			purchase: function ( gateway, data ) {

				var deferred = $.Deferred();

				data.id = gateway;

				$.ajax( {
					method: 'POST',
					url   : _.result( this, 'url' ) + '/purchase',
					data  : data,

					beforeSend: (function ( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );

						if ( this.guestEmail.length ) {
							xhr.setRequestHeader( 'Authorization', 'ITHEMES-EXCHANGE-GUEST email="' + this.guestEmail + '"' );
						}
					}).bind( this ),

					success: function ( data ) {
						deferred.resolve( new app.Models.Transaction( data ) );
					},

					error: function ( xhr ) {
						alert( ExchangeCommon.getErrorFromXhr( xhr ) );
					},
				} );

				return deferred.promise();
			},

			/**
			 * Get all items in this cart.
			 *
			 * @since 2.0.0
			 *
			 * @returns {app.Collections.CartItems}
			 */
			allItems: function () {

				if ( typeof this._allItems !== 'undefined' ) {
					return this._allItems;
				}

				var collection = new app.Collections.CartItems(), items = this.get( 'items' ), item, model,
					modelCollection;

				for ( var i = 0; i < items.length; i++ ) {
					item = items[i];

					if ( item.summary_only ) {
						continue;
					}

					modelCollection = _.result( this, item.type + 's' );
					model = modelCollection.get( item.id );

					collection.add( model );
				}

				this._allItems = collection;

				return collection;
			},

			/**
			 * Get the customer purchasing this cart.
			 *
			 * @since 2.0.0
			 *
			 * @returns {app.Models.Customer}
			 */
			customer: function () {

				if ( !this.get( 'customer' ) ) {
					return null;
				}

				if ( this._customer instanceof app.Models.Customer == false ) {
					this._customer = new app.Models.Customer( {
						id: this.get( 'customer' )
					} );
				}

				return this._customer;
			},

			/**
			 * Get the line item totals.
			 *
			 * @since 2.0.0
			 *
			 * @returns {app.Collections.LineItemTotals}
			 */
			lineItemTotals: function () {
				if ( !this._lineItemTotals ) {
					this._lineItemTotals = new app.Collections.LineItemTotals( this.get( 'total_lines' ), {
						parent: this,
					} );
				}

				return this._lineItemTotals;
			},

			/**
			 * Get the total for a given line.
			 *
			 * @since 2.0.0
			 *
			 * @param {String} line
			 *
			 * @returns {Number}
			 */
			lineTotal: function ( line ) {
				var totalLines = this.get( 'total_lines' );

				for ( var i = 0; i < totalLines.length; i++ ) {
					if ( totalLines[i].slug === line ) {
						return totalLines[i].total;
					}
				}

				return null;
			},

			totalFormatted: function () {
				return ExchangeCommon.formatPrice( this.get( 'total' ) );
			},

			subtotalFormatted: function () {
				return ExchangeCommon.formatPrice( this.get( 'subtotal' ) );
			}
		} );

		app.Models.LineItemTotal = app.Model.extend( {
			idAttribute: 'slug',

			totalFormatted: function () {
				return ExchangeCommon.formatPrice( this.get( 'total' ) );
			}
		} );

		app.Collections.LineItemTotals = app.Collection.extend( {
			model: app.Models.LineItemTotal,
			url  : '',

			sync: function () {
				// No-op
				console.log( 'no-op sync' );
			}
		} );

		app.Models.PurchaseMethod = app.Model.extend( {

			defaultCache: false,

			/**
			 * Check if this purchase method accepts a value for purchase.
			 *
			 * @since 2.0.0
			 *
			 * @param {String} value 'card', 'token', 'tokenize'.
			 *
			 * @returns {boolean}
			 */
			accepts: function ( value ) {

				var accepts = this.get( 'method.accepts' );

				if ( !accepts || !accepts.length ) {
					return false;
				}

				return _.indexOf( accepts, value ) !== -1;
			},

			/**
			 * Get the type of this purchase method.
			 *
			 * Either 'redirect', 'REST', 'dialog', 'iframe'.
			 *
			 * @since 2.0.0
			 *
			 * @returns {String}
			 */
			getType: function () {
				return this.get( 'method.method' );
			},
		} );

		app.Collections.PurchaseMethods = app.Collection.extend( {
			model       : app.Models.PurchaseMethod,
			parent      : null, // Parent cart.
			route       : 'purchase',
			schemaID    : 'cart-purchase',
			defaultCache: false,
		} );

		app.Models.CartItem = app.Model.extend( {
			defaultCache: false,

			save: function ( key, val, options ) {

				var attrs;
				if ( key == null || typeof key === 'object' ) {
					attrs = key;
					options = val;
				} else {
					(attrs = {})[key] = val;
				}

				var isNew = this.isNew();
				var qty = this.get( 'quantity.selected' );

				var success = options.success;

				options.success = function ( model ) {

					if ( isNew && model.parentModel && !model.get( 'summary_only' ) ) {
						model.parentModel.allItems().add( model );
					}

					var newQty = model.get( 'quantity.selected' );

					if ( qty != newQty ) {
						model.set( 'total', model.get( 'amount' ) * newQty );
					}

					if ( model.parentModel && !model.get( 'summary_only' ) ) {
						model.parentModel.estimateTotalOnChange();
					}

					if ( model.parentModel ) {
						model.parentModel.fetch();
					}

					if ( success ) {
						return success.apply( options.context, arguments );
					}
				};

				return Backbone.Model.prototype.save.call( this, attrs, options );
			},

			destroy: function ( options ) {

				var r = Backbone.Model.prototype.destroy.call( this, options );

				if ( !r ) {
					return r;
				}

				r.done( (function () {

					if ( this.parentModel ) {
						this.parentModel.fetch();
					}
				}).bind( this ) );

				return r;
			},
		} );

		app.Collections.CartItems = app.Collection.extend( {
			model       : app.Models.CartItem,
			parent      : null, // Parent cart.
			route       : 'items',
			itemType    : null,
			editable    : false,
			typeLabel   : '',
			defaultCache: false,

			url: function () {

				if ( !this.itemType ) {
					return '';
				}

				return app.Collection.prototype.url.apply( this );
			}
		} );

		/**
		 * Create a cart.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} [guestEmail]
		 * @param {*} [opts]
		 * @param {Boolean} [opts.is_main] Should this be the main cart for the customer. Defaults to true.
		 * @param {Number} [opts.customer] Create the cart for a different customer than the current user.
		 * @param {Boolean} [opts.embed]
		 *
		 * @returns {*} Promise that resolves to a cart.
		 */
		app.createCart = function ( guestEmail, opts ) {

			guestEmail = guestEmail || '';
			opts = opts || {};

			var data = {};

			if ( opts.hasOwnProperty( 'is_main' ) ) {
				data.is_main = opts.is_main;
			} else {
				data.is_main = true;
			}

			if ( opts.hasOwnProperty( 'customer' ) ) {
				data.customer = opts.customer;
			}

			var qa = {};

			if ( opts.embed ) {
				qa._embed = 1;
			}

			var deferred = $.Deferred();

			if ( !guestEmail.length ) {

				$.ajax( {
					method: 'POST',
					url   : ExchangeCommon.getRestUrl( 'carts', qa, false ),
					data  : data,

					beforeSend: function ( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );
					},

					success: function ( cart ) {
						deferred.resolve( new app.Models.Cart( cart, { parse: true, } ) );
					},

					error: function () {
						deferred.reject( arguments );
					}
				} );
			} else {

				$.ajax( {
					method: 'POST',
					url   : ExchangeCommon.getRestUrl( 'carts', qa, false ),
					data  : data,

					beforeSend: function ( xhr ) {
						xhr.setRequestHeader( 'Authorization', 'ITHEMES-EXCHANGE-GUEST email="' + email + '"' );
					},

					success: function ( cart ) {
						var model = new app.Models.Cart( cart, { parse: true } );
						model.guestEmail = email;
						deferred.resolve( model );
					},

					error: function () {
						deferred.reject( arguments );
					}
				} );
			}

			return deferred.promise();
		};

		if ( Config.cartItemTypes ) {
			parseCartItemTypes( Config.cartItemTypes );
		} else {
			$.get( ExchangeCommon.getRestUrl( 'cart_item_types' ), parseCartItemTypes );
		}

		function parseCartItemTypes( types ) {

			for ( var i = 0; i < types.length; i++ ) {

				var type = types[i];
				var capitalized = type.id.charAt( 0 ).toUpperCase() + type.id.slice( 1 );
				var plural = type.id + 's';

				var model = app.Models[capitalized + 'CartItem'] = app.Models.CartItem.extend( {
					sync    : app.sync,
					defaults: {
						type: type.id
					}
				} );

				app.Collections[capitalized + 'CartItems'] = app.Collections.CartItems.extend( {
					model    : model,
					route    : 'items/' + type.id,
					itemType : type.id,
					editable : type.editable,
					typeLabel: type.label,
					schemaID : 'cart-item-' + type.id,
				} );

				app.Models.Cart.prototype[plural] = (function ( capitalized, id, plural ) {
					return function () {

						var prop = '_' + plural;

						if ( !this[prop] ) {
							this[prop] = new app.Collections[capitalized + 'CartItems']( this.sortedItems[id] || [], {
								parent: this,
							} );
						}

						return this[prop];
					};
				})( capitalized, type.id, plural );
			}

			if ( create ) {
				var promise = app.createCart( email );

				promise.done( function ( cart ) {
					app.currentCart = cart;

					deferred.resolve();
				} );

				promise.fail( function ( err ) {
					deferred.reject( err );
				} );

			} else {
				deferred.resolve();
			}
		}

		return deferred.promise();
	};

	/* ---------- Views ---------- */

	app.View = wp.Backbone.View.extend( {

		animateRemoval: false,

		inject: function ( selector ) {
			this.render();

			var $selector;

			if ( selector instanceof $ ) {
				$selector = selector;
			} else {
				$selector = $( selector );
			}

			$selector.html( this.el );

			this.views.ready();
		},

		constructor: function () {
			this.className = this.className || '';
			this.className += ' it-exchange-view';

			wp.Backbone.View.prototype.constructor.apply( this, arguments );
		},

		initialize: function ( options ) {

			if ( options && typeof options.animateRemoval !== 'undefined' ) {
				this.animateRemoval = options.animateRemoval;
			}

			if ( this.model ) {
				this.listenTo( this.model, 'saving', function () {
					this.$el.addClass( 'saving' );
				} );

				this.listenTo( this.model, 'destroying', function () {
					this.$el.addClass( 'deleting' );
				} );

				this.listenTo( this.model, 'sync', function () {

					this.$el.on( 'animationiteration animationend webkitAnimationIteration webkitAnimationEnd', ( function () {
						this.$el.removeClass( 'saving' );
					} ).bind( this ) );
				} );
			}
		},

		prepare: function () {
			if ( !_.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		},

		/**
		 * Remove a subview by model.
		 *
		 * @since 2.0.0
		 *
		 * @param {String|app.Model} selector
		 * @param {app.Model} [model]
		 */
		removeViewByModel: function ( selector, model ) {

			if ( selector instanceof app.Model ) {
				model = selector;
				selector = '';
			}

			var views = this.views.get( selector ), view;

			for ( var i = 0; i < views.length; i++ ) {
				view = views[i];

				if ( view.model === model ) {
					this.views.unset( selector, view );

					return;
				}
			}
		},

		_removeElement: function () {
			if ( this.animateRemoval ) {
				this.$el.fadeOut( 500, (function () {
					this.$el.remove();
				}).bind( this ) );
			} else {
				this.$el.remove();
			}
			this.$el.removeClass( 'deleting' );
		},
	} );

	app.Views.Checkout = app.View.extend( {

		template: wp.template( 'it-exchange-checkout' ),

		showLineItems               : true,
		showLineItemImages          : true,
		showLineItemTotals          : true,
		includeTotalInLineItemTotals: true,
		includePurchaseMethods      : true,
		defaultPurchaseMethod       : '',
		defaultPurchaseToken        : 0,
		redirectTo                  : '',
		showCouponEntry             : false,

		events: {
			'click button.it-exchange-checkout-purchase-method-button': 'onMethodSelected',
		},

		initialize: function ( options ) {

			options = options || {};

			if ( typeof options.showLineItems === 'undefined' ) {
				this.showLineItems = true;
			} else {
				this.showLineItems = options.showLineItems;
			}

			if ( typeof options.showLineItemImages === 'undefined' ) {
				this.showLineItemImages = true;
			} else {
				this.showLineItemImages = options.showLineItemImages;
			}

			if ( typeof options.showLineItemTotals === 'undefined' ) {
				this.showLineItemTotals = true;
			} else {
				this.showLineItemTotals = options.showLineItemTotals;
			}

			if ( typeof options.includeTotalInLineItemTotals === 'undefined' ) {
				this.includeTotalInLineItemTotals = true;
			} else {
				this.includeTotalInLineItemTotals = options.includeTotalInLineItemTotals;
			}

			if ( typeof options.includePurchaseMethods === 'undefined' ) {
				this.includePurchaseMethods = true;
			} else {
				this.includePurchaseMethods = options.includePurchaseMethods;
			}

			if ( typeof options.defaultPurchaseMethod === 'undefined' ) {
				this.defaultPurchaseMethod = '';
			} else {
				this.defaultPurchaseMethod = options.defaultPurchaseMethod;
			}

			if ( typeof options.defaultPurchaseToken === 'undefined' ) {
				this.defaultPurchaseToken = 0;
			} else {
				this.defaultPurchaseToken = options.defaultPurchaseToken;
			}

			if ( typeof options.showCouponEntry === 'undefined' ) {
				this.showCouponEntry = false;
			} else {
				this.showCouponEntry = options.showCouponEntry;
			}

			if ( options.redirectTo ) {
				this.redirectTo = options.redirectTo;
			}

			if ( !this.model ) {
				return;
			}

			this.model.customer().fetchIfEmpty( { embed: true } );

			if ( this.showLineItems ) {
				this.views.add(
					'.it-exchange-checkout-line-items-container',
					new app.Views.CheckoutLineItems( {
						collection: this.model.allItems(),
						showImages: this.showLineItemImages,
					} )
				);
			}

			if ( this.showLineItemTotals ) {
				this.views.add(
					'.it-exchange-checkout-line-item-totals-container',
					new app.Views.CheckoutLineItemTotals( {
						collection  : this.model.lineItemTotals(),
						includeTotal: this.includeTotalInLineItemTotals,
						cart        : this.model,
					} )
				);
			}

			if ( this.showCouponEntry ) {
				this.views.add(
					'.it-exchange-checkout-coupon-container',
					new app.Views.CheckoutCoupon( { model: this.model } )
				);
			}

			if ( this.includePurchaseMethods ) {

				var methods = this.model.purchaseMethods();

				if ( methods.length ) {

					if ( this.defaultPurchaseMethod.length ) {
						this.addDefaultPurchaseMethodView( methods.get( this.defaultPurchaseMethod ) );
					}

					_.each( methods.models, this.addPurchaseMethodView, this );
				} else {
					var methodFetchOpts = {};

					if ( this.redirectTo ) {
						methodFetchOpts.data['redirect_to'] = this.redirectTo;
					}

					methods.fetch( methodFetchOpts ).done( (function () {
						if ( this.defaultPurchaseMethod.length ) {
							this.addDefaultPurchaseMethodView( methods.get( this.defaultPurchaseMethod ) );
						}

						_.each( methods.models, this.addPurchaseMethodView, this );
					}).bind( this ) );
				}
			}

			app.View.prototype.initialize.apply( this, arguments );
		},

		onMethodSelected: function ( e ) {
			this.$( '.it-exchange-checkout-purchase-methods' ).hide();
		},

		render: function () {
			this.$el.html( this.template( {
				showLineItems     : this.showLineItems,
				showLineItemTotals: this.showLineItemTotals,
				showCouponEntry   : this.showCouponEntry,
			} ) );
			this.views.render();

			if ( this.defaultPurchaseMethod.length ) {
				this.$( '.it-exchange-checkout-purchase-methods' ).hide();
			}
		},

		addDefaultPurchaseMethodView: function ( purchaseMethodModel ) {

			if ( !purchaseMethodModel ) {
				this.$( '.it-exchange-checkout-purchase-methods' ).show();

				return;
			}

			var methodView = new app.Views.PurchaseMethod( {
				model     : purchaseMethodModel,
				cart      : this.model,
				addView   : (this._addAdditionalDetailsView).bind( this ),
				removeView: (this._removeAdditionalDetailsView).bind( this ),
			} );

			var defaultView = new app.Views.DefaultPurchaseMethod( {
				model             : purchaseMethodModel,
				purchaseMethodView: methodView,
				defaultToken      : this.defaultPurchaseToken,
				cart              : this.model,
			} );

			this.views.add( '.it-exchange-checkout-default-method-container', defaultView );

			this.listenTo( defaultView, 'exchange.selectOtherMethod', function () {
				this.$( '.it-exchange-checkout-purchase-methods' ).show();
			} );

			this.listenTo( methodView, 'exchange.purchaseFinished', this.renderReceipt );
		},

		addPurchaseMethodView: function ( purchaseMethodModel ) {
			var view = new app.Views.PurchaseMethod( {
				model     : purchaseMethodModel,
				cart      : this.model,
				addView   : (this._addAdditionalDetailsView).bind( this ),
				removeView: (this._removeAdditionalDetailsView).bind( this ),
			} );
			this.views.add( '.it-exchange-checkout-purchase-methods', view );
			this.listenTo( view, 'exchange.cancelPurchaseMethod', function () {
				this.$( '.it-exchange-checkout-purchase-methods' ).show();
			} );
			this.listenTo( view, 'exchange.purchaseFinished', this.renderReceipt );
		},

		renderReceipt: function ( transaction ) {
			var summaryView = new app.Views.PaymentSummary( {
				model: transaction
			} );
			summaryView.render();

			this.$el.html( '' );
			this.views.add( summaryView );
		},

		_addAdditionalDetailsView: function ( view ) {
			this.views.add( '.it-exchange-checkout-additional-info-container', view );
		},

		_removeAdditionalDetailsView: function ( view ) {
			this.views.unset( '.it-exchange-checkout-additional-info-container', view );
		},
	} );

	app.Views.CheckoutLineItems = app.View.extend( {

		tagName  : 'ul',
		className: 'it-exchange-checkout-line-items',

		showImages        : true,
		_lineItemTemplates: {},

		initialize: function ( options ) {

			if ( typeof options.showImages === 'undefined' ) {
				this.showImages = true;
			} else {
				this.showImages = options.showImages;
			}

			_.each( this.collection.models, this.addLineItemView, this );

			this.collection.on( 'add', this.addLineItemView, this );
			this.collection.on( 'remove', this.removeViewByModel, this );

			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.views.render();
		},

		addLineItemView: function ( lineItemModel ) {

			var view = new app.Views.CheckoutLineItem( {
				model    : lineItemModel,
				showImage: this.showImages,
			} );
			view.template = this.findTemplateForLineItem( lineItemModel );

			this.views.add( view );
		},

		/**
		 * Find a template for a line item.
		 *
		 * @since 2.0.0
		 *
		 * @param {app.Models.CartItem} lineItemModel
		 */
		findTemplateForLineItem: function ( lineItemModel ) {

			var type = lineItemModel.get( 'type' );

			if ( this._lineItemTemplates[type] ) {
				return this._lineItemTemplates[type];
			}

			var template = "it-exchange-checkout-line-item--" + lineItemModel.get( 'type' );
			var specific = $( "#tmpl-" + template );

			if ( specific.length ) {
				this._lineItemTemplates[type] = wp.template( template );
			} else {
				this._lineItemTemplates[type] = wp.template( 'it-exchange-checkout-line-item' );
			}

			return this._lineItemTemplates[type];
		}
	} );

	app.Views.CheckoutLineItem = app.View.extend( {

		template: wp.template( 'it-exchange-checkout-line-item' ),
		tagName : 'li',

		showImage: true,

		initialize: function ( options ) {

			if ( typeof options.showImage === 'undefined' ) {
				this.showImage = true;
			} else {
				this.showImage = options.showImage;
			}

			app.View.prototype.initialize.apply( this, arguments );

			this.model.on( 'change', this.render, this );
		},

		render: function () {

			var json = this.model.toJSON();
			json.totalFormatted = ExchangeCommon.formatPrice( json.total );
			json.showImage = this.showImage;

			this.$el.html( this.template( json ) );
		}
	} );

	app.Views.CheckoutLineItemTotals = app.View.extend( {

		tagName     : 'ul',
		className   : 'it-exchange-checkout-line-item-totals',
		includeTotal: true,
		cart        : null,
		totalView   : null,

		initialize: function ( options ) {
			this.cart = options.cart;
			this.includeTotal = options.includeTotal;
			_.each( this.collection.models, this.addLineItemTotalsView, this );

			if ( this.includeTotal ) {
				this.totalView = new app.Views.CheckoutLineItemTotalTotal( { model: this.cart } );
				this.views.add( this.totalView );
			}

			this.collection.on( 'add', this.addLineItemTotalsView, this );
			this.collection.on( 'remove', this.removeViewByModel, this );

			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.views.render();
		},

		addLineItemTotalsView: function ( lineItemTotalModel ) {

			var options = {};

			if ( this.includeTotal ) {
				options.at = this.collection.length - 2;
			}

			this.views.add( new app.Views.CheckoutLineItemTotal( { model: lineItemTotalModel } ), options );
		},
	} );

	app.Views.CheckoutLineItemTotal = app.View.extend( {

		template: wp.template( 'it-exchange-checkout-line-items-total' ),
		tagName : 'li',

		initialize: function () {
			this.model.on( 'change:total', this.render, this );
			this.model.on( 'change:description', this.render, this );

			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {

			var attributes = this.model.toJSON();
			attributes.totalFormatted = ExchangeCommon.formatPrice( attributes.total );

			this.$el.html( this.template( attributes ) );
		},
	} );

	app.Views.CheckoutLineItemTotalTotal = app.View.extend( {

		template : wp.template( 'it-exchange-checkout-line-items-total' ),
		tagName  : 'li',
		className: 'it-exchange-checkout-line-items-total-total-line',

		initialize: function () {
			this.model.on( 'change:total', this.render, this );
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {

			var attributes = {
				slug          : 'total',
				label         : i18n.checkout.total,
				total         : this.model.get( 'total' ),
				description   : '',
				totalFormatted: this.model.totalFormatted(),
			};

			this.$el.html( this.template( attributes ) );
		},
	} );

	app.Views.CheckoutCoupon = app.View.extend( {

		template: wp.template( 'it-exchange-checkout-coupon' ),

		events: {
			'click .it-exchange-checkout-have-coupon-prompt': 'onHaveCouponClick',
			'click .it-exchange-checkout-coupon-add'        : 'onAddClick',
			'click .it-exchange-checkout-coupon-cancel'     : 'onCancelClick',
		},

		/**
		 * Display the coupon form when the "Have a coupon" link is clicked.
		 *
		 * @param {Event} e
		 */
		onHaveCouponClick: function ( e ) {
			e.preventDefault();

			this.$( '.it-exchange-checkout-have-coupon-prompt' ).hide();
			this.$( '.it-exchange-checkout-coupon-input-container' ).show();
		},

		/**
		 * Add the coupon to the cart when the "Add" button is clicked.
		 *
		 * @param {Event} e
		 */
		onAddClick: function ( e ) {

			var $button = this.$( e.currentTarget ).attr( 'disabled', true );
			var $code = this.$( 'input[type="text"]' ).attr( 'disabled', true );

			var code = $code.val();

			if ( code.length ) {
				this.model.coupons().create( { coupon: code }, {
					wait   : true,
					success: (function () {
						$button.removeAttr( 'disabled' );
						$code.removeAttr( 'disabled' );
						this.$el.hide();
					}).bind( this ),

					error: function () {
						$button.removeAttr( 'disabled' );
						$code.removeAttr( 'disabled' );
					},
				} );
			}
		},

		onCancelClick: function () {
			this.$( '.it-exchange-checkout-have-coupon-prompt' ).show();
			this.$( '.it-exchange-checkout-coupon-input-container' ).hide();
		},

		render: function () {
			var attr = { i18n: Config.i18n.checkout };

			this.$el.html( this.template( attr ) );
		},
	} );

	app.Views.PurchaseMethod = app.View.extend( {
		template: wp.template( 'it-exchange-checkout-purchase-method-button' ),
		tagName : 'li',
		events  : {
			'click button': 'onMethodSelected'
		},

		cart         : null,
		visualCC     : null,
		tokenSelector: null,

		initialize: function ( options ) {
			this.cart = options.cart;
			this.addView = options.addView;
			this.removeView = options.removeView;
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.$el.html( this.template( this.model.toJSON() ) );
		},

		onMethodSelected: function ( e ) {

			if ( this.model.getType() === 'redirect' ) {
				window.location = this.model.get( 'method.url' );

				return;
			}

			var sourceView;

			if ( this.model.accepts( 'token' ) ) {

				var tokens = this.cart.customer().tokens();
				tokens.doFilter( { gateway: this.model.id }, { local: true, greedy: true } );

				this.tokenSelector = sourceView = new app.Views.PaymentTokenSelector( {
					model     : this.model,
					collection: tokens
				} );
				this.addView( this.tokenSelector );
				this.listenTo( this.tokenSelector, 'exchange.doPurchase', this.purchase );

			} else if ( this.model.getType() === 'iframe' ) {
				var iframe = new app.Views.iFramePurchase( {
					model: this.model,
					cart : this.cart,
				} );
				this.listenTo( iframe, 'exchange.iFramePurchaseDone', function ( data ) {
					this.purchase( e, data );
				} );
				this.listenTo( iframe, 'exchange.iFramePurchaseCancelled', function () {
					this.trigger( 'exchange.cancelPurchaseMethod' );
				} );
				this.listenTo( iframe, 'exchange.iFramePurchaseFailed', function ( reason ) {
					this.trigger( 'exchange.cancelPurchaseMethod' );
					alert( reason );
				} );
				this.addView( iframe );

				return iframe.open();
			} else if ( this.model.accepts( 'card' ) ) {
				this.visualCC = sourceView = new app.Views.VisualCC();
				this.addView( this.visualCC );
			} else {
				return this.purchase( e );
			}

			var complete = new app.Views.CompletePurchaseMethod( {
				sourceView: sourceView,
				model     : this.model,
				cart      : this.cart,
			} );

			this.addView( complete );
			this.listenTo( complete, 'exchange.doPurchase', this.purchase );
			this.listenTo( complete, 'exchange.cancelCompletePurchase', function () {
				this.removeView( complete );

				if ( this.tokenSelector ) {
					this.removeView( this.tokenSelector );
				}

				if ( this.visualCC ) {
					this.removeView( this.visualCC );
				}

				this.trigger( 'exchange.cancelPurchaseMethod' );
			} );
		},

		/**
		 * Purchase with this method.
		 *
		 * @since 2.0.0
		 *
		 * @param {Event} [e]
		 * @param {*} [additionalData]
		 */
		purchase: function ( e, additionalData ) {

			if ( e ) {
				var target = $( e.target );
				target.attr( 'disabled', true );
			}

			var data = _.extend( { nonce: this.model.get( 'nonce' ) }, additionalData );

			this.cart.purchase( this.model.id, data ).done( ( function ( transaction ) {
				this.onPurchaseFinished( transaction, target );
			} ).bind( this ) ).fail( function ( error ) {
				if ( target ) {
					target.removeAttr( 'disabled' );
				}

				alert( error );
			} );
		},

		/**
		 * Called when the purchase completes.
		 *
		 * @since 2.0.0
		 *
		 * @param {app.Models.Transaction} transaction
		 * @param {jQuery} $target
		 */
		onPurchaseFinished: function ( transaction, $target ) {
			this.trigger( 'exchange.purchaseFinished', transaction, $target );
		},
	} );

	/**
	 * The default purchase method view is a slimmed-down PurchaseMethod view aimed to have
	 * the customer complete their purchase as quickly as possible.
	 *
	 * For redirect views, the customer will be immediately redirected to the external gateway.
	 * For token views, the customer's primary token will be displayed and used.
	 * For Visual CC views, the Visual CC form will be displayed.
	 */
	app.Views.DefaultPurchaseMethod = app.View.extend( {
		template : wp.template( 'it-exchange-checkout-default-method' ),
		className: 'it-exchange-checkout-default-method',
		events   : {
			'click .it-exchange-checkout-default-method--action'      : 'onPurchaseClicked',
			'click .it-exchange-checkout-default-method--select-other': 'onOtherClicked',
		},

		allowOther  : true,
		cart        : null,
		defaultToken: null,

		purchaseMethodView: null,
		sourceView        : null,

		initialize: function ( options ) {
			this.cart = options.cart;
			this.purchaseMethodView = options.purchaseMethodView;
			this.allowOther = typeof options.allowOther === 'undefined' ? true : options.allowOther;

			if ( options.defaultToken ) {
				if ( options.defaultToken instanceof app.Models.PaymentToken ) {
					this.defaultToken = options.defaultToken;
				} else if ( _.isObject( options.defaultToken ) && !options.defaultToken instanceof Backbone.Model ) {
					this.defaultToken = new app.Models.PaymentToken( options.defaultToken, { parent: this.cart.customer() } );
				} else if ( _.isNumber( options.defaultToken ) ) {
					// Don't care if this fails because tokens aren't loaded yet. Goal is to be as fast as possible,
					// so content to fallback to the primary token for that customer
					var tokens = this.cart.customer().tokens();
					tokens.doFilter( { gateway: this.model.id }, {
						local : true,
						greedy: true,
						async : !Backbone.fetchCache.getCache( tokens )
					} );
					this.defaultToken = tokens.get( options.defaultToken );
				}
			}

			if ( !this.defaultToken && this.model.accepts( 'token' ) ) {
				this.defaultToken = new app.Models.PaymentToken( this.model.get( 'method.primaryToken' ) );
			}
		},

		render: function () {

			var attr = { i18n: i18n.checkout };
			attr.i18n.purchaseDefaultMethod = this.model.get( 'label' );

			this.$el.html( this.template( attr ) );

			if ( !this.allowOther ) {
				this.$( '.it-exchange-checkout-default-method--select-other' ).remove();
			}

			var sourceClass;

			if ( this.defaultToken ) {
				this.sourceView = new app.Views.PaymentTokenSelectorToken( {
					model  : this.defaultToken,
					count  : 0,
					tagName: 'div',
				} );
				sourceClass = 'it-exchange-checkout-default-method--source--token';
			} else if ( this.model.accepts( 'token' ) ) {
				var tokens = this.cart.customer().tokens();
				tokens.doFilter( { gateway: this.model.id }, { local: true } );
				this.sourceView = new app.Views.PaymentTokenSelector( { model: this.model, collection: tokens } );
				sourceClass = 'it-exchange-checkout-default-method--source--token-selector';
			} else if ( this.model.accepts( 'card' ) ) {
				this.sourceView = new app.Views.VisualCC();
				sourceClass = 'it-exchange-checkout-default-method--source--visual-cc';
			}

			if ( this.sourceView ) {
				this.views.add( '.it-exchange-checkout-default-method--source', this.sourceView );

				if ( sourceClass ) {
					this.$( '.it-exchange-checkout-default-method--source' ).addClass( sourceClass );
				}

				this.$( '.it-exchange-checkout-default-method--source input[type="radio"]' ).hide();
			}
		},

		onPurchaseClicked: function ( e ) {
			if ( this.defaultToken ) {
				this.purchaseMethodView.purchase( e, {
					token: this.defaultToken.id
				} );
			} else if ( this.model.accepts( 'token' ) ) {
				var gateway = this.model.id;
				var token = this.sourceView.selected(), tokenize;

				if ( _.isObject( token ) ) {
					if ( app.canTokenize( gateway ) ) {
						app.tokenize( gateway, 'card', token ).done( ( function ( tokenize ) {
							this.purchaseMethodView.purchase( e, { tokenize: tokenize } );
						}).bind( this ) );

						return;
					}

					if ( this.model.accepts( 'tokenize' ) ) {
						return this.purchaseMethodView.purchase( e, { tokenize: token } );
					}

					this.cart.customer().tokens().create( { source: token, gateway: gateway }, {
						wait   : true,
						success: ( function ( model ) {
							this.purchaseMethodView.purchase( e, { token: model.id } );
						} ).bind( this ),
					} );
				} else {
					this.purchaseMethodView.purchase( e, { token: token } );
				}
			} else if ( this.model.accepts( 'card' ) ) {
				this.purchaseMethodView.purchase( e, { card: this.sourceView.getCard() } );
			} else if ( this.model.getType() === 'redirect' ) {
				window.location = this.model.get( 'method.url' );
			} else {
				this.purchaseMethodView.purchase( e );
			}
		},

		onOtherClicked: function ( e ) {
			e.preventDefault();
			this.remove();
			this.trigger( 'exchange.selectOtherMethod' );
		},
	} );

	app.Views.CompletePurchaseMethod = app.View.extend( {

		template: wp.template( 'it-exchange-checkout-complete-purchase-method-button' ),
		events  : {
			'click .it-exchange-checkout-complete-purchase-method-button': 'complete',
			'click .it-exchange-checkout-cancel-purchase-method'         : 'cancel',
		},

		cart: null,

		initialize: function ( options ) {
			this.cart = options.cart;
			this.sourceView = options.sourceView;
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.$el.html( this.template( i18n.checkout ) );
		},

		cancel: function () {
			this.trigger( 'exchange.cancelCompletePurchase' );
		},

		complete: function ( e ) {

			if ( this.sourceView instanceof app.Views.VisualCC ) {
				this.triggerPurchase( e, { card: this.sourceView.getCard() } );
			} else if ( this.sourceView instanceof app.Views.PaymentTokenSelector ) {

				var gateway = this.model.id;
				var token = this.sourceView.selected(), tokenize;

				if ( _.isObject( token ) ) {
					if ( app.canTokenize( gateway ) ) {
						app.tokenize( gateway, 'card', token ).done( ( function ( tokenize ) {
							this.triggerPurchase( e, { tokenize: tokenize } );
						}).bind( this ) );

						return;
					}

					if ( this.model.accepts( 'tokenize' ) ) {
						return this.triggerPurchase( e, { tokenize: token } );
					}

					this.cart.customer().tokens().create( { source: token, gateway: gateway }, {
						wait   : true,
						success: ( function ( model ) {
							this.triggerPurchase( e, { token: model.id } );
						} ).bind( this ),
					} );
				} else {
					this.triggerPurchase( e, { token: token } );
				}
			}
		},

		triggerPurchase: function ( e, data ) {
			this.trigger( 'exchange.doPurchase', e, data );
		}
	} );

	app.Views.iFramePurchase = app.View.extend( {
		className: 'it-exchange-iframe-purchase-container',

		cart: null,

		initialize: function ( options ) {
			this.cart = options.cart;
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.$el.html( this.model.get( 'method.html' ) );
			this.$el.hide(); // We just want to inject JS
		},

		open: function () {
			var deferred = $.Deferred();
			itExchange.hooks.doAction( 'iFramePurchaseBegin.' + this.model.get( 'id' ), deferred );

			deferred.done( (function ( data ) {
				if ( data.cancelled ) {
					this.trigger( 'exchange.iFramePurchaseCancelled', this );
					this.trigger( 'exchange.iFramePurchaseCancelled.' + this.model.get( 'id' ) );
				} else if ( data.tokenize ) {
					this.trigger( 'exchange.iFramePurchaseDone', { tokenize: data.tokenize }, this );
				} else if ( data.one_time_token ) {
					this.trigger( 'exchange.iFramePurchaseDone', { one_time_token: data.one_time_token }, this );
				}
			}).bind( this ) );
			deferred.fail( (function ( data ) {
				this.trigger( 'exchange.iFramePurchaseFailed', data.message, this );
			}).bind( this ) );
		},
	} );

	app.Views.VisualCC = app.View.extend( {
		template: wp.template( 'it-exchange-visual-cc' ),

		events: {
			'keyup .it-exchange-visual-cc-number input': 'detectType'
		},

		render: function () {

			var attributes = {
				i18n: i18n.visualCC
			};

			this.$el.html( this.template( attributes ) );

			this.$( '.it-exchange-visual-cc--year-input' ).payment( 'restrictNumeric' );
			this.$( '.it-exchange-visual-cc--month-input' ).payment( 'restrictNumeric' );
			this.$( '.it-exchange-visual-cc-cvc input' ).payment( 'formatCardCVC' );
			this.$( '.it-exchange-visual-cc-number input' ).payment( 'formatCardNumber' );
		},

		detectType: function ( e ) {

			var $el = this.$( e.currentTarget );
			var number = $el.val();

			if ( number.length <= 2 ) {
				$el.removeClass( function ( index, css ) {
					return ( css.match( /(^|\s)card-\S+/g ) || [] ).join( ' ' );
				} );
			} else {
				var cardType = $.payment.cardType( number );

				$el.addClass( 'card-' + cardType );
			}
		},

		/**
		 * Get the card values.
		 *
		 * @since 2.0.0
		 *
		 * @returns {{number: *, year: *, month: *, cvc: *, name: *}}
		 */
		getCard: function () {
			return {
				number: this.$( '.it-exchange-visual-cc-number input' ).val().replace( /\s/g, '' ),
				year  : this.$( '.it-exchange-visual-cc--year-input' ).val(),
				month : this.$( '.it-exchange-visual-cc--month-input' ).val(),
				cvc   : this.$( '.it-exchange-visual-cc-code input' ).val(),
				name  : this.$( '.it-exchange-visual-cc-holder input' ).val(),
			};
		},
	} );

	app.Views.PaymentTokenSelector = app.View.extend( {

		template: wp.template( 'it-exchange-token-selector' ),
		class   : '.it-exchange-payment-tokens-selector',
		events  : {
			'change input[type="radio"]': 'onChange'
		},

		showAddNew: true,
		visualCC  : null,
		iframe    : null,

		thisCount: 0,

		initialize: function ( options ) {

			app.Views.PaymentTokenSelector.count++;

			this.thisCount = app.Views.PaymentTokenSelector.count;

			options = options || {};

			this.showAddNew = typeof options.showAddNew === 'undefined' ? true : options.showAddNew;

			if ( this.showAddNew ) {

				this.addNewRadio = new app.Views.PaymentTokenSelectorAddNew( {
					count: this.thisCount,
				} );
				this.views.add(
					'.it-exchange-payment-tokens-selector--list',
					this.addNewRadio
				);

				if ( this.model && this.model.getType() === 'iframe' ) {
					this.iframe = new app.Views.iFramePurchase( {
						model: this.model,
						cart : this.cart,
					} );
					this.views.add(
						'.it-exchange-payment-tokens-selector--add-new-container',
						this.iframe
					);
				} else {
					this.visualCC = new app.Views.VisualCC();
					this.views.add(
						'.it-exchange-payment-tokens-selector--add-new-container',
						this.visualCC
					);
				}
			}

			_.each( this.collection.models, this.addPaymentTokenView, this );

			this.collection.on( 'reset', function () {
				_.each( this.collection.models, this.addPaymentTokenView, this );
			}, this );

			app.View.prototype.initialize.apply( this, arguments );
		},

		selected: function () {
			var val = this.$( 'input[type="radio"]:checked' ).val();

			if ( val === 'add-new' ) {
				return this.visualCC.getCard();
			}

			return val;
		},

		/**
		 * Add a Payment Token View.
		 *
		 * @since 2.0.0
		 *
		 * @param {App.Models.PaymentToken} paymentTokenModel
		 */
		addPaymentTokenView: function ( paymentTokenModel ) {

			var options = {}, views;

			if ( this.showAddNew ) {
				if ( views = this.views.get( '.it-exchange-payment-tokens-selector--list' ) ) {
					options.at = views.length - 2;
				}
			}

			var view = new app.Views.PaymentTokenSelectorToken( {
				model : paymentTokenModel,
				count : this.thisCount,
				parent: this,
			} );
			this.views.add(
				'.it-exchange-payment-tokens-selector--list',
				view,
				options
			);

			if ( this.addNewRadio && this.$el.is( ':hidden' ) ) {
				this.addNewRadio.$( 'input' ).prop( 'checked', false );

				if ( this.visualCC ) {
					this.visualCC.$el.hide();
				}
			}
		},

		onChange: function () {

			if ( !this.showAddNew ) {
				return;
			}

			if ( this.addNewRadio.$( 'input' ).is( ':checked' ) ) {
				this.onAddNewSelected();
			} else {
				this.onAddNewDeselected();
			}
		},

		onAddNewSelected: function () {

			if ( this.visualCC ) {
				this.visualCC.$el.show();
			} else {
				this.iframe.open();
				this.listenTo( this.iframe, 'exchange.iFramePurchaseDone', function ( data ) {
					this.trigger( 'exchange.doPurchase', null, data );
				} );
				this.listenTo( this.iframe, 'exchange.iFramePurchaseCancelled', function () {
					this.$( 'input[type="radio"]:first' ).prop( 'checked', true );
				} );
				this.listenTo( this.iframe, 'exchange.iFramePurchaseFailed', function ( reason ) {
					this.trigger( 'exchange.cancelPurchaseMethod' );
					alert( reason );
				} );
			}
		},

		onAddNewDeselected: function () {
			if ( this.visualCC ) {
				this.visualCC.$el.hide();
			}
		},

		render: function () {

			var attributes = {
				showAddNew: this.showAddNew
			};

			this.$el.html( this.template( attributes ) );
			this.views.render();

			if ( this.visualCC && this.collection.length ) {
				this.visualCC.$el.hide();
			} else if ( this.visualCC ) {
				this.addNewRadio.$( 'input' ).prop( 'checked', true );
			}
		}
	} );

	app.Views.PaymentTokenSelector.count = 0;

	app.Views.PaymentTokenSelectorAddNew = app.View.extend( {

		tagName  : 'li',
		className: 'it-exchange-payment-tokens-selector--payment-token it-exchange-payment-tokens-selector--add-new',
		template : wp.template( 'it-exchange-token-selector-add-new' ),

		initialize: function ( options ) {
			this.count = options.count;
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {

			var attr = {
				i18n     : { addNewCard: i18n.paymentToken.addNew },
				inputName: 'paymentToken' + this.count,
			};

			this.$el.html( this.template( attr ) );
		},
	} );

	app.Views.PaymentTokenSelectorToken = app.View.extend( {

		tagName  : 'li',
		className: 'it-exchange-payment-tokens-selector--payment-token',
		template : wp.template( 'it-exchange-token-selector-token' ),

		cardBrands  : ['amex', 'discover', 'mastercard', 'visa'],
		defaultImage: 'credit',

		initialize: function ( options ) {
			this.count = options.count;
			this.parent = options.parent;
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {

			var attr = this.model.toJSON();
			attr.inputName = 'paymentToken' + this.count;

			if ( this.model.get( 'type.slug' ) === 'card' ) {
				attr.cardImage = Config.imageRoot;
				var issuer = this.model.get( 'issuer' ).toLowerCase();
				var i = this.cardBrands.indexOf( issuer );

				if ( i == -1 ) {
					attr.cardImage += this.defaultImage;
				} else {
					attr.cardImage += issuer;
				}

				attr.cardImage += '.png';
			}

			this.$el.html( this.template( attr ) );

			if ( this.parent && this.model.get( 'primary' ) && ( this.parent.$el.is( ':hidden' ) || $( 'input[name="' + attr.inputName + '"]:checked' ).length < 1) ) {
				this.$( 'input' ).prop( 'checked', true );
			}
		},
	} );

	app.Views.PaymentSummary = app.View.extend( {
		className: 'it-exchange-checkout-payment-summary',
		template : wp.template( 'it-exchange-checkout-payment-summary' ),
		events   : {
			'click button': 'onViewDetails'
		},

		render: function () {

			var attr = this.model.toJSON();
			attr.totalFormatted = this.model.totalFormatted();
			attr.dateFormatted = this.model.orderDateFormatted();
			attr.i18n = i18n.checkout;

			this.$el.html( this.template( attr ) );

			console.log( this.model );
		},

		onViewDetails: function () {
			window.location = this.model.getLinkUrl( 'alternate' );
		}
	} );

	app.Views.PaymentTokensManager = app.View.extend( {

		template: wp.template( 'it-exchange-manage-tokens' ),

		gateways: {},

		initialize: function () {

			if ( !this.collection ) {
				return;
			}

			var gateways = this.collection.groupBy( 'gateway.slug' );

			_.each( gateways, function ( tokens, gateway ) {

				if ( !this.gateways[gateway] ) {
					this.gateways[gateway] = {
						class: 'it-exchange-manage-tokens-list-' + gateway,
						label: tokens[0].get( 'gateway.label' ),
					};
				}

				_.each( tokens, function ( token ) {
					this.views.add(
						'.' + this.gateways[gateway].class,
						new app.Views.PaymentTokensManagerToken( { model: token } )
					);
				}, this );
			}, this );

			this.gateways = _.sortBy( this.gateways, 'label' );
			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {
			this.$el.html( this.template( { i18n: Config.i18n.paymentToken } ) );

			_.each( this.gateways, function ( details ) {
				this.$( '.it-exchange-manage-tokens-list-container' ).append(
					'<h5>' + details.label + '</h5><ul class="it-exchange-manage-tokens-list ' + details.class + '"></ul>'
				);
			}, this );

			this.views.render();
		},
	} );

	app.Views.PaymentTokensManagerToken = app.View.extend( {

		cardBrands  : ['amex', 'discover', 'mastercard', 'visa'],
		defaultImage: 'credit',

		tagName       : 'li',
		className     : 'it-exchange-manage-tokens-token',
		template      : wp.template( 'it-exchange-manage-tokens-token' ),
		animateRemoval: true,

		events: {
			'click .it-exchange-manage-tokens-token-action--edit'   : 'onEditClicked',
			'click .it-exchange-manage-tokens-token-action--save'   : 'onSaveClicked',
			'click .it-exchange-manage-tokens-token-action--cancel' : 'onCancelClicked',
			'click .it-exchange-manage-tokens-token-action--primary': 'onPrimaryClicked',
			'click .it-exchange-manage-tokens-token-action--delete' : 'onDeleteClicked',
		},

		initialize: function () {
			if ( this.model.get( 'primary' ) ) {
				this.$el.addClass( 'it-exchange-manage-tokens-token--primary' );
			}

			this.listenTo( this.model, 'change:primary', function () {
				this.$el.toggleClass( 'it-exchange-manage-tokens-token--primary' );
			} );

			this.listenTo( this.model, 'change:label.rendered', function () {
				this.$( '.it-exchange-manage-token-label' ).text( this.model.get( 'label.rendered' ) );
			} );

			this.listenTo( this.model, 'change:expiration.*', function () {
				this.$( '.it-exchange-manage-token-label-expiration' ).text(
					ExchangeCommon.zeroise( this.model.get( 'expiration.month' ), 2 ) + '/' + this.model.get( 'expiration.year' )
				);
			} );

			app.View.prototype.initialize.apply( this, arguments );
		},

		render: function () {

			var attr = this.model.toJSON();
			attr.isExpirationEditable = this.model.isExpirationEditable();
			attr.i18n = Config.i18n.paymentToken;

			if ( this.model.get( 'type.slug' ) === 'card' ) {
				attr.cardImage = Config.imageRoot;
				var issuer = this.model.get( 'issuer' ).toLowerCase();
				var i = this.cardBrands.indexOf( issuer );

				if ( i == -1 ) {
					attr.cardImage += this.defaultImage;
				} else {
					attr.cardImage += issuer;
				}

				attr.cardImage += '.png';
			}

			this.$el.html( this.template( attr ) );

			if ( $.payment && this.model.isExpirationEditable() ) {
				this.$( '.it-exchange-manage-tokens-token-edit--expiration-input' ).payment( 'formatCardExpiry' );
			}
		},

		toggleButtons: function () {
			this.$( '.it-exchange-manage-tokens-token-action--edit' ).toggle();
			this.$( '.it-exchange-manage-tokens-token-action--delete' ).toggle();
			this.$( '.it-exchange-manage-tokens-token-action--primary' ).toggle();
			this.$( '.it-exchange-manage-tokens-token-action--save' ).toggle();
			this.$( '.it-exchange-manage-tokens-token-action--cancel' ).toggle();
		},

		onEditClicked: function ( e ) {
			this.$( '.it-exchange-manage-token-label-container' ).hide();
			this.$( '.it-exchange-manage-tokens-token-edit-container' ).show();

			this.toggleButtons();
		},

		onCancelClicked: function ( e ) {
			this.$( '.it-exchange-manage-token-label-container' ).show();
			this.$( '.it-exchange-manage-tokens-token-edit-container' ).hide();

			this.toggleButtons();
		},

		onSaveClicked: function ( e ) {

			var label = this.$( '.it-exchange-manage-tokens-token-edit--label-input' ).val();

			var toSave = {};

			if ( label.length ) {
				toSave.label = label;
			}

			var expiration = this.$( '.it-exchange-manage-tokens-token-edit--expiration-input' ).val();

			if ( expiration ) {
				var parts = expiration.split( '/' );
				toSave.expiration = {};
				toSave.expiration.month = parts[0].trim();
				toSave.expiration.year = parts[1].trim();
			}

			if ( !_.isEmpty( toSave ) ) {
				this.model.save( toSave, { wait: true } );
			}

			this.$( '.it-exchange-manage-token-label-container' ).show();
			this.$( '.it-exchange-manage-tokens-token-edit-container' ).hide();

			this.toggleButtons();
		},

		onDeleteClicked: function ( e ) {
			this.model.destroy( {
				wait   : true,
				success: (function () {
					this.remove();
				}).bind( this )
			} );
		},

		onPrimaryClicked: function ( e ) {

			if ( this.model.get( 'primary' ) ) {
				return;
			}

			this.model.save( 'primary', true, {
				success: (function () {
					this.model.collection.each( function ( model ) {
						if ( model.id != this.model.id && model.get( 'gateway.slug' ) == this.model.get( 'gateway.slug' ) ) {
							model.set( 'primary', false );
						}
					}, this );
				}).bind( this )
			} );
		},

		_removeElement: function () {
			this.$el.fadeOut( 1000, (function () {
				this.$el.remove();
			}).bind( this ) );
		}
	} );

	app.Views.AddressForm = app.View.extend( {

		template: wp.template( 'it-exchange-customer-address-form' ),

		events: {
			'click .it-exchange-customer-addresses-address-action--save'  : 'onSaveClicked',
			'click .it-exchange-customer-addresses-address-action--cancel': 'onCancelClicked',
			'change .it-exchange-address-form-field--country select'      : 'onCountryChange',
		},

		countriesLoaded: false,
		statesLoaded   : false,
		states         : {},
		countries      : '',
		type           : '',

		initialize: function ( options ) {
			this.countries = options.countries;
			this.states = options.states;

			if ( this.model ) {
				this.listenTo( this.model, 'change', this.render );
			}

			if ( options.type ) {
				this.type = options.type;
			}

			app.View.prototype.initialize.apply( this, arguments );
		},

		onCountryChange: function ( e ) {
			var country = this.$( '.it-exchange-address-form-field--country select :selected' ).val();
			this.loadStatesSelect( country );
		},

		onSaveClicked: function ( e ) {

			var save = {}, val,
				fields = ['first-name', 'last-name', 'address1', 'address2', 'state', 'country', 'zip', 'city', 'label'];

			for ( var i = 0; i < fields.length; i++ ) {
				var field = fields[i], $input;

				switch ( field ) {
					case 'country':
						$input = this.$( '.it-exchange-address-form-field--country select :selected' );
						break;

					case 'state':
						$input = this.$( '.it-exchange-address-form-field--state select :selected' );

						if ( !$input.length ) {
							$input = this.$( '.it-exchange-address-form-field--state input' );
						}

						break;
					default:
						$input = this.$( '.it-exchange-address-form-field--' + field + ' :input' );
						break;

				}

				val = $input.val();

				if ( !this.model || this.model.get( field ) != val ) {
					save[field] = val;
				}
			}

			if ( !_.isEmpty( save ) && this.model ) {

				if ( this.type.length && this.type !== 'both' ) {
					save['type'] = this.type;
				}

				this.model.save( save );
			} else if ( this.collection ) {
				var address = this.collection.create( save );
				return this.trigger( 'exchange.closedFromCreate', address );
			}

			this.trigger( 'exchange.closedFromSave', save );
		},

		onCancelClicked: function () {
			this.trigger( 'exchange.closedFromCancel' );
		},

		render: function () {
			this.statesLoaded = this.countriesLoaded = false;

			var attr = {};

			if ( this.model ) {
				attr = this.model.toJSON();
			}

			attr.i18n = Config.i18n.address;

			this.$el.html( this.template( attr ) );
			this.loadCountriesSelect();
			this.loadStatesSelect();
			this.delegateEvents();
		},

		/**
		 * Load the countries selector.
		 *
		 * @since 2.0.0
		 */
		loadCountriesSelect: function () {

			if ( !this.countries || this.countriesLoaded ) {
				return;
			}

			var currentCountry = this.model ? this.model.get( 'country' ) : ExchangeCommon.config.baseCountry;

			this.countriesLoaded = true;
			this.$( '.it-exchange-address-form-field--country select' ).html( this.countries );

			if ( currentCountry.length ) {
				this.$( '.it-exchange-address-form-field--country select option[value="' + currentCountry + '"]' ).prop( 'selected', true );
			}

			this.$( '.it-exchange-address-form-field--country select' ).selectToAutocomplete();
		},

		/**
		 * Load the state input or select.
		 *
		 * @since 2.0.0
		 *
		 * @param {String} [country]
		 */
		loadStatesSelect: function ( country ) {

			if ( _.isEmpty( this.states ) ) {
				return;
			}

			country = country || ( this.model ? this.model.get( 'country' ) : '' );

			if ( this.statesLoaded == country ) {
				return;
			}

			var states = this.states[country];

			var current =
				this.$( '.it-exchange-address-form-field--state select :selected' ).val() ||
				this.$( '.it-exchange-address-form-field--state input' ).val() ||
				( this.model ? this.model.get( 'state' ) : '' );

			this.$( '.it-exchange-address-form-field--state :input' ).remove();

			if ( states ) {
				this.$( '.it-exchange-address-form-field--state' ).append( '<select>' + states + '</select>' );
				var $select = this.$( '.it-exchange-address-form-field--state select' );

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

				this.$( '.it-exchange-address-form-field--state' ).append( $input );
			}

			this.statesLoaded = country;
		},
	} );

	window.itExchange = window.itExchange || {};
	window.itExchange.api = app;

	/**
	 * Get a schema for a route URL.
	 *
	 * @since 2.0.0
	 *
	 * @param url {string} URL of the route to retrieve the schema for.
	 * @param [schemaID] {String} Unique ID for this schema.
	 * @param [async] {boolean} Whether to retrieve the schema asynchronously, defaults to true.
	 *
	 * @returns {$.Promise}
	 */
	function getSchema( url, schemaID, async ) {

		var cacheKey;

		if ( schemaID ) {
			cacheKey = schemaID;
		} else {
			cacheKey = url.replace( ExchangeCommon.getRestUrl( '', {}, false ), '' );
		}

		async = typeof async === 'undefined' ? true : async;

		if ( Schemas && Schemas[cacheKey] ) {
			return $.when( Schemas[cacheKey] );
		}

		// Store a copy of the schema model in the session cache if available.
		if ( !_.isUndefined( sessionStorage ) ) {
			try {
				var cached = sessionStorage.getItem( 'itExchangeAPISchema-' + cacheKey );

				if ( cached ) {
					return $.when( $.parseJSON( cached ) );
				}
			} catch ( error ) {
				// Fail silently, fixes errors in safari private mode.
			}
		}

		var deferred = $.Deferred();

		$.ajax( {
			async  : async,
			method : 'OPTIONS',
			url    : url,
			success: function ( data ) {

				if ( !data || !data.schema ) {
					return deferred.reject();
				}

				try {
					sessionStorage.setItem( 'itExchangeAPISchema-' + cacheKey, JSON.stringify( data.schema ) );
				} catch ( error ) {
					// Fail silently, fixes errors in safari private mode.
				}

				return deferred.resolve( data.schema );
			},
			error  : function () {
				deferred.reject();
			}
		} );

		return deferred.promise();
	}

	/**
	 * Get a nested property from an object.
	 *
	 * @since 2.0.0
	 *
	 * @param obj {Object}  Object to fetch attribute from
	 * @param path {String}  Object path e.g. 'user.name'
	 * @param [return_exists] {Boolean}
	 *
	 * @returns {*}
	 */
	function getNested( obj, path, return_exists ) {
		var separator = Backbone.DeepModel.keyPathSeparator;

		var fields = path ? path.split( separator ) : [];
		var result = obj;
		return_exists = return_exists || false;

		for ( var i = 0, n = fields.length; i < n; i++ ) {
			if ( return_exists && !_.has( result, fields[i] ) ) {
				return false;
			}
			result = result[fields[i]];

			if ( result == null && i < n - 1 ) {
				result = {};
			}

			if ( typeof result === 'undefined' ) {
				if ( return_exists ) {
					return true;
				}
				return result;
			}
		}

		if ( return_exists ) {
			return true;
		}

		return result;
	}

	function objToPaths( obj ) {
		var ret = {},
			separator = Backbone.DeepModel.keyPathSeparator;

		for ( var key in obj ) {
			var val = obj[key];

			if ( val && (val.constructor === Object || val.constructor === Array) && !_.isEmpty( val ) ) {
				//Recursion for embedded objects
				var obj2 = objToPaths( val );

				for ( var key2 in obj2 ) {
					var val2 = obj2[key2];

					ret[key + separator + key2] = val2;
				}
			} else {
				ret[key] = val;
			}
		}

		return ret;
	}

	var wrapError = function ( model, options ) {
		var error = options.error;
		options.error = function ( resp ) {
			if ( error ) error.call( options.context, model, resp, options );
			model.trigger( 'error', model, resp, options );
		};
	};

	var addMethod = function ( length, method, attribute ) {
		if ( length === 3 ) {
			return function ( iteratee, context ) {
				return _[method]( this[attribute], cb( iteratee, this ), context );
			};
		}
	};

	var addUnderscoreMethods = function ( Class, methods, attribute ) {
		_.each( methods, function ( length, method ) {
			if ( _[method] ) Class.prototype[method] = addMethod( length, method, attribute );
		} );
	};

	var cb = function ( iteratee, instance ) {
		if ( _.isFunction( iteratee ) ) return iteratee;
		if ( _.isObject( iteratee ) && !instance._isModel( iteratee ) ) return modelMatcher( iteratee );
		if ( _.isString( iteratee ) ) return function ( model ) { return model.get( iteratee ); };
		return iteratee;
	};

	var modelMatcher = function ( attrs ) {
		var matcher = _.matches( attrs );
		return function ( model ) {
			return matcher( objToPaths( model.attributes ) );
		};
	};
	var collectionMethods = {
		forEach : 3, each: 3, map: 3, collect: 3, find: 3, detect: 3, filter: 3,
		select  : 3, reject: 3, every: 3, all: 3, some: 3, any: 3, include: 3, includes: 3,
		contains: 3, max: 3, min: 3, first: 3,
		head    : 3, take: 3, initial: 3, rest: 3, tail: 3, drop: 3, last: 3,
		indexOf : 3, shuffle: 1, lastIndexOf: 3, sample: 3, partition: 3, groupBy: 3, countBy: 3,
		sortBy  : 3, indexBy: 3, findIndex: 3, findLastIndex: 3
	};

	addUnderscoreMethods( app.Collection, collectionMethods, 'models' );
})( window.ITExchangeRESTSchemas || {}, window.itExchange.common, jQuery, window._, window.Backbone, window.wp, window.ITExchangeRESTConfig );
