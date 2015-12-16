<?php
/**
 * Upgrade routine for coupons.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Routine_Demo
 */
class IT_Exchange_Upgrade_Routine_Demo implements IT_Exchange_UpgradeInterface {

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_version() {
		return '1.32';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Demo', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'demo';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Upgrade orders to provide more advanced analytics.', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_group() {
		return __( 'Membership', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the total records needed to be processed for this upgrade.
	 *
	 * This is used to build the upgrade UI.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_total_records_to_process() {
		return 5;
	}

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
	public function upgrade( IT_Exchange_Upgrade_Config $config, IT_Exchange_Upgrade_SkinInterface $skin ) {

		$coupons = it_exchange_get_coupons( array(
			'posts_per_page' => $config->get_number(),
			'paged'          => $config->get_step(),
			'meta_query'     => array(
				'key'     => '_it-basic-code',
				'compare' => 'EXISTS'
			)
		) );

		foreach ( $coupons as $coupon ) {
			$skin->debug( $coupon->post_title );
		}

		$skin->tick( $config->get_number() );

		sleep( 2 );
	}
}