<?php
/**
 * Expiring Session Interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Expiring_Session
 */
interface ITE_Expiring_Session extends IT_Exchange_SessionInterface  {

	/**
	 * Get the time this session expires.
	 *
	 * @since 2.0.0
	 *
	 * @return \DateTime|null
	 */
	public function expires_at();
}
