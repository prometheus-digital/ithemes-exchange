<?php
/**
 * Purchase Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Purchase
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Purchase extends Base implements Getable, Postable {

	/** @var \ITE_Gateway_Request_Factory */
	private $request_factory;

	/**
	 * Purchase constructor.
	 *
	 * @param \ITE_Gateway_Request_Factory $request_factory
	 */
	public function __construct( \ITE_Gateway_Request_Factory $request_factory ) {
		$this->request_factory = $request_factory;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$cart = $request->get_cart();

		$cart->prepare_for_purchase();

		$purchase_request = $this->request_factory->make( 'purchase', array( 'cart' => $cart ) );

		$data = array();

		foreach ( it_exchange_get_available_transaction_methods_for_cart( $cart ) as $gateway ) {
			if ( $handler = $gateway->get_handler_for( $purchase_request ) ) {
				$data[] = $this->get_data_for_handler( $handler, $purchase_request );
			}
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * Get the data for a handler.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Purchase_Request_Handler $handler
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	protected function get_data_for_handler( \ITE_Purchase_Request_Handler $handler, \ITE_Gateway_Purchase_Request $request ) {

		$data = array(
			'id'     => $handler->get_gateway()->get_slug(),
			'name'   => $handler->get_gateway()->get_name(),
			'label'  => $handler->get_payment_button_label(),
			'nonce'  => $handler->get_nonce(),
			'method' => $handler->get_data_for_REST( $request ),
		);

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$cart = $request->get_cart();

		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		$purchase_request = $this->request_factory->make( 'purchase', array(
			'cart'     => $cart,
			'nonce'    => $request['nonce'],
			'card'     => $request['card'],
			'token'    => $request['token'],
			'tokenize' => $request['tokenize'],
		) );
		$gateway          = \ITE_Gateways::get( $request['id'] );
		$handler          = $gateway->get_handler_for( $purchase_request );

		if ( ! $handler ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_gateway',
				__( 'Invalid gateway.', 'it-l10n-ithemes-exchange' ),
				400
			);
		}

		$transaction = $handler->handle( $purchase_request );

		if ( ! $transaction ) {
			return new \WP_Error(
				'it_exchange_rest_unexpected_gateway_error',
				__( 'An unexpected error occurred while processing the purchase.', 'it-l10n-ithemes-exchange' ),
				500
			);
		}

		$route = $this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Transaction\Transaction' );
		$url   = \iThemes\Exchange\REST\get_rest_url( $route, array( 'transaction_id' => $transaction->ID ) );

		$response = new \WP_REST_Response();
		$response->set_status( \WP_Http::SEE_OTHER );
		$response->header( 'Location', $url );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'purchase/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart-purchase',
			'type'       => 'object',
			'properties' => array()
		);
	}
}