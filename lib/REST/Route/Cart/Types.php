<?php
/**
 * Item Types.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Types
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Types extends Base implements Getable {

	/** @var TypeSerializer */
	private $serializer;

	/**
	 * Types constructor.
	 *
	 * @param TypeSerializer $serializer
	 */
	public function __construct( TypeSerializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var Items[] $item_routes */
		$item_routes = $this->get_manager()->get_routes_by_class( 'iThemes\Exchange\REST\Route\Cart\Items' );
		$id_to_route = array();

		foreach ( $item_routes as $item_route ) {
			$id_to_route[ $item_route->get_type()->get_type() ] = $item_route;
		}

		$data = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {

			$href = \iThemes\Exchange\REST\get_rest_url( $id_to_route[ $item_type->get_type() ], array( 'cart_id' => '{cart_id}' ) );

			$serialized = $this->serializer->serialize( $item_type );

			$serialized['_links']['https://api.w.org/items'][] = array(
				'href' => $href,
			);

			$data[] = $serialized;
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'cart_item_types/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
