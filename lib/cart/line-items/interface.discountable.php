<?php
/**
 * Discountable interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Discountable_Line_Item
 */
interface ITE_Discountable_Line_Item {

	/**
	 * Get the amount to discount.
	 * 
	 * @since 1.36
	 * 
	 * @return float
	 */
	public function get_amount_to_discount();
}