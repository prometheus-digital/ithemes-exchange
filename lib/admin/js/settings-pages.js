jQuery( document ).ready( function( $ ) {
	$( '.page-type select' ).on( 'change', function() {
		var current_row = $( this ).parent().parent().parent();
		
		if ( $( this ).val() == 'exchange' ) {
			current_row.find( '.toggle-disabled' ).removeClass( 'hidden' );
			current_row.find( '.ex-page' ).removeClass( 'hidden' );
			current_row.find( '.wp-page' ).addClass( 'hidden' );
		} else if ( $( this ).val() == 'wordpress' ) {
			current_row.find( '.toggle-disabled' ).removeClass( 'hidden' );
			current_row.find( '.wp-page' ).removeClass( 'hidden' );
			current_row.find( '.ex-page' ).addClass( 'hidden' );
		} else if ( $( this ).val() == 'disabled' ) {
			current_row.find( '.toggle-disabled' ).addClass( 'hidden' );
		}
	});
});
