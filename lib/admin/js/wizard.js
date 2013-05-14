jQuery( document ).ready( function($) {
	$( '.tip' ).tooltip();
	
	$( '.payments' ).on( 'click', 'li', function() {
		$( this ).toggleClass( 'selected' );
		
		$( '.' + $( this ).attr( 'data-toggle' ) ).fadeToggle( 250 );
	});
	
	$( '.stripe-wizard' ).on( 'click', '.stripe-action', function() {
		window.open( $( this ).find( 'a' ).attr( 'href' ), $( this ).find( 'a' ).attr( 'target' ) );
	});
});