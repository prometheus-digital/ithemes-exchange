<?php
/**
 * Optionally Supported Feature interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Optionally_Supported_Feature
 */
interface ITE_Optionally_Supported_Feature {

	/**
	 * Slug of the feature required.
	 *
	 * For example: 'recurring-payments'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_feature_slug();

	/**
	 * Human readable describing this feature.
	 *
	 * For example: 'Recurring Payments'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_feature_label();

	/**
	 * Get all possible details for this feature.
	 *
	 * For example: 'auto-renew', 'profile', 'signup-fee'.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function get_allowed_details();
}
