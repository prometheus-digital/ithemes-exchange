<?php
/**
 * Line Item interface.
 *
 * @since   2.0.0
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
	 * Clone this line item with a new ID.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $include_frozen
	 *
	 * @return \ITE_Line_Item
	 */
	public function clone_with_new_id( $include_frozen = true ) {
		return new static( $this->get_id(), $this->bag, $include_frozen ? $this->frozen : new ITE_Array_Parameter_Bag() );
	}

	/**
	 * Get the ID of this line item.
	 *
	 * This need only be unique across line items of the same type.
	 *
	 * @since 2.0.0
	 *
	 * @return string|int
	 */
	public function get_id() { return $this->id; }

	/**
	 * Get the name of this line item.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_name();

	/**
	 * Get the description for this line item.
	 *
	 * HTML is permitted.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_description();

	/**
	 * Get the quantity of this line item.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public abstract function get_quantity();

	/**
	 * Get the base amount of this line item.
	 *
	 * To get the total, multiple the amount by the quantity.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public abstract function get_amount();

	/**
	 * Get the total amount.
	 *
	 * Most often, this is simply the amount multiplied by the quantity.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_total() {

		if ( $this->frozen->has_param( 'total' ) ) {
			$total = $this->frozen->get_param( 'total' );
		} else {
			$total = $this->get_amount() * $this->get_quantity();
		}

		// This is crap, but kind of unavoidable without using traits which are PHP 5.4
		if ( $this instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $this->get_line_items()->non_summary_only() as $item ) {
				$total += $item->get_total();
			}
		}

		return $total;
	}

	/**
	 * Get the type of the line item.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public abstract function is_summary_only();

	/**
	 * Get the line item object ID.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_object_id() { return 0; }

	/**
	 * Freeze this line item's state.
	 *
	 * This should take any configuration that might change, and persist that to parameterized storage.
	 *
	 * @since 2.0.0
	 */
	public function freeze() {
		$this->frozen->set_param( 'total', $this->get_amount() * $this->get_quantity() );
	}

	/**
	 * Get the line item's frozen state.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Read_Only_Parameter_Bag
	 */
	public function frozen() { return new ITE_Read_Only_Parameter_Bag( $this->frozen ); }

	/**
	 * @inheritDoc
	 */
	public function get_params() {
		$params = $this->bag->get_params();

		// Again, we shouldn't be doing it this way, but we don't have traits and this makes client code easier.
		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() ) {
			$scoped_params = $this->scoped_from()->get_params();
			$shared        = $this->shared_params_in_scope();

			$params = array_merge( $params, array_intersect_key( $scoped_params, array_flip( $shared ) ) );
		}

		return $params;
	}

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) {

		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() && in_array( $param, $this->shared_params_in_scope() ) ) {
			return $this->scoped_from()->has_param( $param );
		}

		return $this->bag->has_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) {

		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() && in_array( $param, $this->shared_params_in_scope() ) ) {
			return $this->scoped_from()->get_param( $param );
		}

		return $this->bag->get_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value ) {

		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() && in_array( $param, $this->shared_params_in_scope() ) ) {
			return $this->scoped_from()->set_param( $param, $value );
		}

		return $this->bag->set_param( $param, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) {

		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() && in_array( $param, $this->shared_params_in_scope() ) ) {
			return $this->scoped_from()->remove_param( $param );
		}

		return $this->bag->remove_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function __clone() {

		if ( $this instanceof ITE_Scopable_Line_Item && $this->is_scoped() ) {
			$this->set_scoped_from( clone $this->scoped_from() );
		}

		if ( $this instanceof ITE_Aggregatable_Line_Item && $this->get_aggregate() ) {
			$this->set_aggregate( clone $this->get_aggregate() );
		}

		$this->bag    = clone $this->bag;
		$this->frozen = clone $this->frozen;
	}
}
