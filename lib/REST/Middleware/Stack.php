<?php
/**
 * Middleware Stack.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;

/**
 * Class Stack
 * @package iThemes\Exchange\REST
 */
class Stack {

	/** @var Middleware[] */
	private $middleware = array();

	/** @var string[] */
	private $skip = array();

	/**
	 * Push a bit of middleware onto the stack.
	 *
	 * @since 2.0.0
	 *
	 * @param Middleware $middleware
	 * @param string     $named
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException
	 */
	public function push( Middleware $middleware, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new \InvalidArgumentException( 'Middleware with this name already exists.' );
		}

		if ( $named ) {
			$this->middleware[ $named ] = $middleware;
		} else {
			$this->middleware[] = $middleware;
		}

		return $this;
	}

	/**
	 * Add a piece of middleware to be executed before another piece of middleware.
	 *
	 * @since 2.0.0
	 *
	 * @param Middleware $middleware
	 * @param string     $before
	 * @param string     $named
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function before( Middleware $middleware, $before, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new \InvalidArgumentException( 'Middleware with this name already exists.' );
		}

		if ( ! isset( $this->middleware[ $before ] ) ) {
			return $this;
		}

		$before_position = 0;

		foreach ( $this->middleware as $key => $value ) {
			if ( $key === $before ) {
				break;
			}

			$before_position ++;
		}

		$all = array_slice( $this->middleware, 0, $before_position, true );

		if ( $named ) {
			$all[ $named ] = $middleware;
		} else {
			$all[] = $middleware;
		}

		$all = array_merge( $all, array_slice( $this->middleware, $before_position, count( $this->middleware ) - $before_position, true ) );

		$this->middleware = $all;

		return $this;
	}

	/**
	 * Add a piece of middleware to be executed after another piece of middleware.
	 *
	 * @since 2.0.0
	 *
	 * @param Middleware $middleware
	 * @param string     $after
	 * @param string     $named
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function after( Middleware $middleware, $after, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new \InvalidArgumentException( 'Middleware with this name already exists.' );
		}

		if ( ! isset( $this->middleware[ $after ] ) ) {
			return $this;
		}

		$after_position = 0;

		foreach ( $this->middleware as $key => $value ) {
			if ( $key === $after ) {
				break;
			}

			$after_position ++;
		}

		$after_position += 1;

		$all = array_slice( $this->middleware, 0, $after_position, true );

		if ( $named ) {
			$all[ $named ] = $middleware;
		} else {
			$all[] = $middleware;
		}

		$all = array_merge( $all, array_slice( $this->middleware, $after_position, count( $this->middleware ) - $after_position, true ) );

		$this->middleware = $all;

		return $this;
	}

	/**
	 * Add a piece of middleware so it is executed first.
	 *
	 * @since 2.0.0
	 *
	 * @param Middleware $middleware
	 * @param string     $named
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function first( Middleware $middleware, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new \InvalidArgumentException( 'Middleware with this name already exists.' );
		}

		$new = array();

		if ( $named ) {
			$new[ $named ] = $middleware;
		} else {
			$new[] = $middleware;
		}

		$this->middleware = array_merge( $new, $this->middleware );

		return $this;
	}

	/**
	 * Skip one or more middlewares during execution.
	 *
	 * Will be cleared after one `handle()` call.
	 *
	 * @param string ...$skip
	 *
	 * @return $this
	 */
	public function skip( $skip ) {

		$this->skip = array_merge( $this->skip, func_get_args() );

		return $this;
	}

	/**
	 * Execute all middleware on a REST request.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request             $request
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return \WP_REST_Response
	 */
	public function handle( \WP_REST_Request $request, Route $route ) {

		$request = Request::from_wp( $request )->set_matched_route_controller( $route );

		reset( $this->middleware );

		$to_use = array();

		foreach ( $this->middleware as $name => $middleware ) {

			if ( $name && in_array( $name, $this->skip, true ) ) {
				continue;
			}

			$to_use[] = $middleware;
		}

		$make_response = function ( Request $request ) {
			return call_user_func( array(
				$request->get_matched_route_controller(),
				'handle_' . strtolower( $request->get_method() )
			), $request );
		};

		$delegate = new Delegate( $to_use, $make_response );

		$response = $delegate->next( $request );

		$this->cleanup();

		return $response;
	}

	/**
	 * Cleanup any state left over from executing middleware.
	 *
	 * @since 2.0.0
	 */
	protected function cleanup() {
		$this->skip = array();
	}
}
