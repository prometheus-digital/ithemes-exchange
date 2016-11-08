<?php
/**
 * Item Type Serializer.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

/**
 * Class TypeSerializer
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class TypeSerializer {

	/**
	 * Serialize a line item type.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item_Type $type
	 *
	 * @return array
	 */
	public function serialize( \ITE_Line_Item_Type $type ) {

		$data = array(
			'id'       => $type->get_type(),
			'label'    => $type->get_label(),
			'editable' => $type->is_editable_in_rest(),
		);

		return $data;
	}

	/**
	 * Get the item type schema.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => "cart_item_types",
			'type'       => 'object',
			'properties' => array(
				'id'       => array(
					'description' => __( 'Line item type id.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
				),
				'label'    => array(
					'description' => __( 'Human readable label representing the line item type.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
				),
				'editable' => array(
					'description' => __( 'Are line items of this type editable via the REST api.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
				),
			)
		);
	}
}