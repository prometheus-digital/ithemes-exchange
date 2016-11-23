<?php
/**
 * Refund Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction\Refunds;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Transaction
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Refund extends Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Transaction\Refunds\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$refund = \ITE_Refund::get( $request->get_param( 'refund_id', 'URL' ) );

		$user     = it_exchange_get_current_customer();
		$response = new \WP_REST_Response( $this->serializer->serialize( $refund, $user ) );

		foreach ( $this->serializer->generate_links( $refund, $this->get_manager() ) as $rel => $links ) {
			foreach ( $links as $link ) {
				$response->add_link( $rel, $link['href'], $link );
			}
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$transaction_id = $request->get_param( 'transaction_id', 'URL' );
		$refund         = \ITE_Refund::get( $request->get_param( 'refund_id', 'URL' ) );

		if ( ! $refund || ! $refund->transaction || $refund->transaction->ID !== (int) $transaction_id ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this refund.', 'it-l10n-ithemes-exchange' ),
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
	public function get_path() { return '(?P<refund_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
