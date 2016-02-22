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
	$upgrader->add_upgrade( new IT_Exchange_Upgrade_Routine_Txn_Activity() );

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

/**
 * Register upgrade handlers.
 *
 * @since 1.35.2
 */
function it_exchange_register_upgrade_handlers() {

	$upgrader = it_exchange_make_upgrader();

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$ajax_handler = new IT_Exchange_Upgrade_Handler_Ajax( $upgrader );
		$ajax_handler->hooks();
	}

	/**
	 * Fires when any additional upgrade handlers should be run, such as WP-CLI
	 *
	 * @since 1.35.2
	 *
	 * @param IT_Exchange_Upgrader $upgrader
	 */
	do_action( 'it_exchange_register_upgrade_handlers', $upgrader );
}

add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_register_upgrade_handlers', 100 );

/**
 * Activates the nag when version is updated if an upgrade is available.
 *
 * @since 1.33
 *
 * @param array $versions contains previous and current elements
 *
 * @return void
 */
function it_exchange_activate_upgrades_available_nag( $versions ) {

	$upgrades = it_exchange_make_upgrader()->get_available_upgrades();

	if ( count( $upgrades ) === 0 ) {
		return;
	}

	update_option( 'it_exchange_show_upgrades_nag', true );
}

add_action( 'it_exchange_version_updated', 'it_exchange_activate_upgrades_available_nag' );

/**
 * Shows the nag when needed.
 *
 * Also dismisses the nag
 *
 * @since 1.33
 */
function it_exchange_show_upgrades_available_nag() {
	if ( ! empty( $_GET['it-exchange-dismiss-upgrades-nag'] ) ) {
		delete_option( 'it_exchange_show_upgrades_nag' );
	}

	$show_nag = get_option( 'it_exchange_show_upgrades_nag', false );

	if ( ! count( it_exchange_make_upgrader()->get_available_upgrades() ) ) {
		update_option( 'it_exchange_show_upgrades_nag', false );

		return;
	}

	if ( $show_nag && ( empty( $_GET['page'] ) || $_GET['page'] != 'it-exchange-tools' ) ) {
		$upgrades_url = admin_url( 'admin.php?page=it-exchange-tools' );

		$dismiss_url = add_query_arg( array( 'it-exchange-dismiss-upgrades-nag' => 1 ) ); // escaped in included file

		include( dirname( dirname( __FILE__ ) ) . '/admin/views/admin-upgrades-available-notice.php' );
	}
}

add_action( 'admin_notices', 'it_exchange_show_upgrades_available_nag' );