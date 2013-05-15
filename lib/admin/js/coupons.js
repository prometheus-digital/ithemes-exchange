jQuery( document ).ready( function($) {
	$( '.tip' ).tooltip();
	$( '.datepicker' ).datepicker();
	
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
	});
});