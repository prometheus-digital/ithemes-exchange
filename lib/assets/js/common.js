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
		symbol = typeof symbol === 'undefined' ? ExchangeCommon.config.symbol : symbol;
		symbolPos = typeof symbolPos === 'undefined' ? ExchangeCommon.config.symbolPos : symbolPos;

		if ( amount < 0 ) {
			before = '–';
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

		decimals = typeof decimals === 'undefined' ? ExchangeCommon.config.decimals : decimals;
		dec_point = typeof decimals === 'undefined' ? ExchangeCommon.config.decimalsSep : dec_point;
		thousands_sep = typeof decimals === 'undefined' ? ExchangeCommon.config.thousandsSep : thousands_sep;

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

		var format = ExchangeCommon.config.dateFormat;

		if ( include_time ) {
			format += ' ' + ExchangeCommon.config.timeFormat;
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

		var thousandsSep = ExchangeCommon.config.thousandsSep;
		var decimalSep = ExchangeCommon.config.decimalsSep;

		var match = new RegExp( "(\\+|-)?((\\d+(\\" + decimalSep + "\\d+)?)|(\\" + decimalSep + "\\d+))" );

		var matches = string.replace( new RegExp( thousandsSep, 'g' ), '' ).match( match );

		if ( !matches || !matches[0] ) {
			return null;
		}

		return Number.parseFloat( matches[0] );
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
			queryArgs._wpnonce = ExchangeCommon.config.restNonce;
		}

		var base = ExchangeCommon.config.restUrl;

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

	window.ExchangeCommon = {
		config               : window.EXCHANGE_CONFIG,
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
	};

})( jQuery );
