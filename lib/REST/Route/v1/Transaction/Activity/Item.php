<?php
/**
 * Single Activity Item Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction\Activity;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Item
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction\Activity
 */
class Item extends Base implements Getable, Deletable, RouteObjectExpandable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Transaction\Activity\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \IT_Exchange_Txn_Activity $item */
		$item = $request->get_route_object( 'activity_id' );

		return new \WP_REST_Response( $this->serializer->serialize( $item, $request ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		/** @var \IT_Exchange_Txn_Activity $item */
		$item = $request->get_route_object( 'activity_id' );

		if ( ! $this->check_exists( $request ) ) {
			return Errors::not_found();
		}

		if ( $item->is_public() && ! $scope->can( 'read_it_transaction', $item->get_transaction() ) ) {
			return Errors::cannot_view();
		}

		if ( ! $item->is_public() && ! $scope->can( 'edit_it_transaction', $item->get_transaction() ) ) {
			return Errors::cannot_view();
		}

		if ( $request['context'] === 'edit' && ! $scope->can( 'edit_it_transaction', $item->get_transaction() ) ) {
			return Errors::forbidden_context( 'edit' );
		}

		return Manager::AUTH_STOP_CASCADE;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		/** @var \IT_Exchange_Txn_Activity $item */
		$item = $request->get_route_object( 'activity_id' );
		$item->delete();

		return new \WP_REST_Response( '', \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {

		/** @var \IT_Exchange_Txn_Activity $item */
		$item = $request->get_route_object( 'activity_id' );

		if ( ! $this->check_exists( $request ) ) {
			return Errors::not_found();
		}

		if ( ! $scope->can( 'edit_it_transaction', $item->get_transaction() ) ) {
			return Errors::cannot_delete();
		}

		return Manager::AUTH_STOP_CASCADE;
	}

	/**
	 * Check that the activity item exists.
	 *
	 * @since 2.0.0
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	protected function check_exists( Request $request ) {
		/** @var \IT_Exchange_Txn_Activity $item */
		$item = $request->get_route_object( 'activity_id' );

		return $item && $item->get_transaction() && $item->get_transaction()->get_ID() === (int) $request->get_param( 'transaction_id', 'URL' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<activity_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'activity_id' => 'it_exchange_get_txn_activity' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'icon_size' => array(
				'description' => __( 'The size of the activity icons.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 96,
				'minimum'     => 64,
				'maximum'     => 256,
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
