(function ( $, wp, _ ) {

	if ( ! wp || ! wp.customize ) {
		return;
	}
	var api = wp.customize;

	var opts = _exchangeEmailCustomizer;
	var presets = opts.presets;

	api( 'it-exchange-email[preset]', function ( value ) {
		value.bind( function ( newValue ) {

			var preset = presets[ newValue ];

			_.each( preset.settings, function ( value, setting ) {

				setting = 'it-exchange-email[' + setting + ']';

				api( setting ).set( value );
			} );
		} );
	} );

	api( 'it-exchange-email[body_text_color]', function ( value ) {
		value.bind( function ( newValue ) {
			$( '.email-text-color' ).css( { color: newValue } );
		} );
	} )

})( jQuery, window.wp, _ );
