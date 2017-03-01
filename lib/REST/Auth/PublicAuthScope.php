<?php
/**
 * Public Auth Scope.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Class PublicAuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
class PublicAuthScope implements AuthScope {

	/**
	 * @inheritDoc
	 */
	public function can( $capability, $args = null ) {

		if ( $capability === 'read' || $capability === 'exists' ) {
			return true;
		}

		return false;
	}
}