<?php
/**
 * Upgrade routine for coupons.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Routine_Coupons
 */
class IT_Exchange_Upgrade_Routine_Coupons implements IT_Exchange_UpgradeInterface {

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_version() {
		return '1.33';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Coupons', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'coupons';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Upgrade coupons to provide more advanced analytics.', 'it-l10n-ithemes-exchange' );
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
		return __( 'Core', 'it-l10n-ithemes-exchange' );
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
		return count( $this->get_coupons_to_upgrade() );
	}

	/**
	 * Get all coupons we need to upgrade.
	 *
	 * @since 1.33
	 *
	 * @param int $number
	 * @param int $page
	 *
	 * @return IT_Exchange_Coupon[]
	 */
	protected function get_coupons_to_upgrade( $number = - 1, $page = 1 ) {

		$args = array(
			'posts_per_page' => $number,
			'page'           => $page,
			'meta_query'     => array(
				array(
					'key'     => '_it-basic-code',
					'compare' => 'EXISTS'
				),
				array(
					'key'     => '_it-basic-allotted-quantity',
					'compare' => 'NOT EXISTS'
				)
			)
		);

		return it_exchange_get_coupons( $args );
	}

	/**
	 * Upgrade a coupon.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_Coupon                $coupon
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 * @param bool                              $verbose
	 */
	protected function upgrade_coupon( IT_Exchange_Coupon $coupon, IT_Exchange_Upgrade_SkinInterface $skin, $verbose ) {

		if ( $verbose ) {
			$skin->debug( 'Upgrading Coupon: ' . $coupon->get_code() );
		}

		if ( $coupon->post_content !== $coupon->get_code() ) {

			if ( $verbose ) {
				$skin->debug( 'Setting post_content to coupon code.' );
			}

			wp_update_post( array(
				'ID'           => $coupon->get_ID(),
				'post_content' => $coupon->get_code()
			) );
		}

		if ( $coupon instanceof IT_Exchange_Cart_Coupon ) {

			$coupon->set_allotted_quantity( $coupon->get_remaining_quantity() );

			if ( $verbose ) {
				$skin->debug( sprintf( 'Setting allotted coupon quantity to %d', $coupon->get_remaining_quantity() ) );
			}

			try {
				if ( $coupon->get_start_date() ) {

					// this changes dates to be saved as Y-m-d H:i:s instead of m/d/y
					$coupon->set_start_date( $coupon->get_start_date() );

					if ( $verbose ) {
						$skin->debug( 'Converting start date format.' );
					}
				}
			}
			catch ( Exception $e ) {
				$skin->error( sprintf(
					'Coupon %s: Exception thrown while trying to convert the start date format. %s',
					$coupon->get_code(), $e->getMessage() ) );
			}

			try {
				if ( $coupon->get_end_date() ) {

					$coupon->set_end_date( $coupon->get_end_date() );

					if ( $verbose ) {
						$skin->debug( 'Converting end date format.' );
					}
				}
			}
			catch ( Exception $e ) {
				$skin->error( sprintf(
					'Coupon %s: Exception thrown while trying to convert the end date format. %s',
					$coupon->get_code(), $e->getMessage() ) );
			}
		}

		if ( $verbose ) {
			$skin->debug( 'Upgraded Coupon: ' . $coupon->get_code() );
			$skin->debug( '' );
		}
	}

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_suggested_rate() {
		return 10;
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

		$coupons = $this->get_coupons_to_upgrade( $config->get_number(), $config->get_step() );

		foreach ( $coupons as $coupon ) {
			$this->upgrade_coupon( $coupon, $skin, $config->is_verbose() );
			$skin->tick();
		}

		sleep( 1 );
	}
}