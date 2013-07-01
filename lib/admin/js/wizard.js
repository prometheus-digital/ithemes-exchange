jQuery( document ).ready( function($) {
	$( '.tip' ).tooltip();
	
	$( '.payments' ).on( 'click', 'li', function() {
		$( this ).toggleClass( 'selected' );
		
		$( '.' + $( this ).attr( 'data-toggle' ) ).toggle();
		
		if ( $( this ).hasClass( 'payoption' ) ) {
			
			if ( $( '.' + $( this ).attr( 'data-toggle' ) ).is( ':visible' ) )
				$( '.' + $( this ).attr( 'data-toggle' ) ).append( '<input class="enable-' + $( this ).attr( 'transaction-method' ) + '" type="hidden" name="it-exchange-transaction-methods[]" value="' + $( this ).attr( 'transaction-method' ) + '" />' );
			else
				$( '.enable-' + $( this ).attr( 'transaction-method' ) ).remove();
				
		}
		
	});
	
	$( '.stripe-wizard' ).on( 'click', '.stripe-action', function() {
		window.open( $( this ).find( 'a' ).attr( 'href' ), $( this ).find( 'a' ).attr( 'target' ) );
	});
	
	$( '.remove-if-js' ).remove();
});