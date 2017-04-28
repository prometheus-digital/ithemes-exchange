<?php
/**
 * Loggers that can purge logs by date.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Date_Purgeable_Logger
 */
interface ITE_Date_Purgeable_Logger extends ITE_Purgeable_Logger {

	/**
	 * Purge all logs that are the older than the given number of days.
	 *
	 * @since 2.0.0
	 *
	 * @param int $days
	 *
	 * @return bool
	 */
	public function purge_older_than( $days );
}