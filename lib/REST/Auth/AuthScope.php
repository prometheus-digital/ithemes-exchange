<?php
/**
 * AuthScope interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Interface AuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
interface AuthScope {

	/**
	 * Can this auth scope perform a given function.
	 *
	 * @since 2.0.0
	 *
	 * @param string $capability
	 * @param mixed  $args,...
	 *
	 * @return bool
	 */
	public function can( $capability, $args = null );
}