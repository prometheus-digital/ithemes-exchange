(function ( $ ) {
	"use strict";

	$( document ).ready( function () {
		$( '.show-email-settings' ).click( function ( e ) {

			e.preventDefault();

			var email = $( this ).data( 'email' );

			$( '.email-settings-container' ).hide();
			$( ".email-" + email ).show();
		} );

		$( '.subsubsub a' ).click( function ( e ) {

			e.preventDefault();

			var group = $( this ).data( 'group' );

			$( '.emails tbody tr' ).show();

			if ( group !== 'all' ) {
				$( '.emails tbody tr[data-group!="' + group + '"]' ).hide();
			}

			$( '.subsubsub .current' ).removeClass( 'current' );
			$( this ).addClass( 'current' );
		} );
	} );

})( jQuery );
