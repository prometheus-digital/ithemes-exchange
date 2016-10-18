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
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Item
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Item extends Base implements Getable, Putable, Deletable {

	/** @var \ITE_Line_Item_Type */
	protected $type;

	/** @var \iThemes\Exchange\REST\Route\Cart\Item_Serializer */
	protected $serializer;

	/**
	 * Item constructor.
	 *
	 * @param \ITE_Line_Item_Type                               $type
	 * @param \iThemes\Exchange\REST\Route\Cart\Item_Serializer $serializer
	 */
	public function __construct( \ITE_Line_Item_Type $type, Item_Serializer $serializer ) {
		$this->type       = $type;
		$this->serializer = $serializer;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();

		$cart = it_exchange_get_cart( $url_params['id'] );
		$item = $cart->get_item( $this->type->get_type(), $url_params['item'] );

		return new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
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

		return new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
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
	public function user_can_delete( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
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
	 * @param \WP_REST_Request      $request
	 * @param \IT_Exchange_Customer $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {

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
	public function get_schema() { return $this->serializer->get_schema(); }
}