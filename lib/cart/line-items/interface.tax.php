<?php
/**
 * Tax Line Item interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Tax_Line_Item
 */
interface ITE_Tax_Line_Item extends ITE_Aggregatable_Line_Item {
	
	/**
	 * Get the tax rate as a percentage.
	 *
	 * Ex: 8.75
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_rate();

	/**
	 * Determine whether this tax applies to a given line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 *
	 * @return bool
	 */
	public function applies_to( ITE_Taxable_Line_Item $item );

	/**
	 * Clone this tax item to be applied to a given taxable item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 *
	 * @return self A new instance of this class.
	 */
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item );
}
