(function ( $ ) {
	"use strict";

	$( document ).ready( function () {
		$( '.show-email-settings' ).click( function ( e ) {

			e.preventDefault();

			var email = $( this ).data( 'email' );

			$( '.email-settings-container' ).hide();
			$( ".email-" + email ).show();
		} );
	} );
})( jQuery );