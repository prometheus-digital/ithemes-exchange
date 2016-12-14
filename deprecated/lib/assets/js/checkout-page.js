(function ( $ ) {

	var orderNotesForm = $( ".it-exchange-customer-order-notes-form" );
	var orderNotesSummary = $( ".it-exchange-customer-order-notes-summary" );

	$( ".it-exchange-edit-customer-order-notes" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.hide();
		orderNotesForm.show();
	} );

	$( ".it-exchange-customer-order-note-cancel" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.show();
		orderNotesForm.hide();
	} );

})( jQuery );
