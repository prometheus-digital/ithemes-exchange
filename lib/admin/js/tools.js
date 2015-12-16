(function ( $ ) {
	"use strict";

	$( document ).ready( function ( $ ) {

		$( ".upgrade-row button" ).click( function ( e ) {

			var $row = $( this ).closest( '.upgrade-row' );

			$row.addClass( 'upgrading' );
		} );

		$( ".upgrade-row .upgrade-progress a" ).click( function ( e ) {
			e.preventDefault();

			var $row = $( this ).closest( '.upgrade-row' );

			if ( ! $row.hasClass( 'show-feedback' ) ) {
				$row.addClass( 'show-feedback' );
				$( this ).text( 'Hide Details' );
			} else {
				$row.removeClass( 'show-feedback' );
				$( this ).text( 'Show Details' );
			}
		} );
	} );

	/**
	 * Begin an upgrade routine.
	 *
	 * @param upgradeSlug
	 * @returns {*} A promise object that will be resolved with an upgrade object.
	 */
	function beginUpgrade( upgradeSlug ) {

		var data = {
			action : 'it_exchange_begin_upgrade',
			upgrade: upgradeSlug,
			nonce  : ''
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, function ( response ) {

			if ( ! response.success ) {
				deferred.reject( response.data.message );

				return;
			}

			var currentUpgrade = Object.create( Upgrade );
			currentUpgrade.init( response.data.slug, response.data.itemCount, response.data.rate );

			deferred.resolve( currentUpgrade );
		} );

		return deferred.promise();
	}

	/**
	 * Do an upgrade step.
	 *
	 * @param {Object} upgrade Upgrade object
	 *
	 * @returns {*} A promise object that will be resolved with a step object.
	 */
	function doNextStep( upgrade ) {

		var data = {
			action : 'it_exchange_do_upgrade_step',
			upgrade: upgrade.slug,
			config : upgrade.generateConfig()
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, function ( response ) {

			if ( ! response.success ) {
				deferred.reject( response.data.message );

				return;
			}

			var step = Object.create( UpgradeStep );
			step.init( data.config.step );
			step.setNumItemsUpgraded( response.data.itemsUpgraded );

			var feedbackCollection = Object.create( FeedbackCollection );

			data.config.feedback.forEach( function ( rawFeedbackItem ) {

				var item = Object.create( FeedbackItem );
				item.init( rawFeedbackItem.message, rawFeedbackItem.type );

				feedbackCollection.addItem( item );
			} );

			step.setFeedback( feedbackCollection );

			upgrade.logStep( step );
			deferred.resolve( step );
		} );

		return deferred.promise();
	}

	/**
	 * An upgrade object.
	 */
	var Upgrade = {

		steps: [],

		/**
		 * Setup the upgrade object.
		 *
		 * @param {String} slug
		 * @param {Number} totalItemsToUpgrade
		 * @param {Number} rate
		 */
		init: function ( slug, totalItemsToUpgrade, rate ) {
			this.slug = slug;
			this.totalItemsToUpgrade = totalItemsToUpgrade;
			this.rate = rate;
		},

		/**
		 * Log an upgrade step.
		 *
		 * @param {Object} step
		 */
		logStep: function ( step ) {
			this.steps.push( step );
		},

		/**
		 * Generate a config object.
		 *
		 * @returns {{step: *, number: (Number|*), verbose: boolean}}
		 */
		generateConfig: function () {
			return {
				step   : this.steps.last().step,
				number : this.rate,
				verbose: true
			};
		},

		/**
		 * Retrieve the total number of items upgraded so far.
		 *
		 * This is an O(n) function and is not cached.
		 *
		 * @returns {Number}
		 */
		totalItemsUpgraded: function () {
			return this.steps.reduce( function ( previousValue, step ) {
				return previousValue + step.numItemsUpgraded;
			}, 0 );
		},

		/**
		 * Check if the upgrade has completed.
		 *
		 * @returns {boolean}
		 */
		completed: function () {
			return this.totalItemsToUpgrade === this.totalItemsUpgraded();
		}
	};

	/**
	 * A single upgrade step.
	 */
	var UpgradeStep = {

		/**
		 * Setup the upgrade step.
		 *
		 * @param step
		 */
		init: function ( step ) {
			this.step = step;
		},

		/**
		 * Set the number of items that were upgraded in this step.
		 *
		 * @param {Number} numItems
		 */
		setNumItemsUpgraded: function ( numItems ) {
			this.numItemsUpgraded = numItems;
		},

		/**
		 * Set the user feedback for this upgrade.
		 *
		 * @param {Object} feedbackCollection
		 */
		setFeedback: function ( feedbackCollection ) {
			this.feedback = feedbackCollection;
		}
	};

	/**
	 * Collection of feedback for a particular upgrade step.
	 */
	var FeedbackCollection = {

		items: [],

		/**
		 * Add a feedback item to the collection.
		 *
		 * @param {Object} feedbackItem
		 */
		addItem: function ( feedbackItem ) {
			this.items.push( feedbackItem )
		},

		/**
		 * Get all feedback items.
		 *
		 * @returns {Array}
		 */
		getItems: function () {
			return this.items;
		},

		/**
		 * Check if the feedback collection has at least one feedback item of a particular type.
		 *
		 * @param {String} type FeedbackItemType
		 *
		 * @returns {boolean}
		 */
		hasItemOfType: function ( type ) {
			return this.getItems().filter( function ( item ) {
					return item.type === type;
				} ).length > 0;
		}
	};

	/**
	 * Feedback Item
	 */
	var FeedbackItem = {

		/**
		 * Initialize a feedback item.
		 *
		 * @param {String} message
		 * @param {String} type
		 */
		init: function ( message, type ) {

			this.message = message;
			this.type = type ? type : FeedbackItemType.DEBUG;
		}
	};

	var FeedbackItemType = Object.freeze( {
		DEBUG  : 'debug',
		WARNING: 'warning',
		ERROR  : 'error'
	} );

	if ( ! Array.prototype.last ) {
		Array.prototype.last = function () {
			return this[ this.length - 1 ];
		};
	}

})( jQuery );