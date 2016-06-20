<?php
/**
 * Line Item Repository aware interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Line_Item_Repository_Aware
 */
interface ITE_Line_Item_Repository_Aware {

	/**
	 * Set the line item repository on this object.
	 * 
	 * @since 1.36
	 * 
	 * @param \ITE_Line_Item_Repository $repository
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository );
}