<?php
/**
 * For a logger that can retrieve its log items.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Retrievable_Logger
 */
interface ITE_Retrievable_Logger extends \Psr\Log\LoggerInterface {

	/**
	 * Get log items from this logger.
	 *
	 * @since 2.0.0
	 *
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return ITE_Log_Item[]
	 */
	public function get_log_items( $page = 1, $per_page = 20 );
}