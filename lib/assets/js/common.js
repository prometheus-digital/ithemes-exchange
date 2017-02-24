(function ( $ ) {
	"use strict";

	/**
	 * Format a number as a price.
	 *
	 * @since 2.0.0
	 *
	 * @param {Number} amount The amount to format as a price.
	 * @param {String} [symbol] The currency symbol.
	 * @param {String} [symbolPos] Either 'before' or 'after'
	 *
	 * @returns {string}
	 */
	function formatPrice( amount, symbol, symbolPos ) {

		var before = '';
		symbol = typeof symbol === 'undefined' ? itExchange.common.config.symbol : symbol;
		symbolPos = typeof symbolPos === 'undefined' ? itExchange.common.config.symbolPos : symbolPos;

		if ( amount < 0 ) {
			before = '−';
			amount *= -1;
		}

		if ( symbolPos === 'before' ) {
			return before + symbol + formatNumber( amount );
		} else {
			return before + formatNumber( amount ) + symbol;
		}
	}

	/**
	 * Format a number.
	 *
	 * @since 2.0.0
	 *
	 * @param number
	 * @param [decimals]
	 * @param [dec_point]
	 * @param [thousands_sep]
	 *
	 * @returns {string}
	 */
	function formatNumber( number, decimals, dec_point, thousands_sep ) {

		decimals = typeof decimals === 'undefined' ? itExchange.common.config.decimals : decimals;
		dec_point = typeof decimals === 'undefined' ? itExchange.common.config.decimalsSep : dec_point;
		thousands_sep = typeof decimals === 'undefined' ? itExchange.common.config.thousandsSep : thousands_sep;

		number = (number + '').replace( thousands_sep, '' ); //remove thousands
		number = (number + '').replace( dec_point, '.' ); //turn number into proper float (if it is an improper float)
		number = (number + '').replace( /[^0-9+\-Ee.]/g, '' );
		var n = !isFinite( +number ) ? 0 : +number;
		var prec = !isFinite( +decimals ) ? 0 : Math.abs( decimals );
		var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
		var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
		var s = '', toFixedFix = function ( n, prec ) {
			var k = Math.pow( 10, prec );
			return '' + Math.round( n * k ) / k;
		};

		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = ( prec ? toFixedFix( n, prec ) : '' + Math.round( n ) ).split( '.' );

		if ( s[0].length > 3 ) {
			s[0] = s[0].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep );
		}

		if ( (s[1] || '').length < prec ) {
			s[1] = s[1] || '';
			s[1] += new Array( prec - s[1].length + 1 ).join( '0' );
		}

		return s.join( dec );
	}

	/**
	 * Format a date.
	 *
	 * @since 2.0.0
	 *
	 * @param dateTime
	 * @param [include_time] false
	 *
	 * @returns {string}
	 */
	function formatDate( dateTime, include_time ) {
		include_time = include_time || false;

		var format = itExchange.common.config.dateFormat;

		if ( include_time ) {
			format += ' ' + itExchange.common.config.timeFormat;
		}

		return moment( dateTime ).format( format );
	}

	/**
	 * Get float from a string.
	 *
	 * @since 2.0.0
	 *
	 * @param {String} string
	 *
	 * @returns {Number|null}
	 */
	function getFloatFromString( string ) {

		var thousandsSep = itExchange.common.config.thousandsSep;
		var decimalSep = itExchange.common.config.decimalsSep;
		var symbol = _regexEscape( itExchange.common.config.symbol );

		var match = new RegExp( "^(\\+|-)?" + symbol + "?(\\+|-)?(\\d+(?:\\.)?\\d*)" );

		string = string.replace( thousandsSep, '' );

		if ( decimalSep !== '.' ) {
			string = string.replace( decimalSep, '.' );
		}

		var matches = string.match( match );

		if ( !matches || !matches[3] ) {
			return null;
		}

		var parse = matches[3];

		if ( matches[1] ) {
			parse = matches[1] + parse;
		}

		return Number.parseFloat( parse );
	}

	function _regexEscape( text ) {
		return text.replace( /[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&" );
	}

	/**
	 * Get a rest URL.
	 *
	 * @since 2.0.0
	 *
	 * @param [path]      {string}
	 * @param [queryArgs] {object}
	 * @param [nonce] {boolean}
	 *
	 * @returns {string}
	 */
	function getRestUrl( path, queryArgs, nonce ) {

		queryArgs = queryArgs || {};
		nonce = typeof nonce === 'undefined' ? true : nonce;

		if ( nonce ) {
			queryArgs._wpnonce = itExchange.common.config.restNonce;
		}

		var base = itExchange.common.config.restUrl;

		if ( path ) {
			base += path + '/';
		}

		if ( !$.isEmptyObject( queryArgs ) ) {
			base += '?' + $.param( queryArgs );
		}

		return base;
	}

	/**
	 * Get an error from a jqXHR.
	 *
	 * @since 2.0.0
	 *
	 * @param {XMLHttpRequest} xhr
	 *
	 * @returns {String}
	 */
	function getErrorFromXhr( xhr ) {
		try {
			var data = $.parseJSON( xhr.responseText );
		} catch ( e ) {
		}

		if ( !data ) {
			return window.EXCHANGE_CONFIG.i18n.unknownError;
		}

		return getErrorFromResponse( data );
	}

	/**
	 * Get an error from a JSON response.
	 *
	 * @since 2.0.0
	 *
	 * @param {*} response
	 *
	 * @returns {String}
	 */
	function getErrorFromResponse( response ) {
		if ( response.message ) {
			return response.message;
		}

		if ( response.feedback && response.feedback.errors.length ) {
			var error;
			for ( var i = 0; i < response.feedback.errors.length; i++ ) {
				error = response.feedback.errors[i];

				if ( error && error.text ) {
					return error.text;
				}
			}
		}

		return window.EXCHANGE_CONFIG.i18n.unknownError;
	}

	/**
	 * Convert a kebab cased string to camel cased.
	 *
	 * @since 2.0.0
	 *
	 * @param {String} s
	 *
	 * @returns {String}
	 */
	function kebabToCamel( s ) {
		return s.replace( /(\-\w)/g, function ( m ) {return m[1].toUpperCase();} );
	}

	/**
	 * Execute a function by name.
	 *
	 * @since 2.0.0
	 *
	 * @link http://stackoverflow.com/a/359910
	 *
	 * @param {String} functionName
	 * @param {*} context
	 *
	 * @returns {*}
	 */
	function executeFunctionByName( functionName, context/*, args */ ) {
		var args = [].slice.call( arguments ).splice( 2 );
		var namespaces = functionName.split( "." );
		var func = namespaces.pop();

		for ( var i = 0; i < namespaces.length; i++ ) {
			context = context[namespaces[i]];
		}

		return context[func].apply( context, args );
	}

	/**
	 * Retrieve a function by name.
	 *
	 * @since 2.0.0
	 *
	 * @link http://stackoverflow.com/a/359910
	 *
	 * @param {String} functionName
	 * @param {*} context
	 *
	 * @returns {*}
	 */
	function getFunctionByName( functionName, context ) {

		var namespaces = functionName.split( "." );
		var func = namespaces.pop();

		for ( var i = 0; i < namespaces.length; i++ ) {
			context = context[namespaces[i]];
		}

		return context[func];
	}

	/**
	 * Zeroise a number.
	 *
	 * @since 2.0.0
	 *
	 * @param {Number}number
	 * @param {Number} threshold
	 * @param {String} [z]
	 *
	 * @returns {String}
	 */
	function zeroise( number, threshold, z ) {
		z = z || '0';
		number = number + '';
		return number.length >= threshold ? number : new Array( threshold - number.length + 1 ).join( z ) + number;
	}

	/**
	 * Format an address.
	 *
	 * @since 2.0.0
	 *
	 * @param {*} address
	 * @param {*} [args]
	 * @param {Array} [format]
	 *
	 * @returns {String}
	 */
	function formatAddress( address, args, format ) {

		var part, i, line, replacedLine, defaultFormat = [
			'{first-name} {last-name}',
			'{company-name}',
			'{address1}',
			'{address2}',
			'{city} {state} {zip}',
			'{country}'
		];

		format = format || defaultFormat;

		args = _.defaults( args || {}, {
			'open-block' : '',
			'close-block': '',
			'open-line'  : '',
			'close-line' : '<br>'
		} );

		var parts = {};

		for ( part in address ) {
			if ( address.hasOwnProperty( part ) ) {
				parts[part] = '{' + part + '}';
			}
		}

		var replaced = [];

		for ( i = 0; i < format.length; i++ ) {

			line = format[i];
			replacedLine = line;

			for ( part in parts ) {
				if ( parts.hasOwnProperty( part ) ) {
					var value = address[part] ? _.escape( address[part] ) : '';
					replacedLine = replacedLine.replace( parts[part], value );
				}
			}

			// get rid of any remaining, un-replaced tags
			replaced.push( replacedLine.trim().replace( /{.*?}/g, '' ) );
		}

		var open = args['open-line'], close = args['close-line'];

		var out = args['open-block'];

		for ( i = 0; i < replaced.length; i++ ) {
			replacedLine = replaced[i];

			if ( replacedLine.trim() !== '' ) {
				out += open + replacedLine + close;
			}
		}

		out += args['close-block'];

		return out;
	}

	var config = window.EXCHANGE_CONFIG;
	config.currentUser = parseInt( config.currentUser );

	window.itExchange = window.itExchange || {};
	window.itExchange.common = {
		config               : config,
		formatPrice          : formatPrice,
		formatNumber         : formatNumber,
		formatDate           : formatDate,
		getFloatFromString   : getFloatFromString,
		getRestUrl           : getRestUrl,
		getErrorFromXhr      : getErrorFromXhr,
		getErrorFromResponse : getErrorFromResponse,
		kebabToCamel         : kebabToCamel,
		getFunctionByName    : getFunctionByName,
		executeFunctionByName: executeFunctionByName,
		zeroise              : zeroise,
		formatAddress        : formatAddress,
	};

})( jQuery );
