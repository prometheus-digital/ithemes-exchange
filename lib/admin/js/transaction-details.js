(function ( $, api, Common, Config ) {
	"use strict";

	$( document ).ready( function () {

		var transaction = new api.Models.Transaction( Config.transaction, {
			collection: new api.Collections.Transactions(),
			context   : 'edit',
		} );

		$( '.handlediv, .hndle' ).remove();

		$( '.tip, .it-exchange-tip' ).tooltip();

		// do transaction status update
		$( '#it-exchange-update-transaction-status' ).on( 'change', function () {
			var $select = $( this );
			var $selected = $( '#it-exchange-update-transaction-status' ).find( ":selected" );
			var currentStatusLabel = transaction.get( 'status.label' );

			transaction.save( { 'status.slug': $selected.val() }, {
				success: function () {

					var newStatusLabel = transaction.get( 'status.label' );

					toastr.success( Config.statusChangeSuccess.replace( '%1$s', currentStatusLabel ).replace( '%2$s', newStatusLabel ) );
					updateStatusLabel( newStatusLabel );

					$( '#it-exchange-update-transaction-current-status' ).val( $selected.val() );
					$select.val( 0 ).removeAttr( 'disabled' );

					// Increase hearbeat interval to check every 5 seconds for 30 seconds.
					// This will allow us to pull in activity items from changing the transaction status.
					wp.heartbeat.interval( 'fast', 6 );
				},

				error: function () {
					toastr.error( Config.statusChangeError );
					$select.val( 0 ).removeAttr( 'disabled' );
				},
			} );
		} );

		$( "#open-receipt-manager" ).click( function ( e ) {
			e.preventDefault();

			var $actionsContainer = $( ".transaction-actions" ),
				$manager = $( "#receipt-manager" );

			$actionsContainer.slideUp();
			$manager.slideDown();
		} );

		$( "#send-receipt" ).click( function ( e ) {
			e.preventDefault();

			var $this = $( this ),
				$actionsContainer = $( ".transaction-actions" ),
				$manager = $( "#receipt-manager" ),
				$email = $( "#receipt-email" );

			$this.attr( 'disabled', true );

			transaction.send_receipt( $email.val() ).always( function () {
				$this.removeAttr( 'disabled' );
			} ).done( function () {
				$manager.slideUp();
				$actionsContainer.slideDown();
				$email.val( '' );
				toastr.success( Config.receiptSuccess )
			} ).fail( function () {
				toastr.error( Config.receiptFailed );
			} );
		} );

		$( "#cancel-receipt" ).click( function ( e ) {

			e.preventDefault();

			var $actionsContainer = $( ".transaction-actions" ),
				$manager = $( "#receipt-manager" );

			$actionsContainer.slideDown();
			$manager.slideUp();
		} );

		$( "#open-refund-manager" ).click( function ( e ) {
			e.preventDefault();

			var $actionsContainer = $( ".transaction-actions" ),
				$refundManager = $( "#refund-manager" );

			$actionsContainer.slideUp();
			$refundManager.slideDown();
		} );

		$( "#add-refund" ).click( function ( e ) {
			e.preventDefault();

			var $this = $( this ),
				$actionsContainer = $( ".transaction-actions" ),
				$refundManager = $( "#refund-manager" ),
				$refund = $( "#refund-amount" );

			$this.attr( 'disabled', true );

			var refund = transaction.refunds().create( { amount: Common.getFloatFromString( $refund.val() ) }, {
				success: function () {
					$refund.val( '' );

					$refundManager.slideUp();
					$actionsContainer.slideDown();
					$this.removeAttr( 'disabled' );

					var refundLabel = Config.refundLabel;
					refundLabel = refundLabel.replace( '%1$s', refund.amountFormatted() );
					refundLabel = refundLabel.replace( '%2$s', Common.formatDate( refund.get( 'created_at' ), true ) );

					$( '.transaction-refunds-list' ).append(
						'<div class="transaction-costs-refund"><span class="code">' + refundLabel + '</span></div>'
					);

					transaction.fetch().done( function () {

						var total = Common.formatPrice( transaction.refunds().total() );
						$( '.transaction-costs-refund-total-amount span' ).text( total );
						$( '.payment-total-amount' ).text( transaction.totalFormatted() );

						$( '.transaction-refunds-container' ).show();
						$( '.payment-original-total-label, .payment-original-total-amount' ).show();
					} );

					// Increase hearbeat interval to check every 5 seconds for 30 seconds.
					// This will allow us to pull in activity items from creating a refund much quicker.
					wp.heartbeat.interval( 'fast', 6 );
				},

				error: function ( xhr ) {
					console.log( xhr );
					$this.removeAttr( 'disabled' );
					alert( Common.getErrorFromXhr( xhr ) );
				},
			} );
		} );

		$( "#cancel-refund" ).click( function ( e ) {

			e.preventDefault();

			var $actionsContainer = $( ".transaction-actions" ),
				$refundManager = $( "#refund-manager" );

			$actionsContainer.slideDown();
			$refundManager.slideUp();
		} );

		// Format refund input.
		$( '#refund-amount' ).on( 'focusout', function () {

			var $this = $( this ),
				newVal = $this.val(),
				maxVal = $this.data( 'max' ),
				symbol = $this.data( 'symbol' ),
				position = $this.data( 'symbol-position' ),
				thousands = $this.data( 'thousands-separator' ),
				decimals = $this.data( 'decimals-separator' );

			if ( newVal > maxVal ) {
				newVal = maxVal;
			}

			$this.data( 'amount', newVal );
			$this.val( Common.formatPrice( newVal ) );
		} );

		/**
		 * Update the status label.
		 *
		 * @since 2.0.0
		 *
		 * @param statusLabel
		 */
		function updateStatusLabel( statusLabel ) {
			var $status = $( '.transaction-status' );

			$status.removeClass( $status.text().toLowerCase() )
				.addClass( statusLabel.toLowerCase() )
				.text( statusLabel );
		}

		var activityMetaBox = $( "#it-exchange-transaction-activity" );

		var $activityStreamHeader = $( ".exchange-activity-stream-header" );
		var $filterContainer = $( ".exchange-filter-action-container" );

		var activityContainer = $( "#activity-stream" );

		var $noteWritingContainer = $( ".exchange-note-writing-container" );
		var $noteEditor = $( "#exchange-note-editor" );
		var $notePublic = $( "#exchange-notify-customer" );

		activityMetaBox.on( 'scroll', function () {

			if ( $( this ).scrollTop() > 5 ) {
				$activityStreamHeader.addClass( 'exchange-shadowed' );
			} else {
				$activityStreamHeader.removeClass( 'exchange-shadowed' );
			}

		} );

		activityMetaBox.height( $( '.inside', '#it-exchange-transaction-details' ).height() );

		var activityCollection = new ActivityCollection( activityContainer, Config.items.map( function ( item ) {
			return new Activity( item );
		} ) );

		/**
		 * When the activity filter is updated, filter the collection.
		 *
		 * @since 1.34
		 */
		$( "#exchange-activity-filter" ).change( function () {
			activityCollection.filter( $( this ).val() );
		} );

		/**
		 * When the Add Note button is clicked, reveal the note writing container.
		 *
		 * @since 1.34
		 */
		$( "#exchange-add-note" ).click( function ( e ) {

			e.preventDefault();

			$activityStreamHeader.addClass( 'exchange-shadowed' );
			$noteWritingContainer.show();
			$filterContainer.hide();
		} );

		/**
		 * When the Post Note button is clicked, create the note on the server and add it to the collection.
		 *
		 * @since 1.34
		 */
		$( "#exchange-post-note" ).click( function ( e ) {

			e.preventDefault();

			if ( !$noteEditor.val().length ) {
				closeNoteEditor();

				return;
			}

			var $this = $( this );
			$this.prop( 'disabled', true );

			createNote( $noteEditor.val(), $notePublic.is( ':checked' ), activityCollection ).fail( function ( message ) {
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

		/**
		 * When a new note will be public, add a new-note-public class to the editor.
		 *
		 * This is used to color the background of the note green.
		 *
		 * @since 1.34
		 */
		$notePublic.change( function () {
			if ( $notePublic.is( ':checked' ) ) {
				$noteEditor.addClass( 'exchange-new-note-public' );
			} else {
				$noteEditor.removeClass( 'exchange-new-note-public' );
			}
		} );

		/**
		 * Hide the note composer when a user hits esc
		 *
		 * @since 1.34
		 */
		$( document ).keyup( function ( e ) {
			if ( e.keyCode == 27 ) {
				closeNoteEditor();
			}
		} );

		$( "#exchange-close-note" ).on( 'click', function ( e ) {
			e.preventDefault();
			closeNoteEditor();
		} );

		/**
		 * Auto-post note when a user hits cmd-enter
		 *
		 * @since 1.34
		 */
		$noteEditor.keydown( function ( e ) {
			if ( ( e.metaKey || e.ctrlKey ) && e.keyCode == 13 ) {
				$( "#exchange-post-note" ).click();
			}
		} );

		/**
		 * When a heartbeat is received, add any new activity items to the collection.
		 *
		 * @since 1.34
		 */
		$( document ).on( 'heartbeat-tick.it-exchange-txn-activity', function ( e, data ) {

			if ( data.hasOwnProperty( 'it-exchange-txn-activity' ) ) {

				if ( data['it-exchange-txn-activity']['items'] ) {
					data['it-exchange-txn-activity']['items'].forEach( function ( activity ) {
						activityCollection.add( new Activity( activity ) );
					} );
				}

				updateStatusLabel( data['it-exchange-txn-activity']['status']['label'] );
			}

			enqueueHeartbeat();
		} );

		enqueueHeartbeat();

		/**
		 * Enqueue the heartbeat to get latest activity items.
		 *
		 * @since 1.34
		 */
		function enqueueHeartbeat() {

			var latest = activityCollection.getLatest();

			wp.heartbeat.enqueue( 'it-exchange-txn-activity', {
				txn   : Config.txn,
				latest: latest ? latest.getTime().toISOString() : false
			} );
		}

		/**
		 * Close the note editor.
		 *
		 * @since 1.34
		 */
		function closeNoteEditor() {
			$filterContainer.show();
			$noteWritingContainer.hide();
			activityMetaBox.trigger( 'scroll' );
		}
	} );

	/**
	 * Create a new note.
	 *
	 * @since 1.34
	 *
	 * @param note
	 * @param isPublic
	 * @param collection
	 * @returns {*} Promise that resolves to an activity.
	 */
	function createNote( note, isPublic, collection ) {

		var data = {
			action  : 'it-exchange-add-note',
			note    : note,
			isPublic: Number( isPublic ),
			nonce   : Config.nonce,
			txn     : Config.txn
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, function ( response ) {

			if ( !response.success ) {
				deferred.reject( response.data.message );

				return;
			}

			deferred.resolve( new Activity( response.data.activity, collection ) );
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
		this.isCalendarTime = true;
		this.$el = $container;
		this.items = items === undefined ? [] : items;

		this.items.forEach( function ( activity ) {
			activity.collection = this;
		}, this );

		this.items.sort( function ( a, b ) {
			return b.getTime() - a.getTime();
		} );

		this.render();

		var updateTimeDisplay = (function () {

			// We only want to run this once every hour.
			if ( new Date().getMinutes() !== 0 && false ) {
				return;
			}

			this.items.forEach( function ( activity ) {
				activity.updateTimeDisplay();
			} );
		}).bind( this );

		var timeInterval = setInterval( updateTimeDisplay, 60000 );

		ifvisible.on( 'blur', function () {
			clearInterval( timeInterval );
		} );

		ifvisible.on( 'focus', function () {
			timeInterval = setInterval( updateTimeDisplay, 60000 );
		} );
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
	 * Remove an activity item from the collection.
	 *
	 * @since 1.34
	 *
	 * @param activity Activity object or ID.
	 *
	 * @return {boolean}
	 */
	ActivityCollection.prototype.remove = function ( activity ) {

		var id = activity instanceof Activity ? activity.getID() : activity;

		var idx = this.items.findIndex( function ( element ) {
			return element.getID() == id;
		} );

		if ( idx == -1 ) {
			return false;
		}

		this.items.splice( idx, 1 );

		this.render();

		return true;
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

		if ( !activity.getID() ) {
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
		return this.items[0];
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
			this.$el.append( item.$el );
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
			this.$el.append( item.$el );
		}, this );
	};

	/**
	 * Empty the collection.
	 *
	 * @since 1.34
	 */
	ActivityCollection.prototype.empty = function () {
		this.items.forEach( function ( item ) {
			item.$el.detach();
		} );
	};

	/**
	 * Activity model.
	 *
	 * @since 1.34
	 *
	 * @param data
	 * @param [collection]
	 * @returns {{}}
	 * @constructor
	 */
	function Activity( data, collection ) {

		this.collection = collection;
		this.data = data;
		this.time = new Date( data.time );
		this.actor = data.actor ? new Actor( data.actor ) : undefined;
		this.descriptionFormatted = data.description.replace( new RegExp( '\r?\n', 'g' ), '<br>' ).autoLink();

		this.$el = $( this.html() );

		$( '.exchange-delete-activity', this.$el ).click( (function ( scope ) {
			return function ( e ) {

				e.preventDefault();

				scope.delete().fail( function ( message ) {
					alert( message );
				} ).done( function () {
					scope.collection.remove( scope );
				} );
			}
		})( this ) );

		$( 'header', this.$el ).click( ( function () {

			this.collection.isCalendarTime = !this.collection.isCalendarTime;

			this.collection.items.forEach( function ( activity ) {
				activity.updateTimeDisplay();
			} );

		}).bind( this ) );
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
	 * Delete the activity item on the server.
	 *
	 * @since 1.34
	 *
	 * @returns {*} Promise that resolves with 'this'
	 */
	Activity.prototype.delete = function () {

		var data = {
			action: 'it-exchange-remove-activity',
			ID    : this.getID(),
			nonce : Config.nonce,
			txn   : Config.txn
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, (function ( scope ) {
			return function ( response ) {
				if ( !response.success ) {

					if ( response.data.message ) {
						deferred.reject( response.data.message );
					} else {
						deferred.reject( 'An unexpected error occurred.' );
					}
				} else {
					scope.$el.remove();
					deferred.resolve( scope )
				}
			};
		})( this ) );

		return deferred.promise();
	};

	/**
	 * Update the time being displayed.
	 *
	 * Moment does things like display time as yesterday or last hour, so we periodically
	 * update the time labels to keep those making sense.
	 *
	 * @since 2.0.0
	 */
	Activity.prototype.updateTimeDisplay = function () {
		var $el = $( 'header', this.$el );

		if ( this.collection && this.collection.isCalendarTime ) {
			$el.text( moment( this.getTime() ).calendar() );
		} else {
			$el.text( moment( this.getTime() ).format( Config.format ) );
		}
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

		this.$el = $( this.html() );
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
	};
})( jQuery, ITExchangeAPI, ExchangeCommon, EXCHANGE );

if ( !Array.prototype.findIndex ) {
	Array.prototype.findIndex = function ( predicate ) {
		if ( this === null ) {
			throw new TypeError( 'Array.prototype.findIndex called on null or undefined' );
		}
		if ( typeof predicate !== 'function' ) {
			throw new TypeError( 'predicate must be a function' );
		}
		var list = Object( this );
		var length = list.length >>> 0;
		var thisArg = arguments[1];
		var value;

		for ( var i = 0; i < length; i++ ) {
			value = list[i];
			if ( predicate.call( thisArg, value, i, list ) ) {
				return i;
			}
		}
		return -1;
	};
}

if ( !Function.prototype.bind ) {
	Function.prototype.bind = function ( oThis ) {
		if ( typeof this !== 'function' ) {
			// closest thing possible to the ECMAScript 5
			// internal IsCallable function
			throw new TypeError( 'Function.prototype.bind - what is trying to be bound is not callable' );
		}

		var aArgs = Array.prototype.slice.call( arguments, 1 ),
			fToBind = this,
			fNOP = function () {
			},
			fBound = function () {
				return fToBind.apply( this instanceof fNOP
						? this
						: oThis,
					aArgs.concat( Array.prototype.slice.call( arguments ) ) );
			};

		if ( this.prototype ) {
			// native functions don't have a prototype
			fNOP.prototype = this.prototype;
		}
		fBound.prototype = new fNOP();

		return fBound;
	};
}

// Production steps of ECMA-262, Edition 5, 15.4.4.18
// Reference: http://es5.github.io/#x15.4.4.18
if ( !Array.prototype.forEach ) {

	Array.prototype.forEach = function ( callback, thisArg ) {

		var T, k;

		if ( this == null ) {
			throw new TypeError( ' this is null or not defined' );
		}

		// 1. Let O be the result of calling ToObject passing the |this| value as the argument.
		var O = Object( this );

		// 2. Let lenValue be the result of calling the Get internal method of O with the argument "length".
		// 3. Let len be ToUint32(lenValue).
		var len = O.length >>> 0;

		// 4. If IsCallable(callback) is false, throw a TypeError exception.
		// See: http://es5.github.com/#x9.11
		if ( typeof callback !== "function" ) {
			throw new TypeError( callback + ' is not a function' );
		}

		// 5. If thisArg was supplied, let T be thisArg; else let T be undefined.
		if ( arguments.length > 1 ) {
			T = thisArg;
		}

		// 6. Let k be 0
		k = 0;

		// 7. Repeat, while k < len
		while ( k < len ) {

			var kValue;

			// a. Let Pk be ToString(k).
			//   This is implicit for LHS operands of the in operator
			// b. Let kPresent be the result of calling the HasProperty internal method of O with argument Pk.
			//   This step can be combined with c
			// c. If kPresent is true, then
			if ( k in O ) {

				// i. Let kValue be the result of calling the Get internal method of O with argument Pk.
				kValue = O[k];

				// ii. Call the Call internal method of callback with T as the this value and
				// argument list containing kValue, k, and O.
				callback.call( T, kValue, k, O );
			}
			// d. Increase k by 1.
			k++;
		}
		// 8. return undefined
	};
}

if ( typeof Object.create != 'function' ) {
	Object.create = (function () {
		var Temp = function () {
		};
		return function ( prototype ) {
			if ( arguments.length > 1 ) {
				throw Error( 'Second argument not supported' );
			}
			if ( typeof prototype != 'object' ) {
				throw TypeError( 'Argument must be an object' );
			}
			Temp.prototype = prototype;
			var result = new Temp();
			Temp.prototype = null;
			return result;
		};
	})();
}

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
		if ( !(options.length > 0) ) {
			return this.replace( pattern, "$1<a href='$2'>$2</a>" );
		}
		option = options[0];
		linkAttributes = ((function () {
			var _results;
			_results = [];
			for ( k in option ) {
				v = option[k];
				if ( k !== 'callback' ) {
					_results.push( " " + k + "='" + v + "'" );
				}
			}
			return _results;
		})()).join( '' );
		return this.replace( pattern, function ( match, space, url ) {
			var link;
			link = (typeof option.callback === "function" ? option.callback( url ) : void 0)
				|| ("<a href='" + url + "'" + linkAttributes + ">" + url + "</a>");
			return "" + space + link;
		} );
	};

	String.prototype['autoLink'] = autoLink;

}).call( this );
