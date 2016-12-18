<?php
/**
 * Upgrades Command.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\CLI\Tools;

use WP_CLI\Formatter;
use WP_CLI\NoOp;
use WP_CLI\Utils as u;

/**
 * Class Upgrades
 *
 * @package iThemes\Exchange\CLI\Tools
 */
class Upgrades extends \WP_CLI_Command {

	/** @var \IT_Exchange_Upgrader */
	private $upgrader;

	/**
	 * Upgrades constructor.
	 *
	 * @param \IT_Exchange_Upgrader $upgrader
	 */
	public function __construct( \IT_Exchange_Upgrader $upgrader ) {
		$this->upgrader = $upgrader;
	}

	/**
	 * Get an upgrade routine.
	 *
	 * ## OPTIONS
	 *
	 * <upgrade>
	 * : Upgrade slug to retrieve details about.
	 *
	 * [--format=<format>]
	 * : Format for the results to be returned in.
	 *
	 * [--fields=<fields>]
	 * : Specify which fields to return.
	 * ---
	 * default: slug,name,group,version,rate,total,status,description
	 * ---
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function get( $args, $assoc_args ) {

		list( $upgrade ) = $args;
		$upgrade = $this->upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade ) {
			\WP_CLI::error( 'Invalid Upgrade' );
		}

		$data = $this->get_data_for_upgrade( $upgrade );

		$data['total'] = $upgrade->get_total_records_to_process();

		$formatter = new Formatter( $assoc_args );
		$formatter->display_item( $data );
	}

	/**
	 * List all upgrades.
	 *
	 * ## OPTIONS
	 *
	 * [--completed]
	 * : Only list completed upgrades.
	 *
	 * [--available]
	 * : Only list available upgrades.
	 *
	 * [--in-progress]
	 * : Only list upgrades in progress.
	 *
	 * [--format=<format>]
	 * : Format for the results to be returned in.
	 *
	 * [--fields=<fields>]
	 * : Specify which fields to return.
	 * ---
	 * default: slug,name,group,version,status
	 * ---
	 *
	 * @alias list
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function list_( $args, $assoc_args ) {

		$upgrades = array();
		$f        = false;

		if ( u\get_flag_value( $assoc_args, 'completed' ) ) {
			$f        = true;
			$upgrades = array_merge( $upgrades, array_filter( $this->upgrader->get_upgrades(),
				function ( \IT_Exchange_UpgradeInterface $u ) {
					return $this->upgrader->is_upgrade_completed( $u );
				} ) );
		}

		if ( u\get_flag_value( $assoc_args, 'in-progress' ) ) {
			$f        = true;
			$upgrades = array_merge( $upgrades, array_filter( $this->upgrader->get_upgrades(),
				function ( \IT_Exchange_UpgradeInterface $u ) {
					return $this->upgrader->is_upgrade_in_progress( $u );
				} ) );
		}

		if ( u\get_flag_value( $assoc_args, 'available' ) ) {
			$f        = true;
			$upgrades = array_merge( $upgrades, array_filter( $this->upgrader->get_upgrades(),
				function ( \IT_Exchange_UpgradeInterface $u ) {
					return ! $this->upgrader->is_upgrade_completed( $u ) && ! $this->upgrader->is_upgrade_in_progress( $u );
				} ) );
		}

		if ( ! $f ) {
			$upgrades = $this->upgrader->get_upgrades();
		}

		$items = array();

		foreach ( $upgrades as $upgrade ) {
			$items[] = $this->get_data_for_upgrade( $upgrade );
		}

		u\format_items( u\get_flag_value( $assoc_args, 'format', 'table' ), $items, u\get_flag_value( $assoc_args, 'fields' ) );
	}

	/**
	 * Run an upgrade routine.
	 *
	 * ## OPTIONS
	 *
	 * <upgrade>
	 * : Upgrade routine slug to run.
	 *
	 * [--log-file=<file>]
	 * : File to log details of the upgrade to. Defaults one will be automatically generated.
	 *
	 * [--rate=<rate>]
	 * : How many records to process at once. Defaults to the amount specified by the upgrade routine.
	 *
	 * [--debug]
	 * : Whether to print debugging statements.
	 *
	 * @since 2.0.0
	 *
	 * @alias upgrade
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function run( $args, $assoc_args ) {

		list( $upgrade ) = $args;
		$upgrade = $this->upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade ) {
			\WP_CLI::error( 'Invalid Upgrade' );
		}

		if ( $this->upgrader->is_upgrade_completed( $upgrade ) ) {
			\WP_CLI::error( 'Upgrade already completed' );
		}

		if ( $file = u\get_flag_value( $assoc_args, 'file' ) ) {
			$file = new \ITE_Upgrade_Skin_File( $file );
		} else {
			$file = \ITE_Upgrade_Skin_File::auto_create_file( $upgrade );
		}

		$total     = $upgrade->get_total_records_to_process();
		$completed = 0;
		$rate      = u\get_flag_value( $assoc_args, 'rate', $upgrade->get_suggested_rate() );

		$progress = u\make_progress_bar( 'Upgrading', $total );
		$progress = $progress instanceof NoOp ? null : $progress;

		$skin = new \ITE_Upgrade_Skin_Multi( array(
			$file,
			new \ITE_Upgrade_Skin_CLI( $progress )
		) );

		do {
			$config = new \IT_Exchange_Upgrade_Config( 1, $rate, true );
			$upgrade->upgrade( $config, $skin );
			$completed += $rate;
		} while ( $completed < $total );

		$skin->finish();
		$this->upgrader->complete( $upgrade );

		\WP_CLI::success( 'Completed. Log File: ' . $file->get_file() );
	}

	/**
	 * Get data for an upgrade.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return array
	 */
	protected function get_data_for_upgrade( \IT_Exchange_UpgradeInterface $upgrade ) {
		$data = array(
			'slug'        => $upgrade->get_slug(),
			'name'        => $upgrade->get_name(),
			'group'       => $upgrade->get_group(),
			'version'     => $upgrade->get_version(),
			'rate'        => $upgrade->get_suggested_rate(),
			'description' => $upgrade->get_description(),
		);

		if ( $this->upgrader->is_upgrade_completed( $upgrade ) ) {
			$data['status'] = 'completed';
		} elseif ( $this->upgrader->is_upgrade_in_progress( $upgrade ) ) {
			$data['status'] = 'in-progress';
		} else {
			$data['status'] = 'available';
		}

		return $data;
	}
}