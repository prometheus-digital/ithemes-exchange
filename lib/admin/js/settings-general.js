jQuery(function() {
	jQuery( 'form#it-exchange-settings' ).submit( function() {
		if ( jQuery( '#reset-exchange' ).is(':checked') ) {
			return confirm( settingsGenearlL10n.delteConfirmationText );
		}
	});
});
