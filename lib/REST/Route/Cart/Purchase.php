<?php
/**
 * Purchase Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use ITE_Gateway_Purchase_Request;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\VariableSchema;

/**
 * Class Purchase
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Purchase extends Base implements Getable, Postable, VariableSchema {

	/** @var \ITE_Gateway_Request_Factory */
	private $request_factory;

	/** @var PurchaseSerializer */
	private $serializer;

	/**
	 * Purchase constructor.
	 *
	 * @param \ITE_Gateway_Request_Factory $request_factory
	 * @param PurchaseSerializer           $serializer
	 */
	public function __construct( \ITE_Gateway_Request_Factory $request_factory, PurchaseSerializer $serializer ) {
		$this->request_factory = $request_factory;
		$this->serializer      = $serializer;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$cart = $request->get_cart();

		$cart->prepare_for_purchase();

		$purchase_request = $this->request_factory->make( 'purchase', array(
			'cart'        => $cart,
			'redirect_to' => $request['redirect_to'],
		) );

		$data = array();

		foreach ( it_exchange_get_available_transaction_methods_for_cart( $cart ) as $gateway ) {
			if ( $handler = $gateway->get_handler_for( $purchase_request ) ) {
				$data[] = $this->serializer->serialize( $handler, $purchase_request );
			}
		}

		return new \WP_REST_Response( $data );
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

		$token = (int) $request['token'];

		if ( $token && ! current_user_can( 'it_use_payment_token', $token ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'You cannot use that payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
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
	public function get_query_args() {
		return array(
			'redirect_to' => array(
				'type'        => 'string',
				'format'      => 'uri',
				'description' => __( 'A location to redirect the customer to after purchase. Useful for redirect methods.', 'it-l10n-ithemes-exchange' ),
				'arg_options' => array(
					'sanitize_callback' => 'wp_sanitize_redirect',
					'validate_callback' => function ( $param ) {
						return wp_validate_redirect( $param );
					},
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }

	/**
	 * @inheritDoc
	 */
	public function schema_varies_on() { return array( 'POST' ); }

	/**
	 * @inheritDoc
	 */
	public function get_schema_for_method( $method ) {
		if ( $method !== 'POST' ) {
			throw new \InvalidArgumentException( 'Invalid method.' );
		}

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart-purchase',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'type'        => 'string',
					'description' => __( 'Purchase gateway slug.', 'it-l10n-ithemes-exchange' ),
				),
				'nonce'       => array(
					'type'        => 'string',
					'description' => __( 'A token unique to this gateway that is required to complete the purchase.', 'it-l10n-ithemes-exchange' ),
				),
				'redirect_to' => array(
					'type'        => 'string',
					'format'      => 'uri',
					'description' => __( 'A location to redirect the customer to after purchase. Useful for redirect methods.', 'it-l10n-ithemes-exchange' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_sanitize_redirect',
						'validate_callback' => function ( $param ) {
							return wp_validate_redirect( $param );
						},
					),
				),
				'card'        => array( '$ref' => \iThemes\Exchange\REST\url_for_schema( 'card' ) ),
				'token'       => array(
					'type'        => 'integer',
					'min'         => 1,
					'description' => __( 'Payment token to use for payment.', 'it-l10n-ithemes-exchange' ),
				),
				'tokenize'    => array(
					'description' => __( 'Payment info to auto-tokenize and then use for payment.', 'it-l10n-ithemes-exchange' ),
					'oneOf'       => array(
						array(
							'type'        => 'string',
							'description' => __( 'Token provided by the payment processor. For example, a Stripe.js token.', 'it-l10n-ithemes-exchange' )
						),
						array( '$ref' => \iThemes\Exchange\REST\url_for_schema( 'card' ) ),
					),
				)
			),
			'oneOf'      => array(
				// Set it up so that only one card, token, or tokenize option may be used in conjunction.
				// May also pass none, for things like Offline Payments
				array( 'required' => array( 'id', 'nonce', 'card' ) ),
				array( 'required' => array( 'id', 'nonce', 'token' ) ),
				array( 'required' => array( 'id', 'nonce', 'tokenize' ) ),
				array( 'required' => array( 'id', 'nonce' ) ),
			),
		);
	}
}
