(function ( $ ) {
	"use strict";

	$( document ).ready( function () {

		$( '.handlediv, .hndle' ).remove();

		$( '.tip' ).tooltip();

		// do transaction status update
		$( '#it-exchange-update-transaction-status' ).on( 'change', function () {
			var nonce = $( '#it-exchange-update-transaction-nonce' ).val();
			var currentStatus = $( '#it-exchange-update-transaction-current-status' ).val();
			var newStatus = $( '#it-exchange-update-transaction-status' ).find( ":selected" ).val();
			var txnID = $( '#it-exchange-update-transaction-id' ).val();

			var data = {
				'action'                    : 'it-exchange-update-transaction-status',
				'it-exchange-nonce'         : nonce,
				'it-exchange-current-status': currentStatus,
				'it-exchange-new-status'    : newStatus,
				'it-exchange-transaction-id': txnID
			};

			$.post( ajaxurl, data, function ( response ) {
				$( '#it-exchange-update-transaction-status-success, #it-exchange-update-transaction-status-failed' )
					.css( 'opacity', '0' ).stop();

				$( '#it-exchange-update-transaction-status-' + response ).animate( {
					opacity: '1'
				}, 500, function () {
					$( this ).delay( 2000 ).animate( {
						opacity: '0'
					} );
				} );
			} ).fail( function () {
				$( '#it-exchange-update-transaction-status-failed' ).css( 'opacity', '0' ).stop().animate( {
					opacity: '1'
				}, 500, function () {
					$( this ).delay( 2000 ).animate( {
						opacity: '0'
					} );
				} );
			} );
		} );

		var activityContainer = $( "#activity-stream" );
		activityContainer.css( {
			height: $( "#it-exchange-transaction-details .inside" ).height()
		} );

		var activityCollection = new ActivityCollection( activityContainer, EXCHANGE.items.map( function ( item ) {
			return new Activity( item );
		} ) );

		var $filterContainer = $( ".exchange-filter-action-container" );
		var $noteWritingContainer = $( ".exchange-note-writing-container" );
		var $noteEditor = $( "#exchange-note-editor" );
		var $notePublic = $( "#exchange-notify-customer" );

		$( "#exchange-activity-filter" ).change( function () {
			activityCollection.filter( $( this ).val() );
		} );

		$( "#exchange-add-note" ).click( function ( e ) {

			e.preventDefault();
			$noteWritingContainer.show();
			$filterContainer.hide();
		} );

		$( "#exchange-post-note" ).click( function ( e ) {

			e.preventDefault();

			var $this = $( this );
			$this.prop( 'disabled', true );

			createNote( $noteEditor.val(), $notePublic.is( ':checked' ) ).fail( function ( message ) {
				alert( message );
			} ).done( function ( activity ) {
				activityCollection.add( activity );

				$filterContainer.show();
				$noteWritingContainer.hide();
				$noteEditor.val( '' );
				$notePublic.prop( 'checked', false ).change();
			} ).always( function () {
				$this.prop( 'disabled', false );
			} );
		} );

		$notePublic.change( function () {

			if ( $notePublic.is( ':checked' ) ) {
				$noteEditor.addClass( 'exchange-new-note-public' );
			} else {
				$noteEditor.removeClass( 'exchange-new-note-public' );
			}

		} );

		$( document ).keyup( function ( e ) {
			if ( e.keyCode == 27 ) {
				$filterContainer.show();
				$noteWritingContainer.hide();
			}
		} );

		$noteEditor.keydown( function ( e ) {
			if ( ( e.metaKey || e.ctrlKey ) && e.keyCode == 13 ) {
				$( "#exchange-post-note" ).click();
			}
		} );

		enqueueHeartbeat();

		$( document ).on( 'heartbeat-tick.it-exchange-txn-activity', function ( e, data ) {

			if ( data.hasOwnProperty( 'it-exchange-txn-activity' ) ) {
				data[ 'it-exchange-txn-activity' ][ 'items' ].forEach( function ( activity ) {
					activityCollection.add( new Activity( activity ) );
				} );
			}

			enqueueHeartbeat();
		} );

		/**
		 * Enqueue the heartbeat to get latest activity items.
		 *
		 * @since 1.34
		 */
		function enqueueHeartbeat() {

			var latest = activityCollection.getLatest();

			wp.heartbeat.enqueue( 'it-exchange-txn-activity', {
				txn   : EXCHANGE.txn,
				latest: latest ? latest.getTime().toISOString() : false
			} );
		}
	} );

	/**
	 * Create a new note.
	 *
	 * @since 1.34
	 *
	 * @param note
	 * @param isPublic
	 * @returns {*} Promise that resolves to an activity.
	 */
	function createNote( note, isPublic ) {

		var data = {
			action  : 'it-exchange-add-note',
			note    : note,
			isPublic: Number( isPublic ),
			nonce   : EXCHANGE.nonce,
			txn     : EXCHANGE.txn
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, function ( response ) {

			if ( ! response.success ) {
				deferred.reject( response.data.message );

				return;
			}

			deferred.resolve( new Activity( response.data.activity ) );
		} );

		return deferred.promise();
	}

	/**
	 * Activity collection.
	 *
	 * @since 1.34
	 *
	 * @param $container {*}
	 * @param items [items]
	 * @returns {{}}
	 * @constructor
	 */
	function ActivityCollection( $container, items ) {

		this.currentTypeFilter = '';
		this.$container = $container;
		this.items = items === undefined ? [] : items;

		this.items.sort( function ( a, b ) {
			return b.getTime() - a.getTime();
		} );

		this.render();
	}

	/**
	 * Add an item to the collection.
	 *
	 * This renders the item as well.
	 *
	 * @since 1.34
	 *
	 * @param activity
	 */
	ActivityCollection.prototype.add = function ( activity ) {

		if ( this.contains( activity ) ) {
			return;
		}

		this.items.push( activity );
		this.items.sort( function ( a, b ) {
			return b.getTime() - a.getTime();
		} );

		if ( this.currentTypeFilter.length == 0 || activity.getType() === this.currentTypeFilter ) {
			this.render();
		}
	};

	/**
	 * Check if the collection contains an activity item.
	 *
	 * Checks by ID.
	 *
	 * @param activity
	 * @returns {boolean}
	 */
	ActivityCollection.prototype.contains = function ( activity ) {

		if ( ! activity.getID() ) {
			return false;
		}

		return this.items.filter( function ( item ) {
				return item.getID() === activity.getID();
			} ).length > 0;
	};

	/**
	 * Get the latest activity item.
	 *
	 * @since 1.34
	 *
	 * @returns {Activity}
	 */
	ActivityCollection.prototype.getLatest = function () {
		return this.items[ 0 ];
	};

	/**
	 * Filter the collection to only show a subset of the items that match a given type.
	 *
	 * @since 1.34
	 *
	 * @param [type]
	 */
	ActivityCollection.prototype.filter = function ( type ) {

		type = type === undefined ? '' : type;
		this.currentTypeFilter = type;

		this.empty();

		var toRender;

		if ( type.length > 0 ) {
			toRender = this.items.filter( function ( item ) {
				return item && item.getType() === type;
			} );
		} else {
			toRender = this.items;
		}

		toRender.forEach( function ( item ) {
			this.$container.append( item.html() );
		}, this );
	};

	/**
	 * Render the collection.
	 *
	 * This will clear the stream, and re-render all items.
	 *
	 * @since 1.34
	 */
	ActivityCollection.prototype.render = function () {

		this.empty();

		if ( this.items.length > 0 ) {
			$( "#exchange-no-activity-found" ).hide();
		}

		this.items.forEach( function ( item ) {
			this.$container.append( item.html() );
		}, this );
	};

	/**
	 * Empty the collection.
	 *
	 * @since 1.34
	 */
	ActivityCollection.prototype.empty = function () {
		this.$container.empty();
	};

	/**
	 * Activity model.
	 *
	 * @since 1.34
	 *
	 * @param data
	 * @returns {{}}
	 * @constructor
	 */
	function Activity( data ) {

		this.data = data;
		this.time = new Date( data.time );
		this.actor = data.actor ? new Actor( data.actor ) : undefined;
		this.descriptionFormatted = data.description.replace( new RegExp( '\r?\n', 'g' ), '<br>' ).autoLink();
	}

	/**
	 * Get the activity item's ID.
	 *
	 * @since 1.34
	 *
	 * @returns {number}
	 */
	Activity.prototype.getID = function () {
		return Number( this.data.ID );
	};

	/**
	 * Get the description.
	 *
	 * Newlines have been replaced, and links have been auto-linked.
	 *
	 * @since 1.34
	 *
	 * @param [raw] Retrieve unmodified value. Defaults to false.
	 *
	 * @returns {string}
	 */
	Activity.prototype.getDescription = function ( raw ) {

		raw = raw === undefined ? false : raw;

		if ( raw ) {
			return this.data.description;
		} else {
			return this.descriptionFormatted;
		}
	};

	/**
	 * Get the activity type.
	 *
	 * Ex: 'note', 'renewal'
	 *
	 * @since 1.34
	 *
	 * @returns {string}
	 */
	Activity.prototype.getType = function () {
		return this.data.type;
	};

	/**
	 * Get the time this activity occurred.
	 *
	 * @since 1.34
	 *
	 * @returns {Date}
	 */
	Activity.prototype.getTime = function () {
		return this.time;
	};

	/**
	 * Check if this is a public activity item.
	 *
	 * Customers are emailed public activity.
	 *
	 * @since 1.34
	 *
	 * @returns {boolean}
	 */
	Activity.prototype.isPublic = function () {
		return Boolean( this.data.public );
	};

	/**
	 * Check if this activity has an actor.
	 *
	 * @since 1.34
	 *
	 * @returns {boolean}
	 */
	Activity.prototype.hasActor = function () {
		return this.getActor() !== undefined;
	};

	/**
	 * Get this activity's actor.
	 *
	 * @since 1.34
	 *
	 * @returns {Actor}
	 */
	Activity.prototype.getActor = function () {
		return this.actor;
	};

	/**
	 * Convert the activity to HTML.
	 *
	 * @since 1.34
	 *
	 * @returns {*}
	 */
	Activity.prototype.html = function () {
		var tpl = _.template( $( "#exchange-activity-tpl" ).html() );

		return tpl( { a: this } );
	};

	/**
	 * Actor model.
	 *
	 * @since 1.34
	 *
	 * @param data
	 * @returns {{}}
	 * @constructor
	 */
	function Actor( data ) {
		this.data = data;
		this.icon = data.icon ? new Icon( data.icon ) : undefined;
	}

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @returns {string}
	 */
	Actor.prototype.getName = function () {
		return this.data.name;
	};

	/**
	 * Get the actor's URL for more info.
	 *
	 * @since 1.34
	 *
	 * @returns {string}
	 */
	Actor.prototype.getURL = function () {
		return this.data.url;
	};

	/**
	 * Check if this actor has an icon.
	 *
	 * @since 1.34
	 *
	 * @returns {boolean}
	 */
	Actor.prototype.hasIcon = function () {
		return this.icon !== undefined;
	};

	/**
	 * Get the actor's icon.
	 *
	 * @since 1.34
	 *
	 * @returns {icon}
	 */
	Actor.prototype.getIcon = function () {
		return this.icon;
	};

	/**
	 * Convert this model to HTML.
	 *
	 * @since 1.34
	 *
	 * @returns {string}
	 */
	Actor.prototype.html = function () {

		var tpl = _.template( $( "#exchange-activity-actor-tpl" ).html() );

		return tpl( { a: this } );
	};

	/**
	 * Icon model.
	 *
	 * @since 1.34
	 *
	 * @param url
	 * @returns {{html: draw}}
	 * @constructor
	 */
	function Icon( url ) {
		this.url = url;
	}

	/**
	 * Convert this model to HTML.
	 *
	 * @since 1.34
	 *
	 * @returns {string}
	 */
	Icon.prototype.html = function () {

		var tpl = _.template( $( "#exchange-icon-tpl" ).html() );

		return tpl( { url: this.url } );
	}


})( jQuery );

/*
 Copyright (c) 2012 Bryan Woods

 autolink-js

 MIT License
 */
(function () {
	var autoLink,
		__slice = [].slice;

	autoLink = function () {
		var k, linkAttributes, option, options, pattern, v;
		options = 1 <= arguments.length ? __slice.call( arguments, 0 ) : [];

		pattern = /(^|[\s\n]|<br\/?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi;
		if ( ! (options.length > 0) ) {
			return this.replace( pattern, "$1<a href='$2'>$2</a>" );
		}
		option = options[ 0 ];
		linkAttributes = ((function () {
			var _results;
			_results = [];
			for ( k in option ) {
				v = option[ k ];
				if ( k !== 'callback' ) {
					_results.push( " " + k + "='" + v + "'" );
				}
			}
			return _results;
		})()).join( '' );
		return this.replace( pattern, function ( match, space, url ) {
			var link;
			link = (typeof option.callback === "function" ? option.callback( url ) : void 0) || ("<a href='" + url + "'" + linkAttributes + ">" + url + "</a>");
			return "" + space + link;
		} );
	};

	String.prototype[ 'autoLink' ] = autoLink;

}).call( this );
