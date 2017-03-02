<?php
/**
 * Refunds Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction\Refunds;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Refunds
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction
 */
class Refunds extends Base implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/** @var \ITE_Gateway_Request_Factory */
	private $request_factory;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Transaction\Refunds\Serializer $serializer
	 * @param \ITE_Gateway_Request_Factory                                   $request_factory
	 */
	public function __construct( Serializer $serializer, \ITE_Gateway_Request_Factory $request_factory ) {
		$this->serializer      = $serializer;
		$this->request_factory = $request_factory;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \IT_Exchange_Transaction $transaction */
		$transaction = $request->get_route_object( 'transaction_id' );
		$refunds     = $transaction->refunds;

		$data = array();

		foreach ( $refunds as $refund ) {
			$serialized = $this->serializer->serialize( $refund );
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
	public function user_can_get( Request $request, AuthScope $scope ) {

		if ( ! $scope->can( 'it_list_transaction_refunds', $request->get_route_object( 'transaction_id' ) ) ) {
			return Errors::cannot_list();
		}

		if ( $request['context'] === 'edit' && ! $scope->can( 'it_edit_refunds' ) ) {
			return Errors::forbidden_context( 'edit' );
		}

		return Manager::AUTH_STOP_CASCADE;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		/** @var \IT_Exchange_Transaction $transaction */
		$transaction = $request->get_route_object( 'transaction_id' );

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
		} catch ( \Exception $e ) {

		}

		if ( empty( $refund ) || ! empty( $e ) ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_refund',
				__( 'Unable to process refund.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$response = new \WP_REST_Response( $this->serializer->serialize( $refund ),	\WP_Http::CREATED );

		$location = \iThemes\Exchange\REST\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Transaction\Refunds\Refund' ),
			array( 'transaction_id' => $transaction->get_ID(), 'refund_id' => $refund->ID )
		);
		$response->header( 'Location', $location );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {

		if ( ! $scope->can( 'it_create_refunds', $request->get_route_object( 'transaction_id' ) ) ) {
			return Errors::cannot_create();
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
