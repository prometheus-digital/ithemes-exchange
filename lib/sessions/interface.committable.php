<?php
/**
 * Committable session.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Committable_Session
 */
interface ITE_Committable_Session extends IT_Exchange_SessionInterface {

	/**
	 * Commit session changes to storage.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function commit();
}