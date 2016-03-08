(function ( $, wp, _ ) {

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	api.bind( 'ready', function () {

		/** Header **/

		api.control( 'it-exchange-email[header_logo_size]', function ( control ) {
			api( 'it-exchange-email[header_show_logo]' ).bind( function ( value ) {
				control.active.set( ! ! value );
			} );
		} );

		api.control( 'it-exchange-email[header_store_name_font]', function ( control ) {
			api( 'it-exchange-email[header_show_store_name]' ).bind( function ( value ) {
				control.active.set( value );
			} );
		} );

		api.control( 'it-exchange-email[header_store_name_size]', function ( control ) {
			api( 'it-exchange-email[header_show_store_name]' ).bind( function ( value ) {
				control.active.set( value );
			} );
		} );

		api.control( 'it-exchange-email[header_store_name_color]', function ( control ) {
			api( 'it-exchange-email[header_show_store_name]' ).bind( function ( value ) {
				control.active.set( value );
			} );
		} );

		/** Background **/

		api.control( 'it-exchange-email[background_image_position]', function ( control ) {
			api( 'it-exchange-email[background_image]' ).bind( function ( value ) {
				control.active.set( value );
			} );
		} );

		api.control( 'it-exchange-email[background_image_repeat]', function ( control ) {
			api( 'it-exchange-email[background_image]' ).bind( function ( value ) {
				control.active.set( value );
			} );
		} );
	} );

})( jQuery, window.wp, _ );
