<?php
/**
 * Info CLI Command.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\CLI;

/**
 * Class Info
 *
 * @package iThemes\Exchange\CLI
 */
class Info extends \WP_CLI_Command {

	/**
	 * Print info about iThemes Exchange.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function __invoke( $args, $assoc_args ) {

		\WP_CLI::line( 'iThemes Exchange' );
		\WP_CLI::line( 'Version: ' . \IT_Exchange::VERSION );
		\WP_CLI::line( 'Pending Upgrades: ' . count( it_exchange_make_upgrader()->get_available_upgrades() ) );
	}
}