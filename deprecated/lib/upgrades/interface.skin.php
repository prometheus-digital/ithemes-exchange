<?php
/**
 * Contains upgrade skin class.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Upgrade_SkinInterface
 *
 * Upgrade skins control how the status of an upgrade is relayed to the user.
 * This interface should be implemented for each type of user interface. For
 * example a CLI skin, a JavaScript progress bar skin, a stepped-redirect skin.
 */
interface IT_Exchange_Upgrade_SkinInterface {

	/**
	 * Output debug information.
	 *
	 * For use when in verbose mode.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function debug( $message );

	/**
	 * Notify the user of a non-critical problem.
	 *
	 * @since 1.33
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function warn( $message );

	/**
	 * Notify the user of a critical error.
	 *
	 * @since 1.33
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function error( $message );

	/**
	 * Increment the progress by a certain amount.
	 *
	 * @since 1.33
	 *
	 * @param int $amount
	 *
	 * @return void
	 */
	public function tick( $amount = 1 );

	/**
	 * Notify the user the upgrade has finished.
	 *
	 * @since 1.33
	 *
	 * @return void
	 */
	public function finish();
}