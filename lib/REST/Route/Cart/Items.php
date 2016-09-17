<?php
/**
 * Route to return line items.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;

/**
 * Class Cart
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Items implements Getable, Postable, Deletable {

	/** @var Item_Serializer */
	protected $serializer;

	/** @var \ITE_Line_Item_Type */
	protected $type;

	/**
	 * Cart constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Cart\Item_Serializer $serializer
	 * @param \ITE_Line_Item_Type                               $type
	 */
	public function __construct( Item_Serializer $serializer, \ITE_Line_Item_Type $type ) {
		$this->serializer = $serializer;
		$this->type       = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {
		return $this->prepare_collection_for_response(
			it_exchange_get_cart( $request['id'] )->get_items( $this->type->get_type() ),
			$request
		);
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $this->type->is_show_in_rest() ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_type',
				__( 'Invalid line item type.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 404 )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( \WP_REST_Request $request ) {

		$id = $this->type->create_from_request( $request );

		if ( $id ) {
			return $this->prepare_collection_for_response(
				it_exchange_get_cart( $request['id'] )->get_items( $this->type->get_type() ),
				$request
			);
		}

		return new \WP_Error(
			'it_exchange_rest_unexpected_error',
			__( 'An unexpected error occurred creating a new line item.', 'it-l10n-ithemes-exchange' ),
			array( 'status', 500 )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_type',
				__( 'Line item type cannot be added.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 404 )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( \WP_REST_Request $request ) {
		$cart = it_exchange_get_cart( $request['id'] );
		$cart->remove_all( $this->type->get_type() );

		return new \WP_REST_Response( '', 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_type',
				__( 'Line item type cannot be edited.', 'it-l10n-ithemes-exchange' ),
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
	public function get_path() { return "items/{$this->type->get_type()}/"; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->type->get_type(),
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'The unique id for this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'        => array(
					'description' => __( 'The type of this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'The name of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'The description of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'amount'      => array(
					'description' => __( 'The cost of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'quantity'    => array(
					'description' => __( 'The quantity purchased of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'selected' => array(
							'description' => __( 'Selected quantity for the line item.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'default'     => 1,
						),
						'max'      => array(
							'description' => __( 'Maximum purchase quantity for the line item.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'editable' => array(
							'description' => __( 'Whether the item quantity can be edited.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					)
				),
				'total'       => array(
					'description' => __( 'The total amount of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		foreach ( $this->type->get_additional_schema_props() as $prop => $schema_prop ) {
			$schema['properties'][ $prop ] = $schema_prop;
		}

		return $schema;
	}

	/**
	 * @inheritDoc
	 */
	public function has_parent() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_parent() { return new Cart( $this->serializer, array( $this->type->get_type() => $this ) ); }

	/**
	 * Prepare a cart for response.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item_Collection $collection
	 * @param \WP_REST_Request          $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_collection_for_response( \ITE_Line_Item_Collection $collection, \WP_REST_Request $request ) {

		$data = array();

		foreach ( $collection as $item ) {
			$data[] = r\response_to_array( $this->prepare_item_for_response( $item, $request ) );
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * Prepare a line item for response.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item   $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( \ITE_Line_Item $item, \WP_REST_Request $request ) {

		$response = new \WP_REST_Response( $this->serializer->serialize( $item, $this->get_schema(), it_exchange_get_cart( $request['id'] ) ) );
		$response->add_link( 'cart', r\get_rest_url( $this, array( 'id' => $request['id'] ) ) );

		return $response;
	}
}