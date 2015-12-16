<?php
/**
 * Upgrader class.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrader
 */
class IT_Exchange_Upgrader {

	/**
	 * @var IT_Exchange_UpgradeInterface[]
	 */
	private $upgrades = array();

	/**
	 * Add an upgrade to the upgrader.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return self
	 */
	public function add_upgrade( IT_Exchange_UpgradeInterface $upgrade ) {
		$this->upgrades[ $upgrade->get_slug() ] = $upgrade;

		return $this;
	}

	/**
	 * Check if an upgrade has been completed.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return bool
	 */
	public function is_upgrade_completed( IT_Exchange_UpgradeInterface $upgrade ) {
		return in_array( $upgrade->get_slug(), $this->get_completed_upgrades() );
	}

	/**
	 * Get all upgrades, ever.
	 *
	 * @since 1.33
	 *
	 * @return IT_Exchange_UpgradeInterface[]
	 */
	public function get_upgrades() {
		return $this->upgrades;
	}

	/**
	 * Get an upgrade object by it's slug.
	 *
	 * @since 1.33
	 *
	 * @param string $slug
	 *
	 * @return IT_Exchange_UpgradeInterface|null
	 */
	public function get_upgrade( $slug ) {
		return isset( $this->upgrades[ $slug ] ) ? $this->upgrades[ $slug ] : null;
	}

	/**
	 * Get available upgrades.
	 *
	 * @since 1.33
	 *
	 * @return IT_Exchange_UpgradeInterface[]
	 */
	public function get_available_upgrades() {

		$available = array();

		foreach ( $this->get_upgrades() as $upgrade ) {
			if ( ! $this->is_upgrade_completed( $upgrade ) ) {
				$available[] = $upgrade;
			}
		}

		return $available;
	}

	/**
	 * Get all completed upgrades.
	 *
	 * @since 1.33
	 *
	 * @return array
	 */
	protected function get_completed_upgrades() {
		return get_option( 'it_exchange_completed_upgrades', array() );
	}
}