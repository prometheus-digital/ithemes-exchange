<?php
/**
 * Load the REST module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

use iThemes\Exchange\REST\Middleware\Autolinker;
use iThemes\Exchange\REST\Middleware\Error_Handler;
use iThemes\Exchange\REST\Middleware\Filter_By_Context;
use iThemes\Exchange\REST\Middleware\Stack;
use iThemes\Exchange\REST\Route\Cart\Carts;
use iThemes\Exchange\REST\Route\Cart\Item;
use iThemes\Exchange\REST\Route\Cart\Meta;
use iThemes\Exchange\REST\Route\Cart\Purchase;
use iThemes\Exchange\REST\Route\Cart\Shipping_Methods;
use iThemes\Exchange\REST\Route\Customer\Customer;
use iThemes\Exchange\REST\Route\Customer\Token\Serializer as TokenSerializer;
use iThemes\Exchange\REST\Route\Customer\Token\Tokens;
use iThemes\Exchange\REST\Route\Transaction\Transaction;

/**
 * Register the rest routes on libraries loaded.
 *
 * @since 1.36.0
 *
 * @return \iThemes\Exchange\REST\Manager
 */
add_action( 'rest_api_init', function () {

	$manager = get_rest_manager();

	/**
	 * Fires when routes should be registered.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Manager $manager
	 */
	do_action( 'it_exchange_register_rest_routes', $manager );

	$manager->initialize();
} );

add_action( 'it_exchange_register_rest_routes', function ( Manager $manager ) {

	$cart = new Route\Cart\Cart();
	$manager->register_route( $cart );
	$manager->register_route( new Carts( $cart ) );

	foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {
		$items_route = new Route\Cart\Items( $item_type->get_rest_serializer(), $item_type );
		$item_route  = new Route\Cart\Item( $item_type, $item_type->get_rest_serializer() );

		$manager->register_route( $items_route->set_parent( $cart ) );
		$manager->register_route( $item_route->set_parent( $items_route ) );
	}

	$shipping_methods = new Route\Cart\Shipping_Methods();
	$purchase         = new Route\Cart\Purchase( new \ITE_Gateway_Request_Factory() );
	$meta             = new Route\Cart\Meta();

	$manager->register_route( $shipping_methods->set_parent( $cart ) );
	$manager->register_route( $purchase->set_parent( $cart ) );
	$manager->register_route( $meta->set_parent( $cart ) );

	// --- Customers --- //
	$customer = new Customer();
	$manager->register_route( $customer );

	/* Tokens */
	$tokens = new Tokens( new TokenSerializer(), new \ITE_Gateway_Request_Factory() );
	$manager->register_route( $tokens->set_parent( $customer ) );

	$token = new Route\Customer\Token\Token( new TokenSerializer() );
	$manager->register_route( $token->set_parent( $tokens ) );

	// --- Transactions --- //
	$manager->register_route( new Route\Transaction\Transaction() );
} );

/**
 * Get the rest url for a given route.
 *
 * @since 1.36.0
 *
 * @param \iThemes\Exchange\REST\Route $route
 * @param array                        $path_parameters
 *
 * @return string
 */
function get_rest_url( Route $route, array $path_parameters ) {

	$manager = get_rest_manager();

	$path     = '';
	$building = $route;

	do {
		$path = $building->get_path() . $path;
	} while ( $building->has_parent() && $building = $building->get_parent() );

	$path = $manager->get_namespace() . "/v{$route->get_version()}/$path";

	$regex = '/\(\?P\<#\>.+?\)/';

	foreach ( $path_parameters as $parameter => $value ) {

		if ( ! is_scalar( $value ) && ! is_callable( array( $value, '__toString' ) ) ) {
			continue;
		}

		$path_regex = str_replace( '#', $parameter, $regex );

		$path = preg_replace( $path_regex, $value, $path );
	}

	return untrailingslashit( rest_url( $path ) );
}

/**
 * Get the REST manager.
 *
 * @since 1.36.0
 *
 * @return \iThemes\Exchange\REST\Manager
 */
function get_rest_manager() {

	static $manager;

	if ( ! $manager ) {

		$stack = new Stack();
		$stack->push( new Error_Handler( defined( 'WP_DEBUG' ) && WP_DEBUG ), 'error-handler' );
		$stack->push( new Autolinker(), 'autolinker' );
		$stack->push( new Filter_By_Context(), 'filter-by-context' );

		$manager = new Manager( 'it_exchange', $stack );
	}

	return $manager;
}

/**
 * Transform a response to an array.
 *
 * @since 1.36.0
 *
 * @param \WP_REST_Response $response
 *
 * @return array
 */
function response_to_array( \WP_REST_Response $response ) {

	$data  = (array) $response->get_data();
	$links = \WP_REST_Server::get_response_links( $response );

	if ( $links ) {
		$data['_links'] = $links;
	}

	return $data;
}