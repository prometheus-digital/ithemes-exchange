<?php
/**
 * Upgrade functions.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Make an upgrader object.
 *
 * @since 1.33
 *
 * @return IT_Exchange_Upgrader
 */
function it_exchange_make_upgrader() {

	$upgrader = new IT_Exchange_Upgrader();
	$upgrader->add_upgrade( new IT_Exchange_Upgrade_Routine_Coupons() );

	/**
	 * Fires when upgrade routines should be attached to the upgrader.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_Upgrader $upgrader
	 */
	do_action( 'it_exchange_register_upgrades', $upgrader );

	return $upgrader;
}