<?php
/**
 * Middleware Delegate.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;
use iThemes\Exchange\REST\Request;

/**
 * Class Delegate
 * @package iThemes\Exchange\REST\Middleware
 */
class Delegate {

	/** @var Middleware[] */
	private $stack = array();

	/** @var int */
	private $index = 0;

	/** @var callable */
	private $final;

	/**
	 * Delegate constructor.
	 *
	 * @param \iThemes\Exchange\REST\Middleware\Middleware[] $stack
	 * @param callable                                       $final
	 */
	public function __construct( array $stack, $final ) {
		$this->stack = $stack;
		$this->final = $final;
	}

	/**
	 * Go to the next middleware.
	 *
	 * @since 1.36.0
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function next( Request $request ) {

		if ( ! isset( $this->stack[ $this->index ] ) ) {
			return call_user_func( $this->final, $request );
		}

		return $this->stack[ $this->index ]->handle( $request, $this->next_delegate() );
	}

	/**
	 * Get the next delegate in the chain.
	 *
	 * @since 1.36.0
	 *
	 * @return \iThemes\Exchange\REST\Middleware\Delegate
	 */
	protected function next_delegate() {
		$next = clone $this;
		$next->index ++;

		return $next;
	}
}