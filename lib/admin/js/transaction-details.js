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

		var $details = $( "#it-exchange-transaction-details" );
		var $activity = $( "#it-exchange-transaction-activity" );
		var $container = $( "#post-body-content" );

		/**
		 * Calculate the widths for the columns.
		 */
		function calculateWidths() {

			var minDetailsWidth = 560;
			var minActivityWidth = 120;

			var maxDetailsWidth = 760;
			var maxActivityWidth = 250;

			var containerWidth = $container.outerWidth( true );
			var activityWidth = $activity.outerWidth( true );

			var newDetailsWidth = containerWidth - activityWidth;

			// ensure we're smaller than the max width
			newDetailsWidth = Math.min( newDetailsWidth, maxDetailsWidth ) - 22;

			var newActivityWidth;

			// if the new details width is too small
			if ( newDetailsWidth < minDetailsWidth ) {
				// try to compensate by shrinking the activity width
				newActivityWidth = $activity.width() - Math.abs( newDetailsWidth - minDetailsWidth );
			} else {
				// otherwise make the activity as wide as possible
				newActivityWidth = containerWidth - newDetailsWidth;
				newActivityWidth = Math.max( Math.min( maxActivityWidth, newActivityWidth ), minActivityWidth );
			}

			// set the values if the bounds match
			if ( newDetailsWidth >= minDetailsWidth ) {
				$details.width( newDetailsWidth );
			}

			if ( newActivityWidth > minActivityWidth ) {
				$activity.width( newActivityWidth );
			}
		}

		$( window ).resize( calculateWidths );
		calculateWidths();

		var activityContainer = $( "#activity-stream" );

		var activity = new Activity( {
			description: "I think it's fine. Let's do it.",
			time       : '2016-01-02T14:11:00-05:00',
			actor      : {
				name: 'Timothy',
				icon: 'https://2.gravatar.com/avatar/596003127e013031dd5299a3879827e9?s=80&d=mm&r=g',
				url : 'www.test.com'
			},
			type       : 'note'
		} );

		var activity2 = new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			},
			type       : 'renewal'
		} );

		activityContainer.css( {
			height: $( "#it-exchange-transaction-details .inside" ).height()
		} );

		var activityCollection = new ActivityCollection( activityContainer, [
			activity,
			activity2,
			activity2,
			activity
		] );

		activityCollection.add( activity2 );

		setTimeout( function () {
			activityCollection.filter( 'note' );
			activityCollection.add( activity2 );

			setTimeout( function () {
				activityCollection.filter();
			}, 1000 );
		}, 1000 );
	} );

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

		var currentTypeFilter = '';

		items = items === undefined ? [] : items;

		var api = {

			/**
			 * Add an item to the collection.
			 *
			 * This renders the item as well.
			 *
			 * @since 1.34
			 *
			 * @param activity
			 */
			add: function ( activity ) {
				items.push( activity );

				if ( currentTypeFilter.length == 0 || activity.getType() === currentTypeFilter ) {
					$container.append( activity.html() );
				}
			},

			/**
			 * Filter the collection to only show a subset of the items that match a given type.
			 *
			 * @since 1.34
			 *
			 * @param [type]
			 */
			filter: function ( type ) {

				type = type === undefined ? '' : type;
				currentTypeFilter = type;

				api.empty();

				var toRender;

				if ( type.length > 0 ) {
					toRender = items.filter( function ( item ) {
						return item && item.getType() === type;
					} );
				} else {
					toRender = items;
				}

				toRender.forEach( function ( item ) {
					$container.append( item.html() );
				} );
			},

			/**
			 * Render the collection.
			 *
			 * This will clear the stream, and re-render all items.
			 *
			 * @since 1.34
			 */
			render: function () {

				api.empty();

				items.forEach( function ( item ) {
					$container.append( item.html() );
				} );
			},

			/**
			 * Empty the collection.
			 *
			 * @since 1.34
			 */
			empty: function () {
				$container.empty();
			}
		};

		api.render();

		return api;
	}


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

