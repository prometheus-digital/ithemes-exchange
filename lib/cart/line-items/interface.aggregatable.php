<?php
/**
 * Aggregatable Line Item interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Aggregatable_Line_Item
 */
interface ITE_Aggregatable_Line_Item {

	/**
	 * Set the aggregate line item.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Aggregate_Line_Item $aggregate
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate );

	/**
	 * Get the aggregate line item.
	 * 
	 * @since 1.36
	 * 
	 * @return \ITE_Aggregate_Line_Item
	 */
	public function get_aggregate();
}
