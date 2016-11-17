<?php
/**
 * Purchase Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use ITE_Gateway_Purchase_Request_Interface;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Purchase
 *
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
	 * @since 2.0.0
	 *
	 * @param \ITE_Purchase_Request_Handler          $handler
	 * @param ITE_Gateway_Purchase_Request_Interface $request
	 *
	 * @return array
	 */
	protected function get_data_for_handler( \ITE_Purchase_Request_Handler $handler, ITE_Gateway_Purchase_Request_Interface $request ) {

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

		if ( $request['redirect_to'] && ! wp_validate_redirect( wp_sanitize_redirect( $request['redirect_to'] ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_redirect_to',
				__( 'Invalid redirect to URL.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$token = (int) $request['token'];

		if ( $token && ! current_user_can( 'it_use_payment_token', $token ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'You cannot use that payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		try {
			$purchase_request = $this->request_factory->make( 'purchase', array(
				'cart'        => $cart,
				'nonce'       => $request['nonce'],
				'card'        => $request['card'],
				'token'       => $token,
				'tokenize'    => $request['tokenize'],
				'redirect_to' => $request['redirect_to']
			) );
		} catch ( \InvalidArgumentException $e ) {
			return new \WP_Error(
				'rest_invalid_param',
				$e->getMessage(),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$gateway = \ITE_Gateways::get( $request['id'] );
		$handler = $gateway->get_handler_for( $purchase_request );

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
