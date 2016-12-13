<?php
/**
 * Supports optional features interface.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Supports_Optional_Features
 */
interface ITE_Supports_Optional_Features {

	/**
	 * Does this object support the given feature.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Optionally_Supported_Feature $feature
	 *
	 * @return bool
	 */
	public function supports_feature( ITE_Optionally_Supported_Feature $feature );

	/**
	 * Does this object support the given feature and a given detail about that feature.
	 *
	 * An object might support a feature, for example 'recurring-payments', but might not support
	 * a 'signup-fee' detail.
	 *
	 * Or, might only support a 'profile' that is 'monthly' and 'yearly', not 'daily' or 'weekly'.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Optionally_Supported_Feature $feature
	 * @param string                           $slug
	 * @param mixed                            $detail
	 *
	 * @return bool
	 */
	public function supports_feature_and_detail( ITE_Optionally_Supported_Feature $feature, $slug, $detail );
}