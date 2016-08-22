<?php
/**
 * Read-Only Parameter Bag.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Read_Only_Parameter_Bag
 */
class ITE_Read_Only_Parameter_Bag implements ITE_Parameter_Bag {

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var bool */
	private $silent = true;

	/**
	 * ITE_Read_Only_Parameter_Bag constructor.
	 *
	 * @param \ITE_Parameter_Bag $bag
	 * @param bool               $silent Whether to throw Exceptions when attempting to modify state.
	 */
	public function __construct( \ITE_Parameter_Bag $bag, $silent = true ) {
		$this->bag    = $bag;
		$this->silent = true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_params() {
		return $this->bag->get_params();
	}

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) {
		return $this->bag->has_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) {
		return $this->bag->get_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value ) {
		if ( ! $this->silent ) {
			throw new BadMethodCallException( 'Read Only Paramter Bag does not allow setting parameters.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) {
		if ( ! $this->silent ) {
			throw new BadMethodCallException( 'Read Only Paramter Bag does not allow removing parameters.' );
		}
	}
}