<?php
/**
 * Line Item interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Line_Item
 */
interface ITE_Line_Item extends ITE_Parameter_Bag {

	/**
	 * ITE_Cart_Product constructor.
	 *
	 * @param string             $id
	 * @param \ITE_Parameter_Bag $bag
	 *
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen );

	/**
	 * Get the ID of this line item.
	 *
	 * This need only be unique across line items of the same type.
	 *
	 * @since 1.36
	 *
	 * @return string|int
	 */
	public function get_id();

	/**
	 * Get the name of this line item.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the description for this line item.
	 *
	 * HTML is permitted.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the quantity of this line item.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function get_quantity();

	/**
	 * Get the base amount of this line item.
	 *
	 * To get the total, multiple the amount by the quantity.
	 *
	 * @since 1.36
	 *
	 * @return float
	 */
	public function get_amount();

	/**
	 * Get the type of the line item.
	 *
	 * @since 1.36
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false );

	/**
	 * Should this line item be displayed only in the summary view of the cart,
	 * or should it also be displayed in the main cart rows.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function is_summary_only();

	/**
	 * Persist the line item to the session.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item_Repository $repository
	 *
	 * @return bool
	 */
	public function persist( ITE_Line_Item_Repository $repository );
}