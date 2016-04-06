(function ( $, wp, _ ) {

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	var opts = _exchangeEmailCustomizer;
	var presets = opts.presets;

	var $body = $( 'body' );
	var $main = $( '.main' );

	var $headerRow = $( ".header-row" );
	var $header = $( "#header" );

	var $footerRow = $( ".footer-row" );
	var $footer = $( "#footer" );

	api( 'it-exchange-email[preset]', function ( value ) {
		value.bind( function ( newValue ) {

			var preset = presets[ newValue ];

			_.each( preset.settings, function ( value, setting ) {

				if ( setting == 'layout' ) {
					return;
				}

				setting = 'it-exchange-email[' + setting + ']';

				api( setting ).set( value );
			} );

			if ( preset.settings.layout != api( 'it-exchange-email[layout]' ).get() ) {
				api( 'it-exchange-email[layout]' ).set( preset.settings.layout );
			}

		} );
	} );

	api( 'it-exchange-email[layout]', function ( value ) {
		value.bind( function ( layout ) {

			var styles = background_el_styles();

			if ( layout === 'full' ) {

				// head

				_.each( styles, function ( value, property ) {
					$main.css( property, value );
					$body.css( property, '' );
				} );

				// header

				$( ".header-bkg" ).removeClass( 'header-bkg' );
				$header.css( 'background', 'none' );
				$headerRow.css( 'background', api( 'it-exchange-email[header_background]' ).get() );
				$headerRow.addClass( 'header-bkg' );

				$header.css( 'margin-top', '0' );

				// footer

				$( ".footer-bkg" ).removeClass( 'footer-bkg' );
				$footer.css( 'background', 'none' );
				$footerRow.css( 'background', api( 'it-exchange-email[footer_background]' ).get() );
				$footerRow.addClass( 'footer-bkg' );

			} else {

				// head

				_.each( styles, function ( value, property ) {
					$body.css( property, value );
					$main.css( property, '' );
				} );

				// header

				$( ".header-bkg" ).removeClass( 'header-bkg' );
				$headerRow.css( 'background', 'none' );
				$header.css( 'background', api( 'it-exchange-email[header_background]' ).get() );
				$header.addClass( 'header-bkg' );

				$header.css( 'margin-top', '40px' );

				// footer

				$( ".footer-bkg" ).removeClass( 'footer-bkg' );
				$footerRow.css( 'background', 'none' );
				$footer.css( 'background', api( 'it-exchange-email[footer_background]' ).get() );
				$footer.addClass( 'footer-bkg' );
			}

			$body.css( 'background-color', body_el_bkg() );
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

				var $h1 = $( "h1", $header );

				if ( $h1.length ) {
					$h1.css( 'margin-top', '20px' );
				}
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
				var margin;

				if ( $( 'img', $header ).length ) {
					margin = "20px 0 0 0";
				} else {
					margin = '0';
				}

				$( 'tr td', $header ).append( '<h1></h1>' );

				$h1 = $( 'h1', $header );
				$h1.text( opts.storeName );
				$h1.css( {
					color        : color,
					margin       : margin,
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

			$( '.header-bkg' ).css( 'background', color );
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

	api( 'it-exchange-email[body_highlight_color]', function ( value ) {
		value.bind( function ( color ) {
			$( '.border-highlight-color' ).css( 'border-color', color );

			var $button = $( '.button' );

			$button.css( 'background-color', color );

			var rgb = hex2rgb( color );

			if ( colourIsLight( rgb.r, rgb.g, rgb.b ) ) {
				$button.css( 'color', '#000000' );
			} else {
				$button.css( 'color', '#ffffff' );
			}
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

	api( 'it-exchange-email[footer_background]', function ( value ) {
		value.bind( function ( color ) {

			var $footerBkg = $( '.footer-bkg' );
			var prevColor = $footerBkg.css( 'background-color' );

			if ( prevColor == 'rgba(0, 0, 0, 0)' ) {
				prevColor = 'transparent';
			}

			color = color.length ? color : 'transparent';

			$footerBkg.css( 'background-color', color );
			$body.css( 'background-color', color );

			if ( ( color == 'transparent' || prevColor == 'transparent' ) && api( 'it-exchange-email[layout]' ).get() == 'full' ) {
				$body.attr( 'style', background_el_styles() + $body.attr( 'style' ) );
			}
		} );
	} );

	api( 'it-exchange-email[background_color]', function ( value ) {
		value.bind( function ( color ) {

			$( '.main' ).css( 'background-color', color );
			$( 'body' ).css( 'background-color', color );
		} );
	} );

	api( 'it-exchange-email[background_image]', function ( value ) {
		value.bind( function ( attachment ) {

			if ( ! attachment ) {
				$( '.main' ).css( 'background-image', 'none' );
			} else if ( ! isNumeric( attachment ) ) {
				$( '.main' ).css( 'background-image', 'url(' + attachment + ')' );
			}
		} );
	} );

	api.bind( 'preview-ready', function () {
		api.preview.bind( 'it-exchange-email[background_image]-attachment-data', function ( attachment ) {

			var sizes = attachment.sizes;
			var img = sizes[ 'full' ];

			$( '.main' ).css( 'background-image', 'url(' + img.url + ')' );
		} );
	} );

	api( 'it-exchange-email[background_image_position]', function ( value ) {
		value.bind( function ( position ) {
			$( '.main' ).css( 'background-image-position', position );
		} );
	} );

	api( 'it-exchange-email[background_image_repeat]', function ( value ) {
		value.bind( function ( repeat ) {
			$( '.main' ).css( 'background-image-repeat', repeat );
		} );
	} );

	/**
	 * Check if a color is light.
	 *
	 *
	 * @link http://stackoverflow.com/a/1855903
	 *
	 * @param r
	 * @param g
	 * @param b
	 *
	 * @returns {boolean}
	 */
	function colourIsLight( r, g, b ) {

		// Counting the perceptive luminance
		// human eye favors green color...
		var a = 1 - (0.299 * r + 0.587 * g + 0.114 * b) / 255;

		return (a < 0.5);
	}

	/**
	 * Convert a hex color to an RGB object.
	 *
	 * @since 1.36
	 *
	 * @param col
	 *
	 * @returns {{r: (Number|*), g: (Number|*), b: (Number|*)}}
	 */
	function hex2rgb( col ) {
		var r, g, b;
		if ( col.charAt( 0 ) == '#' ) {
			col = col.substr( 1 );
		}
		r = col.charAt( 0 ) + col.charAt( 1 );
		g = col.charAt( 2 ) + col.charAt( 3 );
		b = col.charAt( 4 ) + col.charAt( 5 );
		r = parseInt( r, 16 );
		g = parseInt( g, 16 );
		b = parseInt( b, 16 );

		return {
			r: r,
			g: g,
			b: b
		}
	}

	/**
	 * Make the font stack from a choice.
	 *
	 * @since 1.36
	 *
	 * @param choice
	 *
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

	/**
	 * Check if a given value is numeric.
	 *
	 * @since 1.36
	 *
	 * @param n
	 *
	 * @returns {boolean}
	 */
	function isNumeric( n ) {
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	}

	/**
	 * Return the background element styles.
	 *
	 * @since 1.36
	 *
	 * @returns {{}}
	 */
	function background_el_styles() {

		var styles = {};

		styles[ "background-color" ] = api( 'it-exchange-email[background_color]' );

		if ( api( 'it-exchange-email[background_image]' ).get().length ) {
			styles[ "background-image" ] = "url(" + api( 'it-exchange-email[background_image]' ).get() + ")";
			styles[ "background-position" ] = api( 'it-exchange-email[background_image_position]' ).get();
			styles[ "background-repeat" ] = api( 'it-exchange-email[background_image_repeat]' ).get();
		}

		return styles;
	}

	/**
	 * Return the color to use for the body element.
	 *
	 * @since 1.36
	 *
	 * @returns {string}
	 */
	function body_el_bkg() {

		var color = api( 'it-exchange-email[footer_background]' ).get();

		if ( ! color || api( 'it-exchange-email[layout]' ).get() === 'boxed' ) {
			color = api( 'it-exchange-email[background_color]' ).get();
		}

		return color;
	}

})( jQuery, window.wp, _ );
