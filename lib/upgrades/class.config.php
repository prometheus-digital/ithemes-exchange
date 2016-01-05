<?php
/**
 * Upgrade config.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Config
 */
final class IT_Exchange_Upgrade_Config {

	/**
	 * @var int
	 */
	private $step;

	/**
	 * @var int
	 */
	private $number;

	/**
	 * @var bool
	 */
	private $verbose;

	/**
	 * IT_Exchange_Upgrade_Config constructor.
	 *
	 * @param int $step
	 * @param int $number
	 * @param     $verbose
	 */
	public function __construct( $step, $number, $verbose ) {
		$this->step    = $step;
		$this->number  = $number;
		$this->verbose = $verbose;
	}

	/**
	 * Get the step number.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Get the total number of records to process in this step.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * If the upgrade routine should output verbose debug information.
	 *
	 * @since 1.33
	 *
	 * @return boolean
	 */
	public function is_verbose() {
		return $this->verbose;
	}
}