<?php
/**
 * Load the REST module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

use iThemes\Exchange\REST\Middleware\Autolinker;
use iThemes\Exchange\REST\Middleware\Cart_Decorator;
use iThemes\Exchange\REST\Middleware\Cart_Feedback;
use iThemes\Exchange\REST\Middleware\Error_Handler;
use iThemes\Exchange\REST\Middleware\Filter_By_Context;
use iThemes\Exchange\REST\Middleware\Stack;
use iThemes\Exchange\REST\Helpers\ContextFilterer;
use iThemes\Exchange\REST\Route\Cart\Carts;
use iThemes\Exchange\REST\Route\Cart\Item;
use iThemes\Exchange\REST\Route\Cart\Meta;
use iThemes\Exchange\REST\Route\Cart\Purchase;
use iThemes\Exchange\REST\Route\Cart\Shipping_Methods;
use iThemes\Exchange\REST\Route\Cart\Types;
use iThemes\Exchange\REST\Route\Cart\TypeSerializer;
use iThemes\Exchange\REST\Route\Customer\Address\Address;
use iThemes\Exchange\REST\Route\Customer\Address\Addresses;
use iThemes\Exchange\REST\Route\Customer\Address\Serializer as AddressSerializer;
use iThemes\Exchange\REST\Route\Customer\Customer;
use iThemes\Exchange\REST\Route\Customer\Serializer as CustomerSerializer;
use iThemes\Exchange\REST\Route\Customer\Token\Serializer as TokenSerializer;
use iThemes\Exchange\REST\Route\Customer\Token\Tokens;
use iThemes\Exchange\REST\Route\Transaction\Activity\Serializer as ActivitySerializer;
use iThemes\Exchange\REST\Route\Transaction\Refunds\Serializer as RefundSerializer;
use iThemes\Exchange\REST\Route\Transaction\Serializer as TransactionSerializer;
use iThemes\Exchange\REST\Route\Transaction\Transaction;

/**
 * Register the rest routes on libraries loaded.
 *
 * @since 2.0.0
 *
 * @return \iThemes\Exchange\REST\Manager
 */
add_action( 'rest_api_init', function () {

	$manager = get_rest_manager();

	/**
	 * Fires when routes should be registered.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Manager $manager
	 */
	do_action( 'it_exchange_register_rest_routes', $manager );

	$manager->initialize();
} );

add_action( 'it_exchange_register_rest_routes', function ( Manager $manager ) {

	$manager->register_route( new Route\Info() );
	$manager->register_route( new Route\Dataset() );

	$cart  = new Route\Cart\Cart( new Route\Cart\Serializer() );
	$carts = new Carts( $cart );

	$manager->register_route( $cart );
	$manager->register_route( $carts );

	$item_types = new Route\Cart\Types( new TypeSerializer() );
	$manager->register_route( $item_types );

	foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {
		$items_route = new Route\Cart\Items( $item_type->get_rest_serializer(), $item_type );
		$item_route  = new Route\Cart\Item( $item_type, $item_type->get_rest_serializer() );

		$manager->register_route( $items_route->set_parent( $cart ) );
		$manager->register_route( $item_route->set_parent( $items_route ) );
	}

	$shipping_methods = new Route\Cart\Shipping_Methods();
	$purchase         = new Route\Cart\Purchase( new \ITE_Gateway_Request_Factory(), new Route\Cart\PurchaseSerializer() );
	$meta             = new Route\Cart\Meta();

	$manager->register_route( $shipping_methods->set_parent( $cart ) );
	$manager->register_route( $purchase->set_parent( $cart ) );
	$manager->register_route( $meta->set_parent( $cart ) );

	// --- Customers --- //
	$customer = new Customer( new CustomerSerializer() );
	$manager->register_route( $customer );

	/* Addresses */
	$serializer = new AddressSerializer();
	$addresses  = new Addresses( $serializer );
	$manager->register_route( $addresses->set_parent( $customer ) );

	$address = new Address( $serializer );
	$manager->register_route( $address->set_parent( $addresses ) );

	/* Tokens */
	$tokens = new Tokens( new TokenSerializer(), new \ITE_Gateway_Request_Factory() );
	$manager->register_route( $tokens->set_parent( $customer ) );

	$token = new Route\Customer\Token\Token( new TokenSerializer() );
	$manager->register_route( $token->set_parent( $tokens ) );

	// --- Transactions --- //
	$transactions = new Route\Transaction\Transactions( new TransactionSerializer() );
	$manager->register_route( $transactions );

	$transaction = new Route\Transaction\Transaction( new TransactionSerializer() );
	$manager->register_route( $transaction->set_parent( $transactions ) );

	$send_receipt = new Route\Transaction\Send_Receipt( it_exchange_email_notifications() );
	$manager->register_route( $send_receipt->set_parent( $transaction ) );

	/* Activity */
	$activity = new Route\Transaction\Activity\Activity( new ActivitySerializer() );
	$manager->register_route( $activity->set_parent( $transaction ) );

	$activity_item = new Route\Transaction\Activity\Item( new ActivitySerializer() );
	$manager->register_route( $activity_item->set_parent( $activity ) );

	/* Refunds */
	$refunds = new Route\Transaction\Refunds\Refunds( new RefundSerializer(), new \ITE_Gateway_Request_Factory() );
	$manager->register_route( $refunds->set_parent( $transaction ) );

	$refund = new Route\Transaction\Refunds\Refund( new RefundSerializer() );
	$manager->register_route( $refund->set_parent( $refunds ) );

	// --- Products --- //
	$serializer = new Route\Product\Serializer();
	$products   = new Route\Product\Products( $serializer );
	$manager->register_route( $products );

	$product = new Route\Product\Product( $serializer );
	$manager->register_route( $product->set_parent( $products ) );
} );

