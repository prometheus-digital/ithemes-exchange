<?php
/**
 * Evaluates natural language strings to boolean equivalent
 *
 * Used primarily for handling boolean text provided in it_exchange() function options.
 * All values defined as true will return true, anything else is false.
 *
 * Boolean values will be passed through.
 *
 * @since 0.4.0
 *
 * @param string $string The natural language value
 * @param array $istrue A list strings that are true
 * @return boolean The boolean value of the provided text
 **/
function it_exchange_str_true ( $string, $istrue = array('yes', 'y', 'true','1','on','open') ) {
    if (is_array($string)) return false;
    if (is_bool($string)) return $string;
    return in_array(strtolower($string),$istrue);
}

/**
 * Parses tag option strings or arrays
 *
 * @author Jonathan Davis from Shopp
 * @since 0.4.0
 *
 * @param string|array $options URL-compatible query string or associative array of tag options
 * @return array API-ready options list 
 **/
function it_exchange_parse_options( $options ) {
	// Set empty array
	$paramset = array();

	// If options is empty, return empty array
	if ( empty( $options ) )
		return $paramset;

	// If options is string, convert to array ($paramset) via parse_str
	if ( is_string( $options) )
		parse_str( $options, $paramset );
	else 
		$paramset = $options;

	// Passed options are now an array ($paramset). Reset $options variable
	$options = array();

	// Clean keys and values
	foreach ( array_keys($paramset) as $key )
		$options[ strtolower($key) ] = $paramset[$key];

	// Strip slashes
	if ( get_magic_quotes_gpc() )
		$options = stripslashes_deep( $options );

	return $options;
}

/**
 * Formats a price based on settings
 *
 * @since 0.4.0
 * @todo possibly get this working with LC_MONETARY and money_format()
 * @return string
*/
function it_exchange_format_price( $price ) {
	$before = ''; $after = '';
	
	$settings = it_exchange_get_option( 'settings_general' );
	
	$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );
	
	if ( 'before' === $settings['currency-symbol-position'] )
		$before = $currency['symbol'];
	else
		$after = $currency['symbol'];
	
	return $before . ' ' . number_format ( $price, 2, $settings['currency-decimals-separator'], $settings['currency-thousands-separator'] ) . ' ' . $after;
}
