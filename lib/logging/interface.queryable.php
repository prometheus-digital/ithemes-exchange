<?php
/**
 * Queryable Logger.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Queryable_Logger
 */
interface ITE_Queryable_Logger extends \Psr\Log\LoggerInterface {

	/**
	 * Query the log source for given logs.
	 *
	 * See ::get_supported_filters() for available where expressions.
	 *
	 * MUST support pagination and ordering by 'time' and 'severity'
	 *
	 * @since 2.0.0
	 *
	 * @param \Doctrine\Common\Collections\Criteria $criteria
	 *
	 * @return ITE_Log_Item[]
	 */
	public function query( \Doctrine\Common\Collections\Criteria $criteria );

	/**
	 * Get the filters that are supported by this log source.
	 *
	 * Should return an array of data => criterion names.
	 *
	 * Example:
	 *  'group' => 'lgroup'
	 *
	 * Globally, the following filters can be supported. 'group', 'user', 'ip', 'level', 'message'. Where 'message'
	 * is a %LIKE% comparison and 'ip' is a LIKE% comparison.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function get_supported_filters();
}