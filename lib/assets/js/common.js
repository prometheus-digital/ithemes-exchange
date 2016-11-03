(function ( $ ) {
	"use strict";

	function formatPrice( amount, symbol, symbolPos ) {

		symbol = typeof symbol === 'undefined' ? ExchangeCommon.config.symbol : symbol;
		symbolPos = typeof symbolPos === 'undefined' ? ExchangeCommon.config.symbolPos : symbolPos;

		if ( symbolPos === 'before' ) {
			return symbol + formatNumber( amount );
		} else {
			return formatNumber( amount ) + symbol;
		}
	}

	/**
	 * Format a number.
	 *
	 * @since 1.36.0
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
	 * @since 1.36.0
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
	 * Get a rest URL.
	 *
	 * @since 1.36.0
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

	window.ExchangeCommon = {
		config      : window.EXCHANGE_CONFIG,
		formatPrice : formatPrice,
		formatNumber: formatNumber,
		formatDate  : formatDate,
		getRestUrl  : getRestUrl
	};

})( jQuery );