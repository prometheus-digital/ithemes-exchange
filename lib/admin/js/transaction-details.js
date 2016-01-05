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
				$notePublic.prop( 'checked', false );
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

		var time = new Date( data.time );

		var actor = data.actor ? new Actor( data.actor ) : undefined;

		return {

			/**
			 * Get the activity item's ID.
			 *
			 * @since 1.34
			 *
			 * @returns {number}
			 */
			getID: function () {
				return Number( data.ID );
			},

			/**
			 * Get the activity description.
			 *
			 * @since 1.34
			 *
			 * @returns {string}
			 */
			getDescription: function () {
				return data.description;
			},

			/**
			 * Get the activity type.
			 *
			 * @since 1.34
			 *
			 * @returns {*}
			 */
			getType: function () {
				return data.type
			},

			/**
			 * Get the time this activity was performed.
			 *
			 * @since 1.34
			 *
			 * @returns {Date}
			 */
			getTime: function () {
				return time;
			},

			/**
			 * Is this activity public.
			 *
			 * @since 1.34
			 *
			 * @returns {boolean}
			 */
			isPublic: function () {
				return Boolean( data.public );
			},

			/**
			 * Does this activity have an actor.
			 *
			 * @since 1.34
			 *
			 * @returns {boolean}
			 */
			hasActor: function () {
				return this.getActor() !== undefined;
			},

			/**
			 * Get this item's actor.
			 *
			 * @since 1.34
			 *
			 * @returns {Actor}
			 */
			getActor: function () {
				return actor;
			},

			/**
			 * Convert this model to HTML.
			 *
			 * @returns {*}
			 */
			html: function () {

				var tpl = _.template( $( "#exchange-activity-tpl" ).html() );

				return tpl( { a: this } );
			}

		};
	}

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

		var icon = data.icon ? new Icon( data.icon ) : undefined;

		return {

			/**
			 * Get the actor's name.
			 *
			 * @since 1.34
			 *
			 * @returns {string}
			 */
			getName: function () {
				return data.name;
			},

			/**
			 * Get the actor's URL for more info.
			 *
			 * @since 1.34
			 *
			 * @returns {*}
			 */
			getURL: function () {
				return data.url
			},

			/**
			 * Check if this actor has an icon.
			 *
			 * @since 1.34
			 *
			 * @returns {boolean}
			 */
			hasIcon: function () {
				return icon !== undefined;
			},

			/**
			 * Get the actor's icon.
			 *
			 * @since 1.34
			 *
			 * @returns {icon}
			 */
			getIcon: function () {
				return icon;
			},

			/**
			 * Convert this model to HTML.
			 *
			 * @since 1.34
			 *
			 * @returns {*}
			 */
			html: function () {
				var tpl = _.template( $( "#exchange-activity-actor-tpl" ).html() );

				return tpl( { a: this } );
			}
		};
	}

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
		return {

			/**
			 * Convert this model to HTML.
			 *
			 * @since 1.34
			 *
			 * @returns {*}
			 */
			html: function () {
				var tpl = _.template( $( "#exchange-icon-tpl" ).html() );

				return tpl( { url: url } );
			}
		};
	}


})( jQuery );

