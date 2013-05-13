jQuery( document ).ready( function($) {
	
	$( '.add-on-actions .add-on-enabled a' ).hover( function() {
		$( this ).text( $( this ).attr( 'data-text-disable' ) );
	}, function() {
		$( this ).text( $( this ).attr( 'data-text-enabled' ) );
	});
	
	$( '.add-on-actions .add-on-disabled a' ).hover( function() {
		$( this ).text( $( this ).attr( 'data-text-enable' ) );
	}, function() {
		$( this ).text( $( this ).attr( 'data-text-disabled' ) );
	});
	
});