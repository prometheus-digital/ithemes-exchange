(function ( ExchangeCommon, $, _, Backbone, wp ) {
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
		 * @since 1.36.0
		 *
		 * @param {String} gateway Gateway id. Ex. 'stripe'.
		 * @param {String} type Type of payment source to tokenize. 'card' or 'bank'.
		 * @param {*} tokenize Source data to tokenize.
		 * @param {*} [tokenize.address} Address to send to the gateway. Helps with verification purposes.
		 *
		 * @returns {*}
		 */
		tokenize: function ( gateway, type, tokenize ) {
			if ( !ITExchangeRESTTokenizers || !ITExchangeRESTTokenizers[gateway] ) {
				return false;
			}

			return ITExchangeRESTTokenizers[gateway].fn( type, tokenize );
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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

		context: 'view',
		schema : null,
		baseUrl: ExchangeCommon.getRestUrl( '', {}, false ),
		filters: {},

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
				getSchema( _.result( this, 'url' ), false ).done( (function ( schema ) {
					this.setSchema( schema );
				}).bind( this ) );
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		model : app.Models.Refund,
		parent: null,
		route : 'refunds'
	} );

	app.Models.Customer = app.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'customers', {}, false ),

		/**
		 * Get a collection of the tokens this customer has.
		 *
		 * @since 1.36.0
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
		model : app.Models.PaymentToken,
		parent: null,
		route : 'tokens',

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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
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
		 * @since 1.36.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		upgrades: function () {

			if ( !this._upgrades ) {
				this._upgrades = new app.Collections.ProrateOffers( [], {
					type  : 'upgrade',
					parent: this,
				} );
			}

			return this._upgrades;
		},

		/**
		 * Get all available downgrades.
		 *
		 * @since 1.36.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		downgrades: function () {

			if ( !this._downgrades ) {
				this._downgrades = new app.Collections.ProrateOffers( [], {
					type  : 'downgrade',
					parent: this,
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
		 * @since 1.36.0
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
		 * @since 1.36.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		upgrades: function () {

			if ( !this._upgrades ) {
				this._upgrades = new app.Collections.ProrateOffers( [], {
					type  : 'upgrade',
					parent: this,
				} );
			}

			return this._upgrades;
		},

		/**
		 * Get all available downgrades.
		 *
		 * @since 1.36.0
		 *
		 * @returns {app.Collections.ProrateOffers}
		 */
		downgrades: function () {

			if ( !this._downgrades ) {
				this._downgrades = new app.Collections.ProrateOffers( [], {
					type  : 'downgrade',
					parent: this,
				} );
			}

			return this._downgrades;
		},
	} );

	app.Models.ProrateOffer = app.Model.extend( {
		idAttribute: 'product',

		/**
		 * Accept this prorate offer.
		 *
		 * @since 1.36.0
		 *
		 * @param {app.Models.Cart} cart
		 *
		 * @returns {*} A promise that resolves to the cart item.
		 */
		accept: function ( cart ) {

			var url = _.result( this, 'url' ),
				deferred = $.Deferred();

			$.ajax( {
				method : 'POST',
				url    : url,
				data   : { product: this.id, cart_id: cart.id },
				success: function ( cart_item ) {

					var model = cart.products.get( cart_item.id );

					if ( !model ) {
						model = new app.Models.ProductCartItem( cart_item );
						cart.products.add( model );
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

			app.Collection.prototype.apply( this, arguments );
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
			guestEmail : '',

			initialize: function ( data, opts ) {
				this.sortedItems = _.chain( this.get( 'items' ) ).groupBy( 'type' ).value();

				app.Model.prototype.initialize.apply( this, arguments );
			},

			/**
			 * Get the available purchase methods for this cart.
			 *
			 * @since 1.36.0
			 *
			 * @returns {*}
			 */
			getPurchaseMethods: function () {

				var deferred = $.Deferred();

				$.ajax( {
					method: 'GET',
					url   : _.result( this, 'url' ) + 'purchase',

					beforeSend: function ( xhr ) {xhr.setRequestHeader( 'X-WP-Nonce', ExchangeCommon.config.restNonce );},

					success: function ( data ) {

						var methods = _.chain( data ).groupBy( 'id' ).value();

						deferred.resolve( methods );
					},

					error: function ( xhr ) {

						var data = $.parseJSON( xhr.responseText );

						deferred.reject( data.message || 'Error' );
					},
				} );

				return deferred.promise();
			},

			/**
			 * Purchase the cart.
			 *
			 * @sine 1.36.0
			 *
			 * @param {String} gateway Select gateway method.
			 * @param {*} data Additional data required to make the transaction.
			 * @param {String} data.nonce Nonce
			 * @param {Integer} [data.token] If the purchase method uses tokens, pass the Payment Token id here.
			 * @param {*} [data.tokenize] The data that should automatically be tokenized.
			 * @param {*} [data.card] If paying via a credit card that cannot be tokenized.
			 *
			 * @returns {*} Promise that resolves to a Transaction model.
			 */
			purchase: function ( gateway, data ) {

				var deferred = $.Deferred();

				data.id = gateway;

				$.ajax( {
					method: 'POST',
					url   : _.result( this, 'url' ) + 'purchase',
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
			 * Get the customer purchasing this cart.
			 *
			 * @since 1.36.0
			 *
			 * @returns {app.Models.Customer}
			 */
			customer : function () {

				if ( !this.get( 'customer' ) ) {
					return null;
				}

				return new app.Models.Customer( {
					id: this.get( 'customer' )
				} );
			},
			/**
			 * Get the total for a given line.
			 *
			 * @since 1.36.0
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

		app.Models.CartItem = app.Model.extend( {} );

		app.Collections.CartItems = app.Collection.extend( {
			model    : app.Models.CartItem,
			parent   : null, // Parent cart.
			route    : 'items',
			itemType : null,
			editable : false,
			typeLabel: '',
		} );

		/**
		 * Create a cart.
		 *
		 * @since 1.36.0
		 *
		 * @param {String} [guestEmail]
		 * @param {*} [opts]
		 * @param {Boolean} [opts.is_main] Should this be the main cart for the customer. Defaults to true.
		 *
		 * @returns {app.Models.Cart}
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
					sync: app.sync
				} );

				app.Collections[capitalized + 'CartItems'] = app.Collections.CartItems.extend( {
					model    : model,
					route    : 'items/' + type.id,
					itemType : type.id,
					editable : type.editable,
					typeLabel: type.label,
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

	window.ITExchangeAPI = app;

	/**
	 * Get a schema for a route URL.
	 *
	 * @since 1.36.0
	 *
	 * @param url     {string}  URL of the route to retrieve the schema for.
	 * @param [async] {boolean} Whether to retrieve the schema asynchronously, defaults to true.
	 *
	 * @returns {$.Promise}
	 */
	function getSchema( url, async ) {

		async = typeof async === 'undefined' ? true : async;

		if ( ExchangeCommon.config.schemas && ExchangeCommon.config.schemas[url] ) {
			return $.when( ExchangeCommon.config.schemas[url] );
		}

		// Store a copy of the schema model in the session cache if available.
		if ( !_.isUndefined( sessionStorage ) ) {
			try {

				var cacheKey = url.replace( ExchangeCommon.getRestUrl( '', {}, false ), '' );
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
					var cacheKey = url.replace( ExchangeCommon.getRestUrl( '', {}, false ), '' );
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
	 * @since 1.36.0
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
})( window.ExchangeCommon, jQuery, window._, window.Backbone, window.wp );