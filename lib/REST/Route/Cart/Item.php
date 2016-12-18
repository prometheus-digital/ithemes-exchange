<?php
/**
 * Single Item Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Item
 *
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
	public function handle_get( Request $request ) {

		$cart = $request->get_cart();
		$item = $cart->get_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) );

		$response = new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
		$response->add_link( 'cart', r\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Cart\Cart' ),
			array( 'cart_id' => $cart->get_id() )
		) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$cart = $request->get_cart();
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
				$cart->get_repository()->save( $item );
			}
		}

		return new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
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
	public function handle_delete( Request $request ) {

		$cart = $request->get_cart();
		$cart->remove_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {
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
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer          $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( Request $request, \IT_Exchange_Customer $user = null ) {

		$cart = it_exchange_get_cart( $request->get_param( 'cart_id', 'URL' ) );

		if ( ! $cart->get_item( $this->type->get_type(), $request->get_param( 'item_id', 'URL' ) ) ) {
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
