<?php
/**
 * Optionally supported feature requirement interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Optionally_Supported_Feature_Requirement
 */
interface ITE_Optionally_Supported_Feature_Requirement {

	/**
	 * Get the feature needing to be supported.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Optionally_Supported_Feature
	 */
	public function get_feature();

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
	public function get_requirement_details();
}