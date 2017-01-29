(function ( ExchangeAPI, Config, ExchangeCommon, $, _, Backbone, wp ) {
	"use strict";

	Config.transactions = Config.transactions || [];
	Config.transactionsSchema = Config.transactionsSchema || null;

	var app = window.ITExchangeTransactionList = {

		start: function ( config ) {

			this.nonce = config.nonce || ExchangeCommon.config.restNonce;
			this.transactions = new ExchangeAPI.Collections.Transactions( config.transactions, {
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

		Models     : {},
		Collections: {},
		Views      : {},

	};

	app.Views.View = wp.Backbone.View.extend( {
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
		className: 'it-exchange-transaction-list-pagination',
		template : wp.template( 'it-exchange-pagination' ),

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

		var customerTransactions = new ExchangeAPI.Collections.Transactions();
		/*customerTransactions.fetch( { data: { customer: 1 } } ).done( function ( data ) {
		 console.log( data );
		 } );*/

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

})( window.itExchange.api, window.ITExchangeTransactions, window.itExchange.common, jQuery, window._, window.Backbone, window.wp );
