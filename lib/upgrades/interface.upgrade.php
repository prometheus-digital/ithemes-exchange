<?php
/**
 * Upgrade routine interface.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_UpgradeInterface
 */
interface IT_Exchange_UpgradeInterface {

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_version();

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_group();

	/**
	 * Get the total records needed to be processed for this upgrade.
	 *
	 * This is used to build the upgrade UI.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_total_records_to_process();

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_suggested_rate();

	/**
	 * Perform the upgrade according to the given configuration.
	 *
	 * Throwing an upgrade exception will halt the upgrade process and notify the user.
	 *
	 * @param IT_Exchange_Upgrade_Config        $config
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 *
	 * @return void
	 *
	 * @throws IT_Exchange_Upgrade_Exception
	 */
	public function upgrade( IT_Exchange_Upgrade_Config $config, IT_Exchange_Upgrade_SkinInterface $skin );
}