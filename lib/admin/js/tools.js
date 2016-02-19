(function ( $ ) {
	"use strict";

	$( document ).ready( function ( $ ) {

		/**
		 * Fires when the upgrade button is clicked.
		 *
		 * This adds the upgrading class to display the progress bar,
		 * and view details link.
		 *
		 * An AJAX request is fired to begin the upgrade process.
		 */
		$( ".upgrade-row button" ).click( function ( e ) {

			var $row = $( this ).closest( '.upgrade-row' );

			$row.addClass( 'upgrading' );
			$row.removeClass( 'in-progress' );

			beginUpgrade( $row.data( 'upgrade' ) ).fail( function ( message ) {
				alert( message );

				$row.removeClass( 'upgrading' );
			} ).done( function ( upgrade ) {

				var $progress = $( 'progress', $row );
				$progress.attr( 'max', upgrade.totalItemsToUpgrade );

				var feedback = Object.create( FeedbackView );
				feedback.init( $( 'textarea', $row ) );
				feedback.clear();

				feedback.heading( 'Beginning Upgrade', 2, false );
				feedback.addLabeledLine( 'Slug', upgrade.slug );
				feedback.addLabeledLine( 'Items', upgrade.totalItemsToUpgrade );
				feedback.addLabeledLine( 'Rate', upgrade.rate );

				initializeLoop( upgrade, $progress, feedback );
			} );
		} );

		/**
		 * Toggles the visbility of the upgrade details textbox.
		 */
		$( ".upgrade-row .upgrade-progress a" ).click( function ( e ) {
			e.preventDefault();

			var $row = $( this ).closest( '.upgrade-row' );

			if ( ! $row.hasClass( 'show-feedback' ) ) {
				$row.addClass( 'show-feedback' );
				$( this ).text( EXCHANGE.hideDetails );
			} else {
				$row.removeClass( 'show-feedback' );
				$( this ).text( EXCHANGE.viewDetails );
			}
		} );

		/**
		 * When any upgrade is completed, add the completed class and remove the in-progress class.
		 */
		$( document ).on( 'it-exchange.upgrade-completed', function ( event, upgrade ) {
			var $row = $( 'div[data-upgrade="' + upgrade.slug + '"]' );
			$row.addClass( 'completed' );
			$row.removeClass( 'in-progress' );
		} );

		/**
		 * When an upgrade step is completed, check if the program encountered an error.
		 *
		 * If so, stylize the upgrade row.
		 */
		$( document ).on( 'it-exchange.upgrade-step-completed', function ( event, step, upgrade ) {

			if ( step.feedback.hasItemOfType( FeedbackItemType.ERROR ) ) {

				var $row = $( '[data-upgrade="' + upgrade.slug + '"]' );
				$row.addClass( 'erred show-feedback' );
			}
		} );
	} );

	/**
	 * Initialize the upgrade loop.
	 *
	 * This will continue for as long as the upgrade is not completed or halted.
	 *
	 * @param {Object} upgrade
	 * @param {*} $progress
	 * @param {Object} feedback
	 */
	function initializeLoop( upgrade, $progress, feedback ) {

		$( upgrade ).on( 'it-exchange.step-completed', function ( event, step ) {

			if ( ! upgrade.completed() && ! upgrade.halted ) {
				doNextStep( upgrade ).fail( function ( message ) {
					alert( message );

					upgrade.halt();

					handleUpgradeException( upgrade, feedback, message );

				} ).done( function ( step ) {

					$progress.val( upgrade.totalItemsUpgraded() );

					feedback.heading( 'Step ' + step.step );
					step.feedback.getItems().forEach( function ( item ) {
						feedback.addLine( item.toText() );
					} );

					$( upgrade ).trigger( 'it-exchange.step-completed', step );
					$( document ).trigger( 'it-exchange.upgrade-step-completed', [ step, upgrade ] );
				} );
			} else if ( upgrade.completed() ) {

				var executionTime = new Date().getTime() - upgrade.start;
				executionTime = Math.floor( executionTime / 1000 );

				feedback.heading( 'Upgrade Completed', 2 );
				feedback.addLabeledLine( 'Time', executionTime + 's' );

				completeUpgrade( upgrade ).fail( function ( message ) {
					alert( message );

					handleUpgradeException( upgrade, feedback, message );
				} ).done( function () {
					$progress.val( 100 );
					$( upgrade ).trigger( 'it-exchange.completed' );
					$( document ).trigger( 'it-exchange.upgrade-completed', upgrade );
				} );
			}
		} );

		$( upgrade ).trigger( 'it-exchange.step-completed' );
	}

	/**
	 * Begin an upgrade routine.
	 *
	 * @param upgradeSlug
	 * @returns {*} A promise object that will be resolved with an upgrade object.
	 */
	function beginUpgrade( upgradeSlug ) {

		var data = {
			action : 'it-exchange-begin-upgrade',
			upgrade: upgradeSlug,
			nonce  : EXCHANGE.nonce
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
			action : 'it-exchange-do-upgrade-step',
			upgrade: upgrade.slug,
			config : upgrade.generateConfig(),
			nonce  : EXCHANGE.nonce
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
			feedbackCollection.init();

			response.data.feedback.forEach( function ( rawFeedbackItem ) {

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
	 * Complete an upgrade.
	 *
	 * @param {Object} upgrade
	 *
	 * @return {*} Promise
	 */
	function completeUpgrade( upgrade ) {

		var data = {
			action : 'it-exchange-complete-upgrade',
			upgrade: upgrade.slug,
			nonce  : EXCHANGE.nonce
		};

		var deferred = $.Deferred();

		$.post( ajaxurl, data, function ( response ) {

			if ( ! response.success ) {
				deferred.reject( response.data.message );

				return;
			}

			deferred.resolve();
		} );

		return deferred.promise();
	}

	/**
	 * Handle the UI changes for when an upgrade exception is thrown.
	 *
	 * @since 1.35.2
	 *
	 * @param upgrade
	 * @param feedback
	 * @param message
	 */
	function handleUpgradeException( upgrade, feedback, message ) {

		var $row = $( '[data-upgrade="' + upgrade.slug + '"]' );
		$row.addClass( 'erred show-feedback' );

		feedback.heading( "Fatal Error", 1 );
		feedback.addLine( message );
	}

	/**
	 * Feedback view.
	 */
	var FeedbackView = {

		/**
		 * Initialize the feedback view.
		 *
		 * @param {*} $elem Input container. Typically a textarea.
		 */
		init: function ( $elem ) {
			this.$elem = $elem;
		},

		/**
		 * Write a heading.
		 *
		 * @param {String} heading Text
		 * @param {Number} [level]   Number of #'s. Lower numbers mean higher priority.
		 * @param {Boolean} [addSpacing]
		 */
		heading: function ( heading, level, addSpacing ) {

			level = level ? level : 3;
			addSpacing = addSpacing === undefined ? true : addSpacing;

			var out, hashes = '';

			for ( var i = 0; i < level; i ++ ) {
				hashes += '#';
			}

			out = hashes + ' ' + heading + ' ' + hashes;

			if ( addSpacing ) {
				out = '\r\n\r\n' + out;
			}

			this.write( out + '\r\n' );
		},

		/**
		 * Add a line.
		 *
		 * This will be prefixed with a list indicator.
		 *
		 * @param {String} content
		 */
		addLine: function ( content ) {

			if ( content.length ) {
				content = '- ' + content;
			}

			this.write( content + '\r\n' )
		},

		/**
		 * Add a labelled line.
		 *
		 * @param {String} label
		 * @param {String} content
		 */
		addLabeledLine: function ( label, content ) {
			this.write( label + ': ' + content + '\r\n' );
		},

		/**
		 * Write content to the textarea.
		 *
		 * @param {String} content
		 */
		write: function ( content ) {
			this.$elem.val( this.$elem.val() + content );
			this.scrollToBottom();
		},

		/**
		 * Scroll the textarea to the bottom.
		 */
		scrollToBottom: function () {
			this.$elem.scrollTop( this.$elem[ 0 ].scrollHeight );
		},

		/**
		 * Clear the textarea.
		 */
		clear: function () {
			this.$elem.val( '' );
		}
	};

	/**
	 * An upgrade object.
	 */
	var Upgrade = {

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
			this.steps = [];
			this.halted = false;
			this.start = new Date();
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

			var latestStep = this.steps.last();

			return {
				step   : latestStep ? latestStep.step + 1 : 1,
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

			var lastStep = this.steps.last();

			if ( lastStep && lastStep.numItemsUpgraded == 0 ) {
				return true;
			}

			return this.totalItemsToUpgrade <= this.totalItemsUpgraded();
		},

		/**
		 * Halt the upgrade routine.
		 */
		halt: function () {
			this.halted = true;
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

		init: function () {
			this.items = [];
		},

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
					return item && item.type === type;
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
		},

		/**
		 * Output a textual representation of the feedback item.
		 *
		 * @returns {string}
		 */
		toText: function () {

			var out = '';

			switch ( this.type ) {
				case FeedbackItemType.ERROR:
					out += 'ERROR: ';
					break;
				case FeedbackItemType.WARNING:
					out += 'Warning: ';
					break;
			}

			return out + this.message;
		}
	};

	/**
	 * Feedback item types.
	 *
	 * @type {Object}
	 */
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