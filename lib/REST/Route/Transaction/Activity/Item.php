<?php
/**
 * Single Activity Item Endpoint.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction\Activity;

use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Item
 * @package iThemes\Exchange\REST\Route\Transaction\Activity
 */
class Item extends Base implements Getable, Deletable {

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

		$item = it_exchange_get_txn_activity( $request->get_param( 'activity_id', 'URL' ) );

		return new \WP_REST_Response( $this->serializer->serialize( $item, $request ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$item = it_exchange_get_txn_activity( $request->get_param( 'activity_id', 'URL' ) );

		if ( ! $item ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_activity',
				__( 'Invalid activity item.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! $item->is_public() && ! user_can( $user->wp_user, 'edit_it_transaction', $item->get_transaction()->ID ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this activity item.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		$item = it_exchange_get_txn_activity( $request->get_param( 'activity_id', 'URL' ) );
		$item->delete();

		return new \WP_REST_Response( '', \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {

		$item = it_exchange_get_txn_activity( $request->get_param( 'activity_id', 'URL' ) );

		if ( ! $item ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_activity',
				__( 'Invalid activity item.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! user_can( $user->wp_user, 'edit_it_transaction', $item->get_transaction()->ID ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this activity item.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
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
	public function get_path() { return '(?P<activity_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'icon_size' => array(
				'description'       => __( 'The size of the activity icons.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'integer',
				'default'           => 96,
				'minimum'           => 64,
				'maximum'           => 256,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}