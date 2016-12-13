<?php
/**
 * Requires optionally supported features interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Requires_Optionally_Supported_Features
 */
interface ITE_Requires_Optionally_Supported_Features {

	/**
	 * Retrieve all optionally supported features that are required by this object.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Optionally_Supported_Feature_Requirement[]
	 */
	public function optional_features_required();
}