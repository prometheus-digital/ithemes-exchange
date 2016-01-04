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
				icon: 'https://2.gravatar.com/avatar/596003127e013031dd5299a3879827e9?s=80&d=mm&r=g'
			}
		} );

		var activity2 = new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} );

		activityContainer.css( {
			height: $( "#it-exchange-transaction-details .inside" ).height()
		} );

		activityContainer.append( activity.html() );
		activityContainer.append( activity2.html() );

		activityContainer.append( new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} ).html() );

		activityContainer.append( new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} ).html() );

		activityContainer.append( new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} ).html() );

		activityContainer.append( new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} ).html() );

		activityContainer.append( new Activity( {
			description: "I don't think we want to start doing this. What do you think Timothy?",
			time       : '2016-01-03T17:29:00-05:00',
			actor      : {
				name: 'Robert',
				icon: 'https://s.gravatar.com/avatar/6c810d6cc81af85bb09e6fee99847787?s=80'
			}
		} ).html() );
	} );


	function Activity( data ) {

		var time = new Date( data.time );

		var actor = data.actor ? new Actor( data.actor ) : undefined;

		return {

			getID: function () {
				return data.ID
			},

			getDescription: function () {
				return data.description;
			},

			getType: function () {
				return data.type
			},

			getTime: function () {
				return time;
			},

			moment: function ( format ) {
				return moment( this.getTime() ).calendar()
			},

			isPublic: function () {
				return data.public
			},

			hasActor: function () {
				return this.getActor() !== undefined;
			},

			getActor: function () {
				return actor;
			},

			html: function () {

				var tpl = _.template( $( "#exchange-activity-tpl" ).html() );

				return tpl( { a: this } );
			}

		};
	}

	function Actor( data ) {

		var icon = data.icon ? new Icon( data.icon ) : undefined;

		return {

			getName: function () {
				return data.name;
			},

			getNameHTML: function () {

				if ( data.url ) {
					return '<a href="' + data.url + '">' + this.getName() + '</a>';
				} else {
					return this.getName();
				}
			},

			hasIcon: function () {
				return icon !== undefined;
			},

			getIcon: function () {
				return icon;
			},

			html: function () {
				var tpl = _.template( $( "#exchange-activity-actor-tpl" ).html() );

				return tpl( { a: this } );
			}
		};
	}

	function Icon( url ) {
		return {
			html: function () {
				var tpl = _.template( $( "#exchange-icon-tpl" ).html() );

				return tpl( { url: url } );
			}
		};
	}


})( jQuery );

