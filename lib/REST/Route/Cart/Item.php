<?php
/**
 * Single Item Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;

/**
 * Class Item
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Item implements Getable, Putable, Deletable {

	/** @var \ITE_Line_Item_Type */
	protected $type;

	/** @var Items */
	protected $collection_route;

	/**
	 * Item constructor.
	 *
	 * @param \ITE_Line_Item_Type                     $type
	 * @param \iThemes\Exchange\REST\Route\Cart\Items $collection_route
	 */
	public function __construct( \ITE_Line_Item_Type $type, Items $collection_route ) {
		$this->type             = $type;
		$this->collection_route = $collection_route;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();

		$cart = it_exchange_get_cart( $url_params['id'] );
		$item = $cart->get_item( $this->type->get_type(), $url_params['item'] );

		if ( ! $item ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_item',
				__( 'Invalid item.', 'it-l10n-ithemes-exchange' ),
				404
			);
		}

		$response = $this->collection_route->prepare_item_for_response( $item, $request );
		$response->add_link( 'items', r\get_rest_url( $this->collection_route, array( 'id' => $url_params['id'] ) ) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \WP_User $user ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();

		$cart = it_exchange_get_cart( $url_params['id'] );
		$item = $cart->get_item( $this->type->get_type(), $url_params['item'] );

		if (
			isset( $request['quantity'], $request['quantity']['selected'] ) &&
			$item instanceof \ITE_Quantity_Modifiable_Item &&
			$item->is_quantity_modifiable()
		) {
			$item->set_quantity( $request['quantity']['selected'] );
		}

		$response = $this->collection_route->prepare_item_for_response( $item, $request );
		$response->add_link( 'items', r\get_rest_url( $this->collection_route, array( 'id' => $url_params['id'] ) ) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( \WP_REST_Request $request, \WP_User $user ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return new \WP_Error(
				'it_exchange_rest_non_editable_item',
				__( 'Item not editable in REST.', 'it-l10n-ithemes-exchange' ),
				400
			);
		}

		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();

		$cart = it_exchange_get_cart( $url_params['id'] );
		$cart->remove_item( $this->type->get_type(), $url_params['item'] );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( \WP_REST_Request $request, \WP_User $user ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return new \WP_Error(
				'it_exchange_rest_non_editable_item',
				__( 'Item not editable in REST.', 'it-l10n-ithemes-exchange' ),
				400
			);
		}

		return $this->permission_check( $request, $user );
	}

	/**
	 * Perform a permission check.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request $request
	 * @param \WP_User         $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( \WP_REST_Request $request, \WP_User $user ) {

		$url_params = $request->get_url_params();
		$cart       = it_exchange_get_cart( $url_params['id'] );

		if ( ! $cart->get_item( $this->type->get_type(), $url_params['item'] ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_item',
				__( 'Invalid item id.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 404 )
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
	public function get_path() { return '(?P<item>[\w\-]+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function has_parent() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_parent() {
		return $this->collection_route;
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return $this->collection_route->get_schema();
	}
}