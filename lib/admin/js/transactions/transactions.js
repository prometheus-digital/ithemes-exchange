(function ( Config, ExchangeCommon, $, _, Backbone, wp ) {
	"use strict";

	Config.transactions = Config.transactions || [];
	Config.transactionsSchema = Config.transactionsSchema || null;

	var app = window.ITExchangeTransactionList = {

		start: function ( config ) {

			this.nonce = config.nonce || ExchangeCommon.config.restNonce;
			this.transactions = new this.Collections.Transactions( config.transactions, {
				schema : config.transactionsSchema,
				context: 'embed',
				state  : {
					pageSize    : parseInt( config.perPage ) || 20,
					totalRecords: parseInt( config.transactionsTotal )
				}
			} );

			this.gridView = new this.Views.TransactionCardList( {
				collection: this.transactions
			} );
			this.gridView.inject( '#it-exchange-transactions-container' );

			this.paginationView = new this.Views.Pagination( {
				collection: this.transactions
			} );
			this.paginationView.inject( "#it-exchange-transactions-pagination-container" );
		},

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

			if ( model instanceof Backbone.PageableCollection ) {
				return Backbone.PageableCollection.prototype.sync( method, model, options );
			}

			return Backbone.sync( method, model, options );
		},

		Models     : {},
		Collections: {},
		Views      : {},

	};

	/**
	 * Single Transaction Model.
	 */
	app.Models.Transaction = Backbone.DeepModel.extend( {

		context: 'view',
		tags   : [],

		initialize: function ( attributes, options ) {

			this.sync = app.sync;

			if ( options && options.context ) {
				this.context = options.context;
			}

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
		},

		get: function ( attr ) {

			if ( !getNested( this.attributes, attr, true ) ) {
				if ( this.collection.schema ) {

					var availableInContext, property;

					if ( this.collection.schema.properties[attr] ) {
						availableInContext = this.collection.schema.properties[attr].context;
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

						if ( property = getNested( this.collection.schema.properties, imploded ) ) {
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

		descriptionOrDefault: function () {
			return this.get( 'description' ) || '(none)';
		},

		orderDateFormatted: function () {
			return ExchangeCommon.formatDate( this.get( 'order_date' ) );
		},

		totalFormatted: function () {
			return ExchangeCommon.formatPrice( this.get( 'total' ) );
		},
	} );

	app.Collections.Transactions = Backbone.PageableCollection.extend( {
		context    : 'view',
		model      : app.Models.Transaction,
		url        : ExchangeCommon.getRestUrl( 'transactions', {}, false ),
		state      : {},
		schema     : null,
		queryParams: {
			currentPage: 'page',
			pageSize   : 'per_page',
		},

		initialize: function ( models, options ) {

			if ( options.schema ) {
				this.setSchema( options.schema );
			}

			this.sync = app.sync;

			if ( this.schema === null ) {
				$.ajax( {
					async  : false,
					method : 'OPTIONS',
					url    : this.url,
					success: (function ( data ) {
						this.setSchema( data.schema );
					}).bind( this )
				} );
			}

			if ( options && options.context ) {
				this.context = options.context;
			}
		},

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
		}
	} );

	app.Views.View = wp.Backbone.View.extend( {
		inject: function ( selector ) {
			this.render();
			$( selector ).html( this.el );
			this.views.ready();
		},

		prepare: function () {
			if ( !_.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		}
	} );

	app.Views.TransactionCardList = app.Views.View.extend( {
		tagName  : 'section',
		className: 'it-exchange-transaction-card-list',

		initialize: function () {
			_.each( this.collection.models, this.addTransactionView, this );

			this.listenTo( this.collection, 'reset', this.onReset );
		},

		onReset: function () {
			this.views.remove();
			_.each( this.collection.models, this.addTransactionView, this );
			this.render();
		},

		render: function () {
			this.views.render();
		},

		addTransactionView: function ( transactionModel ) {
			this.views.add( new app.Views.TransactionCard( { model: transactionModel } ) );
		}
	} );

	app.Views.TransactionCard = app.Views.View.extend( {
		tagName  : 'article',
		className: 'it-exchange-transaction-card',
		template : wp.template( 'it-exchange-transaction-card' ),
		render   : function () {
			var attributes = this.model.attributes;
			attributes.orderDateFormatted = this.model.orderDateFormatted();
			attributes.descriptionFormatted = this.model.descriptionOrDefault();
			attributes.totalFormatted = this.model.totalFormatted();
			attributes.tags = this.model.tags;

			this.$el.html( this.template( attributes ) );
		},
	} );

	app.Views.Pagination = app.Views.View.extend( {
		tagName : 'it-exchange-transaction-list-pagination',
		template: wp.template( 'it-exchange-pagination' ),

		events: {
			'click .first-page'   : 'firstPage',
			'click .next-page'    : 'nextPage',
			'click .prev-page'    : 'prevPage',
			'click .last-page'    : 'lastPage',
			'change .current-page': 'changeCurrentPage',
		},

		initialize: function () {
			this.listenTo( this.collection, 'reset', this.render );
		},

		render: function () {

			if ( !this.collection ) {
				return;
			}

			var attributes = {
				itemsLabel       : this.collection.state.totalRecords + ' items',
				firstPageLabel   : 'First Page',
				previousPageLabel: 'Previous Page',
				currentPageLabel : 'Current Page',
				ofLabel          : 'of',
				nextPageLabel    : 'Next Page',
				lastPageLabel    : 'Last Page',
				hasPrevious      : this.collection.hasPreviousPage(),
				hasNext          : this.collection.hasNextPage(),
				currentPage      : this.collection.state.currentPage,
				totalPages       : this.collection.state.totalPages,
			};

			this.$el.html( this.template( attributes ) );
		},

		firstPage: function ( e ) {
			e.preventDefault();

			this.collection.getFirstPage( { reset: true } );
		},

		nextPage: function ( e ) {
			e.preventDefault();

			this.collection.getNextPage( { reset: true } );
		},

		prevPage: function ( e ) {
			e.preventDefault();

			this.collection.getPreviousPage( { reset: true } );
		},

		lastPage: function ( e ) {
			e.preventDefault();

			this.collection.getLastPage( { reset: true } );
		},

		changeCurrentPage: function ( e ) {
			this.collection.getPage( parseInt( this.$( e.currentTarget ).val() ), { reset: true } );
		},
	} );

	$( document ).ready( function () {

		app.start( Config );

		$( document ).on( 'mouseenter', '.it-exchange-transaction-card--tags li', function () {
			var $this = $( this );
			$this.css( {
				width          : '100%',
				color          : '#444',
				'border-radius': '3px'
			} );
		} );

		$( document ).on( 'mouseleave', '.it-exchange-transaction-card--tags li', function () {
			var $this = $( this );
			$this.css( {
				width          : '18px',
				'border-radius': '20px',
				color          : $this.css( 'background-color' )
			} );
		} );
	} );

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
})( window.ITExchangeTransactions, window.ExchangeCommon, jQuery, window._, window.Backbone, window.wp );