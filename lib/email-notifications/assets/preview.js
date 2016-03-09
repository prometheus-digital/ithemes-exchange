(function ( $, wp, _ ) {

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	var opts = _exchangeEmailCustomizer;
	var presets = opts.presets;

	var $header = $( "#header" );
	var $footer = $( "#footer" );

	api( 'it-exchange-email[preset]', function ( value ) {
		value.bind( function ( newValue ) {

			var preset = presets[ newValue ];

			_.each( preset.settings, function ( value, setting ) {

				setting = 'it-exchange-email[' + setting + ']';

				api( setting ).set( value );
			} );
		} );
	} );

	api( 'it-exchange-email[logo]', function ( value ) {
		value.bind( function ( logo ) {

			if ( isNumeric( logo ) ) {
				return;
			}

			var $headerLogo = $( 'img', $header );
			var $footerLogo = $( 'img', $footer );

			if ( $headerLogo ) {
				$headerLogo.attr( 'src', logo );
			}

			if ( $footerLogo ) {
				$footerLogo.attr( 'src', logo );
			}
		} );
	} );

	api( 'it-exchange-email[header_show_logo]', function ( value ) {
		value.bind( function ( show ) {

			var $img = $( "img", $header );

			if ( ! $img.length ) {

				var src = api( 'it-exchange-email[logo]' ).get();
				var width = api( 'it-exchange-email[header_logo_size]' ).get();

				$( 'tr td', $header ).prepend(
					'<img src="' + src + '" width="' + width + '">'
				);

				$img = $( 'img', $header );
			}

			if ( show ) {
				$img.show();
			} else {
				$img.hide();
			}
		} );
	} );

	api( 'it-exchange-email[header_logo_size]', function ( value ) {
		value.bind( function ( size ) {
			$( "img", $header ).attr( 'width', size );
		} );
	} );

	api( 'it-exchange-email[header_show_store_name]', function ( value ) {
		value.bind( function ( show ) {

			var $h1 = $( "h1", $header );

			if ( ! $h1.length ) {

				var color = api( 'it-exchange-email[header_store_name_color]' ).get();
				var font = api( 'it-exchange-email[header_store_name_font]' ).get();
				var size = api( 'it-exchange-email[header_store_name_size]' ).get();

				$( 'tr td', $header ).append( '<h1></h1>' );

				$h1 = $( 'h1', $header );
				$h1.text( opts.storeName );
				$h1.css( {
					color        : color,
					margin       : '20px 0 0 0',
					'font-family': font,
					'font-size'  : size + 'px'
				} );
			}

			if ( show ) {
				$h1.show();
			} else {
				$h1.hide();
			}
		} );
	} );

	api( 'it-exchange-email[header_store_name_font]', function ( value ) {
		value.bind( function ( fontChoice ) {
			$( "h1", $header ).css( 'font-family', make_font_stack( fontChoice ) );
		} );
	} );

	api( 'it-exchange-email[header_store_name_size]', function ( value ) {
		value.bind( function ( size ) {
			$( "h1", $header ).css( 'font-size', size + 'px' );
		} );
	} );

	api( 'it-exchange-email[header_store_name_color]', function ( value ) {
		value.bind( function ( color ) {
			$( "h1", $header ).css( 'color', color );
		} );
	} );

	api( 'it-exchange-email[header_background]', function ( value ) {
		value.bind( function ( color ) {

			color = color.length ? color : 'transparent';

			$header.css( 'background', color );
			$( "td", $header ).css( 'border-top-color', color );
		} );
	} );

	api( 'it-exchange-email[header_image]', function ( value ) {
		value.bind( function ( attachment ) {
			if ( ! attachment ) {
				$( 'tr td', $header ).css( 'background-image', 'none' );
				$header.css( 'min-height', 0 );
			} else if ( ! isNumeric( attachment ) ) {
				$( 'tr td', $header ).css( 'background-image', 'url(' + attachment + ')' );
				$header.css( 'min-height', '225px' );
			}
		} );
	} );

	api.bind( 'preview-ready', function () {
		api.preview.bind( 'it-exchange-email[header_image]-attachment-data', function ( attachment ) {

			var sizes = attachment.sizes;
			var img = sizes[ 'full' ];

			$( 'td', $header ).css( 'background-image', 'url(' + img.url + ')' );
			$header.css( 'min-height', '225px' );
		} );
	} );

	api( 'it-exchange-email[background_color]', function ( value ) {
		value.bind( function ( color ) {
			$( 'body' ).css( 'background', color );
		} );
	} );

	api( 'it-exchange-email[body_font]', function ( value ) {
		value.bind( function ( fontChoice ) {
			$( 'body' ).css( 'font-family', make_font_stack( fontChoice ) );
		} );
	} );

	api( 'it-exchange-email[body_text_color]', function ( value ) {
		value.bind( function ( color ) {
			$( 'body' ).css( 'color', color );
		} );
	} );

	api( 'it-exchange-email[body_font_size]', function ( value ) {
		value.bind( function ( size ) {
			$( 'body' ).css( 'font-size', size + 'px' );
		} );
	} );

	api( 'it-exchange-email[body_background_color]', function ( value ) {
		value.bind( function ( color ) {

			color = color.length ? color : 'transparent';

			$( '.body-bkg-color' ).css( 'background', color );
		} );
	} );

	api( 'it-exchange-email[body_border_color]', function ( value ) {
		value.bind( function ( color ) {
			$( '.body-border-color' ).css( 'border-color', color );
		} );
	} );

	api( 'it-exchange-email[footer_text]', function ( value ) {
		value.bind( function ( text ) {
			$( '.footer-text-container', '#footer' ).html( text );
		} );
	} );

	api( 'it-exchange-email[footer_text_color]', function ( value ) {
		value.bind( function ( color ) {
			$( '.footer-text-container', '#footer' ).css( 'color', color );
		} );
	} );

	api( 'it-exchange-email[footer_show_logo]', function ( value ) {
		value.bind( function ( show ) {

			var $img = $( "img", $footer );

			if ( ! $img.length ) {

				var src = api( 'it-exchange-email[logo]' ).get();
				var width = api( 'it-exchange-email[footer_logo_size]' ).get();

				$( '.footer-logo-container', $footer ).prepend(
					'<img src="' + src + '" width="' + width + '">'
				);

				$img = $( 'img', $footer );
				$img.css( 'margin-top', '40px' );
			}

			if ( show ) {
				$img.show();
			} else {
				$img.hide();
			}
		} );
	} );

	api( 'it-exchange-email[footer_logo_size]', function ( value ) {
		value.bind( function ( size ) {
			$( ".footer-logo-container img", $footer ).attr( 'width', size );
		} );
	} );

	/**
	 * Make the font stack from a choice.
	 *
	 * @param choice
	 * @returns {*}
	 */
	function make_font_stack( choice ) {

		switch ( choice ) {
			case 'serif':
				return "'Georgia', 'Times New Roman', serif";
			case 'sans-serif':
				return "'Helvetica', Arial, sans-serif";
			case 'monospace':
				return 'Courier, Monaco, monospace';
			default:
				return choice;
		}
	}

	function isNumeric( n ) {
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	}

})( jQuery, window.wp, _ );
