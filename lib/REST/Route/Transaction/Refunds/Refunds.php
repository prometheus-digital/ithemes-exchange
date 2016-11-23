<?php
/**
 * Refunds Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction\Refunds;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Refunds
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Refunds extends Base implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/** @var \ITE_Gateway_Request_Factory */
	private $request_factory;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Transaction\Refunds\Serializer $serializer
	 * @param \ITE_Gateway_Request_Factory                                $request_factory
	 */
	public function __construct( Serializer $serializer, \ITE_Gateway_Request_Factory $request_factory ) {
		$this->serializer      = $serializer;
		$this->request_factory = $request_factory;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$transaction = it_exchange_get_transaction( $request->get_param( 'transaction_id', 'URL' ) );
		$refunds     = $transaction->refunds;

		$user = it_exchange_get_current_customer();
		$data = array();

		foreach ( $refunds as $refund ) {
			$serialized = $this->serializer->serialize( $refund, $user );
			$links      = $this->serializer->generate_links( $refund, $this->get_manager() );

			foreach ( $links as $rel => $rel_links ) {
				$serialized['_links'][ $rel ] = array();

				foreach ( $rel_links as $link ) {
					$serialized['_links'][ $rel ][] = $link;
				}
			}

			$data[] = $serialized;
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$transaction = it_exchange_get_transaction( $request->get_param( 'transaction_id', 'URL' ) );

		$gateway = \ITE_Gateways::get( $transaction->get_method() );

		if ( ! it_exchange_transaction_can_be_refunded( $transaction ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_transaction_for_refund',
				__( 'This transaction is not valid for refund.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		$refund_request = $this->request_factory->make( 'refund', array(
			'transaction' => $transaction->get_ID(),
			'amount'      => $request['amount'],
			'reason'      => $request['reason'],
		) );
		$handler        = $gateway->get_handler_for( $refund_request );

		try {
			$refund = $handler->handle( $refund_request );
		}
		catch ( \Exception $e ) {

		}

		if ( empty( $refund ) || ! empty( $e ) ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_refund',
				__( 'Unable to process refund.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$response = new \WP_REST_Response(
			$this->serializer->serialize( $refund, it_exchange_get_current_customer() ),
			\WP_Http::CREATED
		);

		$location = \iThemes\Exchange\REST\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Transaction\Refunds\Refund' ),
			array( 'transaction_id' => $transaction->get_ID(), 'refund_id' => $refund->ID )
		);
		$response->header( 'Location', $location );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * Perform a permissions request.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer|null     $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permissions_check( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user || ! user_can( $user->wp_user, 'edit_it_transaction', $request->get_param( 'transaction_id', 'URL' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( "Sorry, you are not allowed to view this transaction's refunds.", 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
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
	public function get_path() { return 'refunds/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
