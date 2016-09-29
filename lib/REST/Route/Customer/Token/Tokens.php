<?php
/**
 * Tokens route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Token;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;

/**
 * Class Tokens
 * @package iThemes\Exchange\REST\Customer\Token
 */
class Tokens implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/** @var \ITE_Gateway_Request_Factory */
	private $request_factory;

	/** @var Token */
	private $token;

	/**
	 * Tokens constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Customer\Token\Serializer $serializer
	 * @param \ITE_Gateway_Request_Factory                           $request_factory
	 * @param \iThemes\Exchange\REST\Route\Customer\Token\Token      $token
	 */
	public function __construct( Serializer $serializer, \ITE_Gateway_Request_Factory $request_factory, Token $token ) {
		$this->serializer      = $serializer;
		$this->request_factory = $request_factory;
		$this->token           = $token;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();
		$customer   = it_exchange_get_customer( $url_params['customer_id'] );

		$tokens = $customer->get_tokens( $request['gateway'] );
		$data   = array_map( array( $this->serializer, 'serialize' ), $tokens->toArray() );

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( \WP_REST_Request $request ) {

		$gateway = \ITE_Gateways::get( $request['gateway'] );

		if ( ! $gateway || ! $gateway->can_handle( 'tokenize' ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_gateway',
				__( 'Invalid gateway.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$tokenize = $this->request_factory->make( 'tokenize', array(
			'customer' => $request['customer_id'],
			'source'   => $request['source'],
			'label'    => $request['label'],
			'primary'  => $request['primary'],
		) );

		$token = $gateway->get_handler_for( $tokenize )->handle( $tokenize );

		return new \WP_REST_Response( $this->serializer->serialize( $token ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * Perform a permissions check.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request           $request
	 * @param \IT_Exchange_Customer|null $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permissions_check( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
			);
		}

		$url_params = $request->get_url_params();
		$customer   = it_exchange_get_customer( $url_params['customer_id'] );

		if ( ! $customer ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_customer',
				__( 'Invalid customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! user_can( $user->wp_user, 'edit_user', $customer->ID ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'customers/(?P<customer_id>\d+)/tokens/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'context' => array(
				'description'       => __( 'Scope under which the request is made; determines fields present in response.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
				'default'           => 'view',
				'enum'              => array( 'view', 'edit' )
			),
			'gateway' => array(
				'description'       => __( 'Gateway the payment token belongs to.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'string',
				'validate_callback' => function ( $value ) {
					return ( $g = \ITE_Gateways::get( $value ) ) && $g->can_handle( 'tokenize' );
				},
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return $this->token->get_schema();
	}

	/**
	 * @inheritDoc
	 */
	public function has_parent() { return false; }

	/**
	 * @inheritDoc
	 */
	public function get_parent() {
		throw new \UnexpectedValueException( "No parent exists for {$this->get_path()}" );
	}
}