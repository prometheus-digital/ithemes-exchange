<?php
/**
 * Discountable interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Discountable_Line_Item
 */
interface ITE_Discountable_Line_Item {

	/**
	 * Get the amount to discount.
	 * 
	 * @since 2.0.0
	 * 
	 * @return float
	 */
	public function get_amount_to_discount();
}
