(function( $ ) {
	$.fn.it_exchange_detect_credit_card_type = function( options ) {
		var settings = $.extend({
				'element' : '.card-type'
			}, options ),
			
			// The element that we should add the classes to.
			element = $( settings.element ),
			
			// Visa
			visa_regex = new RegExp('^4[0-9]{0,15}$'),
			
			// MasterCard
			mastercard_regex = new RegExp('^5$|^5[1-5][0-9]{0,14}$'),
			
			// American Express
			amex_regex = new RegExp('^3$|^3[47][0-9]{0,13}$'),
			
			// Diners Club
			diners_regex = new RegExp('^3$|^3[068]$|^3(?:0[0-5]|[68][0-9])[0-9]{0,11}$'),
			
			// Discover
			discover_regex = new RegExp('^6$|^6[05]$|^601[1]?$|^65[0-9][0-9]?$|^6(?:011|5[0-9]{2})[0-9]{0,12}$'),
			
			// JCB
			jcb_regex = new RegExp('^2[1]?$|^21[3]?$|^1[8]?$|^18[0]?$|^(?:2131|1800)[0-9]{0,11}$|^3[5]?$|^35[0-9]{0,14}$');
			
		return this.each( function() {
			// On keyup, check which class to add (if any)
			$( this ).keyup( function() {
				var current_value = $( this ).val();
				
				// Remove empty spaces and dashes.
				current_value = current_value.replace(/ /g,'').replace(/-/g,'');
				
				if ( current_value.match( visa_regex ) ) {
					$( element ).addClass( 'card-visa' );
				} else {
					$( element ).removeClass( 'card-visa' );
				}
				
				if ( current_value.match( mastercard_regex ) ) {
					$( element ).addClass( 'card-mastercard' );
				} else {
					$( element ).removeClass( 'card-mastercard' );
				}
				
				if ( current_value.match( amex_regex ) ) {
					$( element ).addClass( 'card-amex' );
				} else {
					$( element ).removeClass( 'card-amex' );
				}
				
				if ( current_value.match( diners_regex ) ) {
					$( element).addClass( 'card-diners' );
				} else {
					$( element ).removeClass( 'card-diners' );
				}
				
				if ( current_value.match(discover_regex) ) {
					$( element ).addClass( 'card-discover' );
				} else {
					$( element ).removeClass( 'card-discover' );
				}

				if ( current_value.match( jcb_regex ) ) {
					$( element ).addClass( 'card-jcb' );
				} else {
					$( element ).removeClass( 'card-jcb' );
				}

				// if nothing is a hit we add a class to fade them all out
				if ( current_value != '' && ! current_value.match( visa_regex ) && ! current_value.match( mastercard_regex ) && ! current_value.match( amex_regex ) && ! current_value.match( diners_regex ) && ! current_value.match( discover_regex ) && ! current_value.match( jcb_regex ) ) {
					$( element ).addClass( 'card-nothing' );
				} else {
					$( element ).removeClass( 'card-nothing' );
				}
			});
		});
	};
})( jQuery );