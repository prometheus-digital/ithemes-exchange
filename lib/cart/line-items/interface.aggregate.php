<?php
/**
 * Aggregate Line Item interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Aggregate_Line_Item
 */
interface ITE_Aggregate_Line_Item {

	/**
	 * Get all of the line items being represented.
	 *
	 * @since 1.36
	 *
	 * @return ITE_Line_Item_Collection|ITE_Aggregatable_Line_Item[]
	 */
	public function get_line_items();

	/**
	 * Add a line item to this aggregate.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Aggregatable_Line_Item $item
	 *
	 * @return $this
	 */
	public function add_item( ITE_Aggregatable_Line_Item $item );

	/**
	 * Remove an item from the aggregate.
	 *
	 * @since 1.36
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public function remove_item( $type, $id );
}