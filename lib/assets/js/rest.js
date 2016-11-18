(function ( ExchangeCommon, $, _, Backbone, wp, i18n ) {
	"use strict";

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

				if ( url ) {
					url += '?context=' + model.context;
					options.url = url;
				}
			}

			var beforeSend = options.beforeSend;

			options.beforeSend = function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );

				// This isn't exactly clean, but it is the easiest.
				if ( app.Models.Cart && model instanceof app.Models.Cart && model.guestEmail.length ) {
					xhr.setRequestHeader( 'Authorization', 'Basic ' + btoa( model.guestEmail + ':' ) );
				} else if ( app.Collections.CartItems && model instanceof app.Collections.CartItems && model.parent.guestEmail.length ) {
					xhr.setRequestHeader( 'Authorization', 'Basic ' + btoa( model.parent.guestEmail + ':' ) );
				}

				if ( beforeSend ) {
					return beforeSend.apply( this, arguments );
				}
			};

			if ( method !== 'read' && model instanceof app.Models.CartItem ) {

				var success = options.success;

				options.success = function ( m, resp, callbackOpts ) {

					if ( model.parentModel ) {
						model.parentModel.allItems().add( m );
						model.parentModel.fetch();
					}

					if ( success ) {
						success.call( callbackOpts.context, m, resp, callbackOpts );
					}
				};
			}

			if ( model.filters && method === 'read' ) {

				options.data = options.data || {};

				for ( var filter in model.filters ) {
					if ( model.filters.hasOwnProperty( filter ) ) {
						options.data[filter] = model.filters[filter].value;
					}
				}
			}

			if ( model instanceof Backbone.PageableCollection ) {
				return Backbone.PageableCollection.prototype.sync( method, model, options );
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
		context: 'view',
		tags   : [],

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
				if ( this.collection.schema ) {

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

					this.context = availableInContext[0];
					this.fetch( { async: false } );
				}
			}

			return Backbone.DeepModel.prototype.get.apply( this, arguments );
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

		context : 'view',
		schema  : null,
		baseUrl : ExchangeCommon.getRestUrl( '', {}, false ),
		filters : {},
		schemaID: '',

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
		 *
		 * @returns {*|XMLHttpRequest}
		 */
		filter: function ( filters, options ) {

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

			var success = options.success;

			options.success = (function ( a, b, c ) {

				this.trigger( 'filtered', this, options );

				if ( success ) {
					return success.apply( this, arguments );
				}
			}).bind( this );

			return this.fetch( options );
		},
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

	app.Models.Transaction = app.Model.extend( {

		tags: [],

		initialize: function ( attributes, options ) {

			this.tags = [];

			if ( this.get( 'purchase_mode' ) === 'sandbox' ) {
				this.tags.push( { hex: '#ffeb3b', label: 'Sandbox' } );
			}

			if ( this.get( 'cleared_for_delivery' ) ) {
				// Green
				this.tags.push( { hex: '#93c47d', label: this.get( 'status.label' ) } );
			} else {
				// Blue
				this.tags.push( { hex: '#76a5af', label: this.get( 'status.label' ) } );
			}

			app.Model.prototype.initialize.apply( this, arguments );
		},

		/**
		 * Send the receipt to the customer's email address.
		 *
		 * @since 2.0.0
		 *
		 * @returns {XMLHttpRequest}
		 */
		send_receipt: function () {
			return $.ajax( {
				method    : 'POST',
				url       : _.result( this, 'url' ) + '/send_receipt',
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
			if ( !this._refunds ) {
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

			return new app.Models.Customer( {
				id: this.get( 'customer' )
			} );
		},

		/**
		 * Get the payment token used to pay for this transaction.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.models.PaymentToken}
		 */
		payment_token: function () {

			if ( !this.get( 'payment_token' ) ) {
				return null;
			}

			return new app.Models.PaymentToken( {
				id: this.get( 'payment_token' )
			} );
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

						var data = $.parseJSON( xhr.responseText );

						deferred.reject( data.message || 'Error' );
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
	} );

	app.Collections.Refunds = app.Collection.extend( {
		model   : app.Models.Refund,
		parent  : null,
		route   : 'refunds',
		schemaID: 'refunds',
	} );

	app.Models.Customer = app.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'customers', {}, false ),

		/**
		 * Get a collection of the tokens this customer has.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.PaymentTokens}
		 */
		tokens: function () {
			if ( !this._tokens ) {
				this._tokens = new app.Collections.PaymentTokens( [], {
					parent : this,
					context: this.context,
				} );
			}

			return this._tokens;
		},
	} );

	app.Models.PaymentToken = app.Model.extend( {
		parentModel: null,
	} );

	app.Collections.PaymentTokens = app.Collection.extend( {
		model   : app.Models.PaymentToken,
		parent  : null,
		route   : 'tokens',
		schemaID: 'customer-tokens',

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

	app.Models.Subscription = app.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'subscriptions', {}, false ),

		/**
		 * Get the customer who is responsible for paying this subscription.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Customer}
		 */
		customer: function () {

			if ( !this.get( 'customer' ) ) {
				return null;
			}

			return new app.Models.Customer( {
				id: this.get( 'customer' )
			} );
		},

		/**
		 * Get the user who is receiving the benefits of this subscription.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Customer}
		 */
		beneficiary: function () {

			if ( !this.get( 'beneficiary' ) ) {
				return null;
			}

			return new app.Models.Customer( {
				id: this.get( 'beneficiary' )
			} );
		},

		/**
		 * Get the transaction used to purchase this subscription.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Transaction}
		 */
		transaction: function () {

			if ( !this.get( 'transaction' ) ) {
				return null;
			}

			return new app.Models.Transaction( {
				id: this.get( 'transaction' )
			} );
		},

		/**
		 * Cancel this subscription.
		 *
		 * @param [options] {*}
		 * @param [options.cancelled_by] {int|app.Models.Customer} Specify who cancelled the subscription.
		 * @param [options.reason] {string} The reason the subscription was cancelled.
		 *
		 * @returns {Promise}
		 */
		cancel: function ( options ) {
			options = options || {};

			var postData = {};

			if ( options.reason ) {
				postData.reason = options.reason;
			}

			if ( options.cancelled_by ) {

				if ( options.cancelled_by instanceof app.Models.Customer ) {
					postData.cancelled_by = options.cancelled_by.id;
				} else {
					postData.cancelled_by = options.cancelled_by;
				}
			}

			var deferred = $.Deferred();

			$.ajax( {
				method    : 'POST',
				url       : _.result( this, 'url' ) + '/cancel',
				data      : postData,
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );
				},

				success: function ( data ) {
					deferred.resolve( new app.Models.Subscription( data ) );
				},

				error: function ( xhr ) {

					var data = $.parseJSON( xhr.responseText );

					deferred.reject( data.message || 'Error' );
				}
			} );

			return deferred.promise();
		},

		/**
		 * Get all available upgrades.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		upgrades: function () {

			if ( !this._upgrades ) {
				this._upgrades = new app.Collections.ProrateOffers( [], {
					type    : 'upgrade',
					parent  : this,
					schemaID: 'subscription-upgrades',
				} );
			}

			return this._upgrades;
		},

		/**
		 * Get all available downgrades.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		downgrades: function () {

			if ( !this._downgrades ) {
				this._downgrades = new app.Collections.ProrateOffers( [], {
					type    : 'downgrade',
					parent  : this,
					schemaID: 'subscription-downgrades',
				} );
			}

			return this._downgrades;
		},
	} );
	app.Models.Membership = app.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'memberships', {}, false ),

		/**
		 * Get the user who is receiving the benefits of this membership.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Models.Customer}
		 */
		beneficiary: function () {

			if ( !this.get( 'beneficiary' ) ) {
				return null;
			}

			return new app.Models.Customer( {
				id: this.get( 'beneficiary' )
			} );
		},

		/**
		 * Get all available upgrades.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		upgrades: function () {

			if ( !this._upgrades ) {
				this._upgrades = new app.Collections.ProrateOffers( [], {
					type    : 'upgrade',
					parent  : this,
					schemaID: 'membership-upgrades',
				} );
			}

			return this._upgrades;
		},

		/**
		 * Get all available downgrades.
		 *
		 * @since 2.0.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		downgrades: function () {

			if ( !this._downgrades ) {
				this._downgrades = new app.Collections.ProrateOffers( [], {
					type    : 'downgrade',
					parent  : this,
					schemaID: 'membership-downgrades',
				} );
			}

			return this._downgrades;
		},
	} );

	app.Models.ProrateOffer = app.Model.extend( {
		idAttribute: 'product',
		parentModel: null,

		/**
		 * Accept this prorate offer.
		 *
		 * @since 2.0.0
		 *
		 * @param {app.Models.Cart} cart
		 *
		 * @returns {*} A promise that resolves to the cart item.
		 */
		accept: function ( cart ) {

			var url = _.result( this.collection, 'url' ),
				deferred = $.Deferred();

			if ( !url ) {
				return deferred.promise();
			}

			var data = { product: this.id, cart_id: cart.id };

			$.ajax( {
				method     : 'POST',
				url        : url,
				data       : JSON.stringify( data ),
				contentType: 'application/json',

				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );
				},

				success: function ( cart_item ) {

					var model = cart.products().get( cart_item.id );

					if ( !model ) {
						model = new app.Models.ProductCartItem( cart_item );
						cart.products().add( model );
						cart.allItems().add( model );
						cart.fetch();
					}

					deferred.resolve( model );
				},

				error: function ( xhr ) {

					var data = $.parseJSON( xhr.responseText );

					deferred.reject( data.message || 'Error' );
				}
			} );

			return deferred.promise();
		},
	} );

	app.Collections.ProrateOffers = app.Collection.extend( {
		model: app.Models.ProrateOffer,
		type : '',

		initialize: function ( models, opts ) {
			opts = opts || {};

			if ( !opts.type ) {
				throw new Error( 'type option must be available for ProrateOffers.' );
			}

			this.type = opts.type;
			this.route = opts.type + 's';
			this.schemaID = opts.schemaID;

			app.Collection.prototype.initialize.apply( this, arguments );
		}
	} );

	app.loadCart = function ( create, email ) {

		create = create || false;
		email = email || '';

		var deferred = $.Deferred();

		/**
		 * @property {app.Collections.CartItems} products
		 * @property {app.Collections.CartItems} coupons
		 * @property {app.Collections.CartItems} fees
		 */
		app.Models.Cart = app.Model.extend( {
			urlRoot    : ExchangeCommon.getRestUrl( 'carts', {}, false ),
			sortedItems: {},
			_allItems  : null,
			guestEmail : '',

			initialize: function ( data, opts ) {
				this.sortedItems = _.chain( this.get( 'items' ) ).groupBy( 'type' ).value();
				this.on( 'change:total_lines', this.updateTotalLines, this );

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

			/**
			 * Get the available purchase methods for this cart.
			 *
			 * @since 2.0.0
			 *
			 * @returns {app.Collections.PurchaseMethods}
			 */
			getPurchaseMethods: function () {

				if ( !this._purchaseMethods ) {
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

					beforeSend: function ( xhr ) {xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );},

					success: function ( data ) {
						deferred.resolve( new app.Models.Transaction( data ) );
					},

					error: function ( xhr ) {

						var data = $.parseJSON( xhr.responseText );

						deferred.reject( data.message || 'Error' );
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

				if ( this._allItems && this._allItems.length ) {
					return this._allItems;
				}

				var collection = new app.Collections.CartItems, items = this.get( 'items' ), item, model, modelCollection;

				for ( var i = 0; i < items.length; i++ ) {
					item = items[i];
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

				return new app.Models.Customer( {
					id: this.get( 'customer' )
				} );
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
			model   : app.Models.PurchaseMethod,
			parent  : null, // Parent cart.
			route   : 'purchase',
			schemaID: 'cart-purchase',
		} );

		app.Models.CartItem = app.Model.extend( {} );

		app.Collections.CartItems = app.Collection.extend( {
			model    : app.Models.CartItem,
			parent   : null, // Parent cart.
			route    : 'items',
			itemType : null,
			editable : false,
			typeLabel: '',

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

			var deferred = $.Deferred();

			if ( !guestEmail.length ) {

				$.ajax( {
					method: 'POST',
					url   : ExchangeCommon.getRestUrl( 'carts', {}, false ),
					data  : data,

					beforeSend: function ( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );
					},

					success: function ( cart ) {
						deferred.resolve( new app.Models.Cart( cart ) );
					},

					error: function () {
						deferred.reject( arguments );
					}
				} );
			} else {

				$.ajax( {
					method: 'POST',
					url   : ExchangeCommon.getRestUrl( 'carts', {}, false ),
					data  : data,

					beforeSend: function ( xhr ) {
						xhr.setRequestHeader( 'Authorization', 'Basic ' + btoa( email + ':' ) )
					},

					success: function ( cart ) {
						var model = new app.Models.Cart( cart );
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

		$.get( ExchangeCommon.getRestUrl( 'cart_item_types' ), function ( types ) {

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
					schemaID : 'cart-' + type.id,
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
		} );

		return deferred.promise();
	};

	/* ---------- Views ---------- */

	app.View = wp.Backbone.View.extend( {
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
		}
	} );

	app.Views.Checkout = app.View.extend( {

		template: wp.template( 'it-exchange-checkout' ),

		showLineItems               : true,
		showLineItemTotals          : true,
		includeTotalInLineItemTotals: true,
		defaultPurchaseMethod       : '',

		initialize: function ( options ) {

			options = options || {};

			if ( typeof options.showLineItems === 'undefined' ) {
				this.showLineItems = true;
			} else {
				this.showLineItems = options.showLineItems;
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

			if ( typeof options.defaultPurchaseMethod === 'undefined' ) {
				this.defaultPurchaseMethod = '';
			} else {
				this.defaultPurchaseMethod = options.defaultPurchaseMethod;
			}

			if ( !this.model ) {
				return;
			}

			if ( this.showLineItems ) {
				this.views.add(
					'.it-exchange-checkout-line-items-container',
					new app.Views.CheckoutLineItems( {
						collection: this.model.allItems(),
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

			var methods = this.model.getPurchaseMethods();

			if ( methods.models.length ) {
				_.each( methods.models, this.addPurchaseMethodView, this );
			} else {
				methods.fetch().done( (function () {
					_.each( methods.models, this.addPurchaseMethodView, this );
				}).bind( this ) );
			}
		},

		render: function () {
			this.$el.html( this.template( {
				showLineItems     : this.showLineItems,
				showLineItemTotals: this.showLineItemTotals,
			} ) );
			this.views.render();
		},

		addPurchaseMethodView: function ( purchaseMethodModel ) {
			this.views.add( '.it-exchange-checkout-purchase-methods', new app.Views.PurchaseMethod( {
				model   : purchaseMethodModel,
				cart    : this.model,
				checkout: this,
			} ) );
		},
	} );

	app.Views.CheckoutLineItems = app.View.extend( {

		tagName  : 'ul',
		className: 'it-exchange-checkout-line-items',

		_lineItemTemplates: {},

		initialize: function () {
			_.each( this.collection.models, this.addLineItemView, this );

			this.collection.on( 'add', this.addLineItemView, this );
			this.collection.on( 'remove', this.removeViewByModel, this );
		},

		render: function () {
			this.views.render();
		},

		addLineItemView: function ( lineItemModel ) {

			var view = new app.Views.CheckoutLineItem( {
				model: lineItemModel
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

		render: function () {

			var json = this.model.toJSON();
			json.totalFormatted = ExchangeCommon.formatPrice( json.total );

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
		},

		render: function () {
			this.views.render();
		},

		addLineItemTotalsView: function ( lineItemTotalModel ) {

			var options = {};

			if ( this.includeTotal ) {
				options.at = this.collection.length - 2;
			}

			this.views.add( new app.Views.CheckoutLineItemTotal( {
				model: lineItemTotalModel
			} ), options );
		},
	} );

	app.Views.CheckoutLineItemTotal = app.View.extend( {

		template: wp.template( 'it-exchange-checkout-line-items-total' ),
		tagName : 'li',

		initialize: function () {
			this.model.on( 'change:total', this.render, this );
			this.model.on( 'change:description', this.render, this );
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
		},

		render: function () {

			var attributes = {
				slug          : 'total',
				label         : 'Total',
				total         : this.model.get( 'total' ),
				description   : '',
				totalFormatted: this.model.totalFormatted(),
			};

			this.$el.html( this.template( attributes ) );
		},
	} );

	app.Views.PurchaseMethod = app.View.extend( {
		template: wp.template( 'it-exchange-checkout-purchase-method-button' ),
		tagName : 'li',
		events  : {
			'click button': 'onMethodSelected'
		},

		cart         : null,
		checkout     : null,
		visualCC     : null,
		tokenSelector: null,

		initialize: function ( options ) {
			this.cart = options.cart;
			this.checkout = options.checkout;
		},

		render: function () {
			this.$el.html( this.template( this.model.toJSON() ) );
		},

		onMethodSelected: function ( e ) {

			if ( this.model.getType() === 'redirect' ) {
				window.location = this.model.get( 'method.url' );
			}

			this.checkout.$( '.it-exchange-checkout-purchase-methods' ).hide();

			if ( this.model.accepts( 'card' ) ) {
				this.visualCC = new app.Views.VisualCC();
				this.checkout.views.add( '.it-exchange-checkout-additional-info-container', this.visualCC );
			} else if ( this.model.accepts( 'token' ) ) {

				var tokens = this.cart.customer().tokens();
				tokens.filter( { gateway: this.model.id } );

				this.tokenSelector = new app.Views.PaymentTokenSelector( {
					collection: tokens
				} );
				this.checkout.views.add( '.it-exchange-checkout-additional-info-container', this.tokenSelector );
			} else {
				return this.purchase( e );
			}

			this.checkout.views.add( '.it-exchange-checkout-additional-info-container', new app.Views.CompletePurchaseMethod( {
				purchaseMethodView: this,
			} ) );
		},

		/**
		 * Purchase with this method.
		 *
		 * @since 2.0.0
		 *
		 * @param {Event} e
		 * @param {*} [additionalData]
		 */
		purchase: function ( e, additionalData ) {

			var target = $( e.target );
			var data = _.extend( { nonce: this.model.get( 'nonce' ) }, additionalData );

			target.attr( 'disabled', true );

			/**
			 * @param {app.Models.Transaction} transaction
			 */
			this.cart.purchase( this.model.id, data ).done( function ( transaction ) {
				target.removeAttr( 'disabled' );
				target.text( 'Purchased! ' + transaction.get( 'order_number' ) );
			} ).fail( function ( error ) {
				target.removeAttr( 'disabled' );
				alert( error );
			} );
		}
	} );

	app.Views.CompletePurchaseMethod = app.View.extend( {

		tagName  : 'button',
		className: 'it-exchange-checkout-complete-purchase-method-button btn button',
		events   : {
			'click': 'complete'
		},

		purchaseMethodView: null,

		initialize: function ( options ) {
			this.purchaseMethodView = options.purchaseMethodView;
		},

		render: function () {
			this.$el.html( 'Complete Purchase' );
		},

		complete: function ( e ) {

			if ( this.purchaseMethodView.visualCC ) {
				this.purchaseMethodView.purchase( e, {
					card: this.purchaseMethodView.visualCC.getCard()
				} );
			} else if ( this.purchaseMethodView.tokenSelector ) {

				var gateway = this.purchaseMethodView.model.id;
				var token = this.purchaseMethodView.tokenSelector.selected(), tokenize;

				if ( token instanceof Object ) {
					if ( app.canTokenize( gateway ) ) {
						tokenize = app.tokenize( gateway, 'card', token );
					} else {
						tokenize = token;
					}

					if ( this.purchaseMethodView.model.accepts( 'tokenize' ) ) {
						return this.purchaseMethodView.purchase( e, { tokenize: tokenize } );
					}

					this.purchaseMethodView.cart.customer().tokens().create( {
						source : token,
						gateway: gateway
					}, {
						wait   : true,
						success: function ( model ) {
							this.purchaseMethodView.purchase( e, { token: model.id } )
						},
					} );
				} else {
					this.purchaseMethodView.purchase( e, { token: token } );
				}
			}
		}
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
				number: this.$( '.it-exchange-visual-cc-number input' ).val(),
				year  : this.$( '.it-exchange-visual-cc--year-input' ).val(),
				month : this.$( '.it-exchange-visual-cc--month-input' ).val(),
				cvc   : this.$( '.it-exchange-visual-cc-cvc input' ).val(),
				name  : this.$( '.it-exchange-visual-cc-name input' ).val(),
			};
		},
	} );

	app.Views.PaymentTokenSelector = app.View.extend( {

		template: wp.template( 'it-exchange-token-selector' ),
		class   : '.it-exchange-payment-tokens-selector',
		events  : {
			'change input': 'onChange'
		},

		showAddNew: true,
		visualCC  : null,

		initialize: function ( options ) {

			options = options || {};

			this.showAddNew = typeof options.showAddNew === 'undefined' ? true : options.showAddNew;

			_.each( this.collection.models, this.addPaymentTokenView, this );

			this.collection.on( 'reset', function () {
				_.each( this.collection.models, this.addPaymentTokenView, this );
			}, this );

			if ( this.showAddNew ) {

				this.visualCC = new app.Views.VisualCC();
				this.addNewRadio = new app.Views.PaymentTokenSelectorAddNew();

				this.views.add(
					'.it-exchange-payment-tokens-selector--add-new-visual-cc-container',
					this.visualCC
				);

				this.views.add(
					'.it-exchange-payment-tokens-selector--list',
					this.addNewRadio
				);
			}
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
		 * @since 1.36.0
		 *
		 * @param {App.Models.PaymentToken} paymentTokenModel
		 */
		addPaymentTokenView: function ( paymentTokenModel ) {

			var options = {};

			if ( this.showAddNew ) {
				options.at = this.views.get( '.it-exchange-payment-tokens-selector--list' ).length - 2;
			}

			this.views.add(
				'.it-exchange-payment-tokens-selector--list',
				new app.Views.PaymentTokenSelectorToken( { model: paymentTokenModel } ),
				options
			);
		},

		onChange: function () {

			if ( !this.visualCC ) {
				return;
			}

			if ( this.addNewRadio.$( 'input' ).is( ':checked' ) ) {
				this.visualCC.$el.show();
			} else {
				this.visualCC.$el.hide();
			}
		},

		render: function () {

			var attributes = {
				showAddNew: this.showAddNew
			};

			this.$el.html( this.template( attributes ) );
			this.views.render();

			if ( this.visualCC ) {
				this.visualCC.$el.hide();
			}
		}
	} );

	app.Views.PaymentTokenSelectorAddNew = app.View.extend( {

		tagName  : 'li',
		className: 'it-exchange-payment-tokens-selector--add-new',
		template : wp.template( 'it-exchange-token-selector-add-new' ),

		render: function () {
			this.$el.html( this.template( { i18n: { addNewCard: 'Add New' } } ) );
		},
	} );

	app.Views.PaymentTokenSelectorToken = app.View.extend( {

		tagName  : 'li',
		className: 'it-exchange-payment-tokens-selector--payment-token',
		template : wp.template( 'it-exchange-token-selector-token' ),

		render: function () {
			this.$el.html( this.template( this.model.toJSON() ) );
		},
	} );

	window.ITExchangeAPI = app;

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

		if ( ExchangeCommon.config.schemas && ExchangeCommon.config.schemas[url] ) {
			return $.when( ExchangeCommon.config.schemas[url] );
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
})( window.ExchangeCommon, jQuery, window._, window.Backbone, window.wp, window.ITExchangeRESTi18n );
