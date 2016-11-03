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

				if ( beforeSend ) {
					return beforeSend.apply( this, arguments );
				}
			};

			options.data = options.data || {};

			if ( model.filters ) {
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

		Models     : {},
		Collections: {},
		Views      : {},
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

			if ( !getNested( this.attributes, attr, true ) ) {
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

		console.log(options);
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

			if ( !this.get( 'customer' ) ) {
				return null;
			}

			return new app.Models.Customer( {
				id: this.get( 'customer' )
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
				url       : _.result( this, 'url' ),
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
		}
	} );

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