/**
 * Get the rest url for a given route.
 *
 * @since 2.0.0
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
 * @since 2.0.0
 *
 * @return \iThemes\Exchange\REST\Manager
 */
function get_rest_manager() {

	static $manager = null;

	if ( ! $manager ) {

		$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		/**
		 * Filter whether the REST API is in debug mode or not.
		 *
		 * When in debug mode Exception messages and stack traces will be included in the error response.
		 *
		 * @since 2.0.0
		 *
		 * @param bool $is_debug
		 */
		$is_debug = apply_filters( 'it_exchange_is_rest_in_debug_mode', $is_debug );

		$stack = new Stack();
		$stack->push( new Error_Handler( $is_debug ), 'error-handler' );
		$stack->push( new Autolinker(), 'autolinker' );
		$stack->push( new Filter_By_Context( new ContextFilterer() ), 'filter-by-context' );
		$stack->push( new Cart_Feedback(), 'cart-feedback' );

		$manager = new Manager( 'it_exchange', $stack, array(
			'card' => array(
				'title'                => 'card',
				'type'                 => 'object',
				'additionalProperties' => false,
				'properties'           => array(
					'number' => array(
						'type'        => 'string',
						'description' => __( 'Card number.', 'it-l10n-ithemes-exchange' ),
						'required'    => true,
					),
					'year'   => array(
						'type'        => 'integer',
						'description' => __( 'Card expiration year', 'it-l10n-ithemes-exchange' ),
						'required'    => true,
					),
					'month'  => array(
						'type'        => 'integer',
						'description' => __( 'Card expiration month', 'it-l10n-ithemes-exchange' ),
						'required'    => true,
						'min'         => 1,
						'max'         => 12,
					),
					'cvc'    => array(
						'type'        => 'string',
						'description' => __( 'Card security code.', 'it-l10n-ithemes-exchange' ),
						'required'    => false,
						'min'         => 3,
						'max'         => 4,
					),
					'name'   => array(
						'type'        => 'string',
						'description' => __( 'Card holder name.', 'it-l10n-ithemes-exchange' ),
					),
				),
			)
		) );
	}

	return $manager;
}

/**
 * Transform a response to an array.
 *
 * @since 2.0.0
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

/**
 * Get the URL for a schema title.
 *
 * @since 2.0.0
 *
 * @param string $title
 *
 * @return string
 */
function url_for_schema( $title ) {
	return "https://api.ithemes.com/exchange/schemas/$title";
}

/**
 * Format a date to follow RFC339.
 *
 * @since 2.0.0
 *
 * @param \DateTime|string|int $date
 *
 * @return string
 */
function format_rfc339( $date ) {

	if ( is_string( $date ) ) {
		$datetime = new \DateTime( $date, new \DateTimeZone( 'UTC' ) );
	} elseif ( is_numeric( $date ) ) {
		$datetime = new \DateTime( "@{$date}", new \DateTimeZone( 'UTC' ) );
	} elseif ( $date instanceof \DateTime ) {
		$datetime = $date;
	} else {
		throw new \InvalidArgumentException();
	}

	return $datetime->format( \DateTime::RFC3339 );
}