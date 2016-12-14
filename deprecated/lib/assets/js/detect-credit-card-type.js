/**
 * Detect Credit Card Types
 *
 * This function will detect a credit card type and add a class
 * ot the chosen element.
 *
 * (Re)Written by Justin Kopepasah - kopepasah.com
 *
 * Inspired by Christian Reed's Credit Card Type Detector
 * https://github.com/christianreed/Credit-Card-Type-Detector
 */
(function ( $ ) {
	$.fn.it_exchange_detect_credit_card_type = function ( options ) {

		var settings = $.extend( {
				'element'     : '.card-type',
				'class_prefix': 'card'
			}, options ),

			// The element that we should add the classes to.
			element = settings.element,

			// Set the class prefix for the cards.
			class_prefix = settings.class_prefix;

		return this.each( function () {
			// On keyup, check which class to add (if any)
			$( this ).keyup( function () {
				var current_value = $( this ).val();

				var classes = $( element ).attr( 'class' ).split( /\s+/ );

				// If we have less than two numbers, remove the classses. Otherwise let's run the check.
				if ( current_value.length < 2 ) {
					$.each( classes, function ( index, value ) {
						if ( value.match( class_prefix, 'g' ) ) {
							$( element ).removeClass( value );
						}
					} );
				} else {

					var type = jQuery.payment.cardType( current_value );

					if ( type ) {
						$( element ).addClass( class_prefix + '-' + type );
					}
				}
			} );
		} );
	};
})( jQuery );
