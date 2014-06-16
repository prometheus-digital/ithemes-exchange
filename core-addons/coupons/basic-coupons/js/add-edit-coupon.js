jQuery( document ).ready( function($) {
	// Init tooltip code
	$( '.tip, .dice' ).tooltip();

	// Init date picker on coupon code start / end fields
	$( '.datepicker' ).datepicker({
		prevText: '',
		nextText: '',
		minDate: 0,
		onSelect: function( date ) {
			if ( ! $( '#' + $( this ).attr( 'data-append' ) ).val() )
				$( '#' + $( this ).attr( 'data-append' ) ).val( date );

			if ( $( this ).attr( 'id' ) == 'start-date' )
				$( '#end-date' ).datepicker( 'option', 'minDate', date );
		}
	});

	// Generate coupon code when dice is clicked
	$( '.coupon-code' ).on( 'click', '.dice', function( event ) {
		event.preventDefault();

		$( this ).parent().find( 'input' ).attr( 'value', it_exchange_random_coupon() );
	}).on( 'focusout', '#code', function() {
		if ( $( this ).val() == 'genrand' )
			$( this ).val( it_exchange_random_coupon() );
	});

	// Show hide quantity limit based on checkbox
	function itExchangeBasicCouponsShowHideQuantity() {
		var selected = $(this).is( ':checked' );
		var $fields  = $('.quantity');

		$fields.addClass('hide-if-js');
		if ( selected ) {
			$fields.removeClass('hide-if-js');
		} else {
			$fields.addClass('hide-if-js');
		}
	}
	$('#limit-quantity').change(itExchangeBasicCouponsShowHideQuantity).triggerHandler("change");

	// Show hide product limit based on checkbox
	function itExchangeBasicCouponsShowHideProduct() {
		var selected = $(this).is( ':checked' );
		var $fields  = $('.product-id');

		$fields.addClass('hide-if-js');
		if ( selected ) {
			$fields.removeClass('hide-if-js');
		} else {
			$fields.addClass('hide-if-js');
		}
	}
	$('#limit-product').change(itExchangeBasicCouponsShowHideProduct).triggerHandler("change");

	// Show hide frequeny limit based on checkbox
	function itExchangeBasicCouponsShowHideFrequency() {
		var selected = $(this).is( ':checked' );
		var $fields  = $('.frequency-limitations');

		$fields.addClass('hide-if-js');
		if ( selected ) {
			$fields.removeClass('hide-if-js');
		} else {
			$fields.addClass('hide-if-js');
		}
	}
	$('#limit-frequency').change(itExchangeBasicCouponsShowHideFrequency).triggerHandler("change");
});

/**
 * Generates a random coupon code
**/
function it_exchange_random_coupon( number ) {
	if ( ! number ) {
		number = 12;
	}

	var coupon = '';
	var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	for ( var i = 0; i < number; i++ ) {
		coupon += possible.charAt( Math.floor( Math.random() * possible.length ) );
	}

	return coupon;
}

