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
abstract class ITE_Line_Item implements ITE_Parameter_Bag {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var \ITE_Parameter_Bag
	 */
	protected $bag;

	/**
	 * @var \ITE_Parameter_Bag
	 */
	protected $frozen;

	/**
	 * ITE_Cart_Product constructor.
	 *
	 * @param string             $id
	 * @param \ITE_Parameter_Bag $bag
	 *
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen ) {
		$this->id     = $id;
		$this->bag    = $bag;
		$this->frozen = $frozen;
	}

	/**
	 * Get the ID of this line item.
	 *
	 * This need only be unique across line items of the same type.
	 *
	 * @since 1.36
	 *
	 * @return string|int
	 */
	public function get_id() { return $this->id; }

	/**
	 * Get the name of this line item.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public abstract function get_name();

	/**
	 * Get the description for this line item.
	 *
	 * HTML is permitted.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public abstract function get_description();

	/**
	 * Get the quantity of this line item.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public abstract function get_quantity();

	/**
	 * Get the base amount of this line item.
	 *
	 * To get the total, multiple the amount by the quantity.
	 *
	 * @since 1.36
	 *
	 * @return float
	 */
	public abstract function get_amount();

	/**
	 * Get the total amount.
	 *
	 * Most often, this is simply the amount multiplied by the quantity.
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	public function get_total() { return $this->get_amount() * $this->get_quantity(); }

	/**
	 * Get the type of the line item.
	 *
	 * @since 1.36
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public abstract function get_type( $label = false );

	/**
	 * Should this line item be displayed only in the summary view of the cart,
	 * or should it also be displayed in the main cart rows.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public abstract function is_summary_only();

	/**
	 * Freeze this line item's stae.
	 *
	 * This should take any configuration that might change, and persist that to parameterized storage.
	 *
	 * @since 1.36.0
	 */
	public function freeze() {}

	/**
	 * @inheritDoc
	 */
	public function get_params() { return $this->bag->get_params(); }

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) { return $this->bag->has_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) { return $this->bag->get_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value ) { return $this->bag->set_param( $param, $value ); }

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) { return $this->bag->remove_param( $param ); }
}
