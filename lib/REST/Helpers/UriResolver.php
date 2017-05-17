<?php
/**
 * Custom URI Resolver to fix issue with query params.
 *
 * @since 2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Helpers;

/**
 * Class UriResolver
 *
 * @package iThemes\Exchange\REST\Helpers
 */
class UriResolver extends \JsonSchema\Uri\UriResolver {

	/**
	 * @inheritDoc
	 */
	public function generate( array $components ) {
		$uri = $components['scheme'] . '://'
		       . $components['authority']
		       . $components['path'];

		if (array_key_exists('query', $components)) {
			$uri .= '?' . $components['query'];
		}
		if (array_key_exists('fragment', $components)) {
			$uri .= '#' . $components['fragment'];
		}

		return $uri;
	}
}