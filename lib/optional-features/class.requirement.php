<?php
/**
 * Optionally supported feature requirement class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Optionally_Supported_Feature_Requirement
 */
class ITE_Optionally_Supported_Feature_Requirement {

	/** @var ITE_Optionally_Supported_Feature */
	private $feature;

	/** @var array */
	private $details = array();

	/**
	 * ITE_Optionally_Supported_Feature_Requirement constructor.
	 *
	 * @param ITE_Optionally_Supported_Feature $feature
	 * @param array                            $details
	 */
	public function __construct( ITE_Optionally_Supported_Feature $feature, array $details = array() ) {
		$this->feature = $feature;
		$this->details = $details;
	}

	/**
	 * Get the feature needing to be supported.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Optionally_Supported_Feature
	 */
	public function get_feature() {	return $this->feature; }

	/**
	 * Retrieve details about the feature.
	 *
	 * For example: [
	 *      'auto-renew' => true,
	 *      'profile'    => new IT_Exchange_Recurring_Profile( 'days', 5 ),
	 *      'signup-fee' => true,
	 * ]
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_requirement_details() { return $this->details; }
}