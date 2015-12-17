jQuery( document ).ready( function ( $ ) {

	$( '.datepicker' ).datepicker( {
		prevText: '',
		nextText: '',
		onSelect: function ( date ) {

			if ( $( this ).attr( 'id' ) == 'start-date' ) {
				$( '#end-date' ).datepicker( 'option', 'minDate', date );
			}
		}
	} );
} );