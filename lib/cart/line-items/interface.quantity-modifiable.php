<?php
/**
 * Quantity Modifiable Interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Quantity_Modifiable_Item
 */
interface ITE_Quantity_Modifiable_Item {

	/**
	 * Set the item's new quantity.
	 *
	 * @since 2.0.0
	 *
	 * @param int $quantity
	 */
	public function set_quantity( $quantity );

	/**
	 * Is the item's quantity modifiable.
	 *
	 * An item type can generally have its quantity modified,
	 * but a particular instance of it could not be.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_quantity_modifiable();

	/**
	 * Get the maximum purchase quantity available.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_max_quantity_available();
}
