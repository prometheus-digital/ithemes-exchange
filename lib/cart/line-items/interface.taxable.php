<?php
/**
 * Taxable Line Item interface.
 *
 * @since   2.0.0
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
	 * @since 2.0.0
	 *
	 * @param ITE_Tax_Provider $for
	 *
	 * @return bool
	 */
	public function is_tax_exempt( ITE_Tax_Provider $for );

	/**
	 * Get the tax code this product falls in.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Tax_Provider $for
	 *
	 * @return int
	 */
	public function get_tax_code( ITE_Tax_Provider $for );

	/**
	 * Get the total amount of this line item without any tax applied.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_taxable_amount();

	/**
	 * Get all taxes this item has accrued.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Line_Item_Collection|ITE_Tax_Line_Item[]
	 */
	public function get_taxes();

	/**
	 * Add a tax to the item.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Tax_Line_Item $tax
	 */
	public function add_tax( ITE_Tax_Line_Item $tax );

	/**
	 * Remove a tax from the item.
	 *
	 * @since 2.0.0
	 *
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public function remove_tax( $id );

	/**
	 * Remove all taxes from the item.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function remove_all_taxes();
}