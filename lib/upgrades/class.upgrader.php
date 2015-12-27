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
		return in_array( $upgrade->get_slug(), $this->get_completed_upgrades(), true );
	}

	/**
	 * Get all upgrades, ever.
	 *
	 * @since 1.33
	 *
	 * @param bool $sorted
	 *
	 * @return IT_Exchange_UpgradeInterface[]
	 */
	public function get_upgrades( $sorted = false ) {

		if ( ! $sorted ) {
			return $this->upgrades;
		}

		$upgrades = $this->upgrades;

		@usort( $upgrades, array( $this, '_sort' ) );

		return $upgrades;
	}

	/**
	 * Sort upgrades to have newest upgrades appear on top.
	 *
	 * @param IT_Exchange_UpgradeInterface $a
	 * @param IT_Exchange_UpgradeInterface $b
	 *
	 * @return int
	 */
	public function _sort( IT_Exchange_UpgradeInterface $a, IT_Exchange_UpgradeInterface $b ) {

		if ( ! $this->is_upgrade_completed( $a ) XOR $this->is_upgrade_completed( $b ) ) {
			return version_compare( $b->get_version(), $a->get_version() );
		}

		return $this->is_upgrade_completed( $a ) ? 1 : - 1;
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
	 * Mark an upgrade as complete.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return $this
	 */
	public function complete( IT_Exchange_UpgradeInterface $upgrade ) {

		$completed   = $this->get_completed_upgrades();
		$completed[] = $upgrade->get_slug();

		update_option( 'it_exchange_completed_upgrades', $completed );

		$in_progress = $this->get_upgrades_in_progress();
		$index       = array_search( $upgrade->get_slug(), $in_progress, true );

		if ( $index !== false ) {
			unset( $in_progress[ $index ] );

			update_option( 'it_exchange_upgrades_in_progress', $in_progress );
		}

		return $this;
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

	/**
	 * Begin an upgrade.
	 *
	 * This sets the flags to determine if upgrades are in progress.
	 *
	 * This will not call the first upgrade step.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return IT_Exchange_Upgrader
	 */
	public function begin( IT_Exchange_UpgradeInterface $upgrade ) {

		$in_progress = $this->get_upgrades_in_progress();

		if ( ! array_search( $upgrade->get_slug(), $in_progress, true ) ) {
			$in_progress[] = $upgrade->get_slug();

			update_option( 'it_exchange_upgrades_in_progress', $in_progress );
		}

		return $this;
	}

	/**
	 * Check if an upgrade is in progress.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return bool
	 */
	public function is_upgrade_in_progress( IT_Exchange_UpgradeInterface $upgrade ) {
		return in_array( $upgrade->get_slug(), $this->get_upgrades_in_progress(), true );
	}

	/**
	 * Get all upgrades in progress.
	 *
	 * @since 1.33
	 *
	 * @return array
	 */
	protected function get_upgrades_in_progress() {
		return get_option( 'it_exchange_upgrades_in_progress', array() );
	}
}