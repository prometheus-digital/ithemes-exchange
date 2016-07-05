<?php
/**
 * Taxable Line Item interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Taxable_Line_Item
 */
interface ITE_Taxable_Line_Item extends ITE_Aggregate_Line_Item {
	
	/**
	 * Is this particular instance of the line item taxable.
	 *
	 * For example, products are taxable, but an individual product might be exempt from tax.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function is_tax_exempt();

	/**
	 * Get the tax code this product falls in.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function get_tax_code();

	/**
	 * Get the total amount of this line item without any tax applied.
	 * 
	 * @since 1.36
	 * 
	 * @return float
	 */
	public function get_taxable_amount();

	/**
	 * Get all taxes this item has accrued.
	 *
	 * @since 1.36
	 *
	 * @return ITE_Tax_Line_Item[]
	 */
	public function get_taxes();

	/**
	 * Add a tax to the item.
	 *
	 * @since 1.36
	 *
	 * @param ITE_Tax_Line_Item $tax
	 */
	public function add_tax( ITE_Tax_Line_Item $tax );

	/**
	 * Remove a tax from the item.
	 *
	 * @since 1.36
	 *
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public function remove_tax( $id );

	/**
	 * Remove all taxes from the item.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function remove_all_taxes();
}