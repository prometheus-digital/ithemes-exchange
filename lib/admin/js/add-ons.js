jQuery( document ).ready( function($) {

	$( '.tip' ).tooltip();

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

	$( 'input.show-test-mode-options').on( 'change', function() {
		$( '.test-mode-options' ).toggleClass( 'hide-if-live-mode' );
	});

});