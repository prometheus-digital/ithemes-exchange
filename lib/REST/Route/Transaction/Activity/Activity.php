<?php
/**
 * Activity Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction\Activity;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Activity
 *
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Activity extends Base implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Transaction\Activity\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$t          = it_exchange_get_transaction( $request->get_param( 'transaction_id', 'URL' ) );
		$collection = new \IT_Exchange_Txn_Activity_Collection( $t, array(
			'per_page'  => $request['per_page'],
			'page'      => $request['page'],
			'is_public' => $request['public_only'] ? true : null,
			'type'      => $request['type'],
		) );

		$data = array();

		foreach ( $collection->get_activity() as $activity ) {
			$data[] = $this->serializer->serialize( $activity, $request );
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to list this transaction activity.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if (
			! $request['activity_id'] &&
			$request['public_only'] !== true &&
			! user_can( $user->wp_user, 'edit_it_transaction', $request['transaction_id'] )
		) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to list non-public transaction activity.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$t = it_exchange_get_transaction( $request->get_param( 'transaction_id', 'URL' ) );

		$builder = new \IT_Exchange_Txn_Activity_Builder( $t, 'note' );
		$builder->set_description( $request['description'] );
		$builder->set_public( (bool) $request['is_public'] );
		$builder->set_actor( new \IT_Exchange_Txn_Activity_User_Actor( wp_get_current_user() ) );

		$activity = $builder->build( it_exchange_get_txn_activity_factory() );

		if ( ! $activity ) {
			return new \WP_Error(
				'it_exchange_rest_unexpected_error',
				__( 'An unexpected error occurred while creating a new activity item.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		$response = new \WP_REST_Response( $this->serializer->serialize( $activity, $request ), \WP_Http::CREATED );
		$response->header(
			'Location',
			\iThemes\Exchange\REST\get_rest_url(
				$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Transaction\Activity\Item' ),
				array(
					'transaction_id' => $t->get_ID(),
					'activity_id'    => $activity->get_ID()
				)
			)
		);

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! user_can( $user->wp_user, 'edit_it_transaction', $request['transaction_id'] ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to create transaction activity.', 'it-l10n-ithemes-exchange' ),
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
	public function get_path() { return 'activity/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'page'        => array(
				'description' => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'    => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 50,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'public_only' => array(
				'description' => __( 'The customer whose transactions should be retrieved.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'boolean',
				'default'     => false,
			),
			'icon_size'   => array(
				'description' => __( 'The size of the activity icons.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 96,
				'minimum'     => 64,
				'maximum'     => 256,
			),
			'type'        => array(
				'description' => __( 'The activity type.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'default'     => 'any',
				'enum'        => array_merge( array_keys( it_exchange_get_txn_activity_factory()->get_types() ), array( 'any' ) ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
