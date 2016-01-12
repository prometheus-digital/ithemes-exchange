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
		var $fields  = $('.product-id, .excluded-products, .product-category');

		$fields.addClass('hide-if-js');
		if ( selected ) {
			$fields.removeClass('hide-if-js');
		} else {
			$fields.addClass('hide-if-js');
		}
	}
	$('#limit-product').change(itExchangeBasicCouponsShowHideProduct).triggerHandler("change");

	// Show hide customer based on checkbox
	function itExchangeBasicCouponsShowHideCustomer() {
		var selected = $(this).is( ':checked' );
		var $fields  = $('.customer');

		$fields.addClass('hide-if-js');
		if ( selected ) {
			$fields.removeClass('hide-if-js');
		} else {
			$fields.addClass('hide-if-js');
		}
	}
	$('#limit-customer').change(itExchangeBasicCouponsShowHideCustomer).triggerHandler("change");

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

	$("#product-id, #excluded-products, #product-category").select2( {
		placeholder: IT_EXCHANGE.productPlaceholder
	});

	// init tabbed section
	$( '#it-exchange-advanced-tabs' ).tabs({
        active: get_default_tab(),
        activate: function(event, ui) {
            store_current_tab( $( '#it-exchange-advanced-tabs' ).tabs( 'option', 'active' ) );
        }
    });

	// set initial height of tab content, then reset on resize
	setTimeout(function() {
		$( '#it-exchange-advanced-tabs .inner').css( 'min-height', $( '#it-exchange-advanced-tabs').height() );
	}, 0 );

	$( window ).resize( function() {
		$( '#it-exchange-advanced-tabs .inner').css( 'min-height', $( '#it-exchange-advanced-tabs').height() );
	});

    /**
     * Get the current coupon ID.
     *
     * @returns {number}
     */
	function get_coupon_id() {
		return $("input[name='it-exchange-basic-coupons-ID']" ).val();
	}

    /**
     * Store the current tab.
     *
     * @param tab
     */
	function store_current_tab(tab) {

		if ( ! get_coupon_id() ) {
			return;
		}

		if ( isLocalStorageSupported() ) {

			var ID = get_coupon_id();

			window.localStorage.setItem( 'it-exchange-coupon-' + ID, tab );
		}
	}

    /**
     * Get the default tab for this coupon.
     *
     * @returns {number}
     */
    function get_default_tab() {
        if ( ! get_coupon_id() ) {
            return 0;
        }

        if ( ! isLocalStorageSupported() ) {
            return 0;
        }

        return window.localStorage.getItem( 'it-exchange-coupon-' + get_coupon_id() );
    }

    /**
     * Check if local storage is supported.
     *
     * @returns {boolean}
     */
	function isLocalStorageSupported() {
		var testKey = 'test', storage = window.localStorage;
		try {
			storage.setItem(testKey, '1');
			storage.removeItem(testKey);
			return true;
		} catch (error) {
			return false;
		}
	}

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

