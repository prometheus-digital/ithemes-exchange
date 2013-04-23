<?php
/**
 * Loads the theme api
*/

include( $this->_plugin_path . '/api/theme/cart.php' );
include( $this->_plugin_path . '/api/theme/checkout.php' );
include( $this->_plugin_path . '/api/theme/confirmation.php' );
include( $this->_plugin_path . '/api/theme/product.php' );
include( $this->_plugin_path . '/api/theme/download.php' );

/**
 * Defines the main it_exchange function
 *
 * @since 0.4.0
*/
function it_exchange() {

	// Set array keys for possible params
	$params = array( 'one', 'two', 'three' );

	// Grab number of params passed in
	$num_args = func_num_args();

	// Set initial values
	$object  = false;
	$context = false;
	$tag     = false;
	$method  = false;
	$options = array(
		'echo'   => true,
		'return' => false,
	);
	$get     = false;
	
	/** @todo log error **/
	// Die if we don't have any args
	if ( $num_args < 1 )
		return;

	$passed_params = func_get_args();
	$params = array_combine( array_slice( $params, 0, $num_args ), $passed_params );

	// Parse Params
	if ( is_object( $params['one'] ) ) {
		// When first param is an API object
		$object = $params['one'];
		$context = ! empty( $object->api ) ? $object->api : strtolower( get_class( $object ) );
		$tag = strtolower( $params['two'] );
		// Parse options
		if ( $num_args > 2 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['three'] );
		}
	} else if ( false !== strpos( $params['one'], '.' ) ) { 
		// When first param is the object.method string format
		list( $context, $tag ) = explode( '.', strtolower( $params['one'] ) );
		// Parse options if present
		if ( $num_args > 1 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['two'] );
		}
	} else if ( '' == $context . $tag ) {
		// When context is first param and method is second param
		list( $context, $tag ) = array_map( 'strtolower', array( $params['one'], $params['two'] ) );
		// Parse options
		if ( $num_args > 2 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['three'] );
		}
	}   

	// Strip hypens from method name
	$tag = str_replace ( '-', '', $tag );

	// Strip get prefix from requested method and set flags
	if ( 'get' == substr( $tag, 0, 3 ) ) { 
		$tag = substr( $tag, 3 );
		$get = true;
	}   

	// Strip has prefix from request method and set flags
	if ( 'has' == substr( $tag, 0, 3 ) ) { 
		$tag = substr( $tag, 3 );
		$options['has'] = true;
	} else {
		$options['has'] = false;
	}

	// Set object
	if ( ! is_object( $object ) ) {

		// Set the class name based on params
		$class_name = 'IT_Theme_API_' . ucfirst( strtolower( $context ) );

		// Does the class exist and return an iThemes Exchange theme API context?
		if ( ! is_callable( array( $class_name, 'get_api_context' ) ) )
			die('not callable'.__FILE__. ' : ' . __LINE__); /** @todo register an error **/

		// Set the object
		$object = new $class_name();
	}

	// Is the requested tag mapped to a method
	if ( empty( $object->_tag_map[$tag] ) )
		die( 'unmapped method for context' ); /** @todo register an error **/
	else
		$method = $object->_tag_map[$tag];

	// Does the method called exist on this class?
	if ( ! is_callable( array( $object, strtolower( $method ) ) ) )
		die('method not callable: ' . $method . ': ' . $context ); /** @todo register an error **/

	// Get the results from the class method
	$result = call_user_func( array( $object, strtolower( $method ) ), $options );

	// Force boolean result
	if ( isset( $options['is'] ) ) {
		if ( it_exchange_str_true( $options['is'] ) ) {
			if ( $result )
				return true;
		} else {
			if ($result == false)
				return true;
		}
		return false;
	}

	// Always return a boolean if the result is boolean
	if ( is_bool( $result ) ) 
		return $result;

	// Return result without printing if requested
	if ( $get
			|| ( isset( $options['return'] ) && it_exchange_str_true( $options['return'] ) ) 
			|| ( isset( $options['echo'] ) && ! it_exchange_str_true( $options['echo'] ) )
		)
		return $result;

	// Output the result
	if ( is_scalar( $result ) )
		echo $result;
	else 
		return $result;

    return true;
}

/**
 * Enforces minimal class structure
 *
 * @since 0.4.0
*/
interface IT_Theme_API {
	function get_api_context();
}
