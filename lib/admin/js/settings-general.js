jQuery( document ).ready( function( $ ) {
	$( 'form#it-exchange-settings' ).submit( function() {
		if ( $( '#reset-exchange' ).is(':checked') ) {
			return confirm( settingsGenearlL10n.delteConfirmationText );
		}
	});
	
	$( '.tip' ).tooltip();
});
