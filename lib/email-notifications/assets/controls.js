/* global MediaElementPlayer */
(function ( $, wp, _ ) {

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	var opts = _exchangeEmailCustomizer;
	var presets = opts.presets;

	api.bind( 'ready', function () {

		api( 'it-exchange-email[preset]', function ( value ) {
			value.bind( function ( newValue ) {

				var preset = presets[ newValue ];

				_.each( preset.settings, function ( value, setting ) {

					setting = 'it-exchange-email[' + setting + ']';

					if ( api.control( setting ) instanceof api.MediaControl && value ) {
						var attachment = wp.media.attachment( value );

						if ( ! attachment ) {
							return;
						}

						attachment.sync( 'read' ).then( function ( data ) {

							var control = api.control( setting );

							control.params.attachment = data;
							control.setting( value );

							if ( ! value ) {
								control.renderContent();
							}
						} ).fail( function () {
							api( setting ).set( value );
						} );
					} else {
						api( setting ).set( value );
					}
				} );
			} );
		} );

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

		// WP 4.5 automatically sends the attachment data for media controls.
		// provide compat for lower versions
		if ( ! opts.sendsAttachmentData ) {
			api.control( 'it-exchange-email[header_image]', function ( control ) {
				control.setting.bind( function ( value ) {

					// Send attachment information to the preview for possible use in `postMessage` transport.
					wp.media.attachment( value ).fetch().done( function () {
						wp.customize.previewer.send( control.setting.id + '-attachment-data', this.attributes );
					} );
				} );
			} );

			api.control( 'it-exchange-email[background_image]', function ( control ) {
				control.setting.bind( function ( value ) {

					// Send attachment information to the preview for possible use in `postMessage` transport.
					wp.media.attachment( value ).fetch().done( function () {
						wp.customize.previewer.send( control.setting.id + '-attachment-data', this.attributes );
					} );
				} );
			} );
		}

		/** Footer **/

		api.control( 'it-exchange-email[footer_logo_size]', function ( control ) {
			api( 'it-exchange-email[footer_show_logo]' ).bind( function ( value ) {
				control.active.set( ! ! value );
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
