<?php
/**
 * Single Item Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Item
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Item extends Base implements Getable, Putable, Deletable {

	/** @var \ITE_Line_Item_Type */
	protected $type;

	/** @var \iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer */
	protected $serializer;

	/**
	 * Item constructor.
	 *
	 * @param \ITE_Line_Item_Type                                  $type
	 * @param \iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer $serializer
	 */
	public function __construct( \ITE_Line_Item_Type $type, Item_Serializer $serializer ) {
		$this->type       = $type;
		$this->serializer = $serializer;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$item = $cart->get_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) );

		$response = new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
		$response->add_link( 'cart', r\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Cart\Cart' ),
			array( 'cart_id' => $cart->get_id() )
		) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {
		return $this->exists_check( $request, $scope );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$item = $cart->get_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) );

		if ( $item instanceof \ITE_Quantity_Modifiable_Item && $item->is_quantity_modifiable() ) {
			if ( isset( $request['quantity'], $request['quantity']['selected'] ) ) {
				$quantity = $request['quantity']['selected'];
			} elseif ( isset( $request['quantity'] ) && is_numeric( $request['quantity'] ) ) {
				$quantity = $request['quantity'];
			} else {
				$quantity = $item->get_quantity();
			}

			if ( (int) $quantity !== (int) $item->get_quantity() ) {
				$item->set_quantity( $quantity );
				$cart->save_item( $item );
			}
		}

		return new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return Errors::edit_line_item_not_supported( $this->type->get_type() );
		}

		return $this->exists_check( $request, $scope );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$cart->remove_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return Errors::delete_line_item_not_supported( $this->type->get_type() );
		}

		return $this->exists_check( $request, $scope );
	}

	/**
	 * Perform a permission check.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param AuthScope                      $scope
	 *
	 * @return bool|\WP_Error
	 */
	protected function exists_check( Request $request, AuthScope $scope ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );

		if ( ! $cart || ! $cart->get_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) ) ) {
			return Errors::not_found();
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
	public function get_path() { return '(?P<item_id>[\w\-]+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }

	/**
	 * Get the item type this endpoint represents.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type
	 */
	public function get_type() { return $this->type; }
}
