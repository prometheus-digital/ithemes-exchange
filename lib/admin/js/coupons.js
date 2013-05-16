jQuery( document ).ready( function($) {
	$( '.tip, .dice' ).tooltip();
	
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
	
	$( '.coupon-code' ).on( 'click', '.dice', function( event ) {
		event.preventDefault();
		
		$( this ).parent().find( 'input' ).attr( 'value', it_exchange_random_coupon() );
	}).on( 'focusout', '#code', function() {
		if ( $( this ).val() == 'genran' )
			$( this ).val( it_exchange_random_coupon() );
	});
});