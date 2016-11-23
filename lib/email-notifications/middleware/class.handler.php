<?php
/**
 * Contains the middleware handler class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Middleware_Handler
 */
class IT_Exchange_Email_Middleware_Handler {

	const SPACING = 5;

	/**
	 * @var IT_Exchange_Email_Middleware[]
	 */
	private $middleware = array();

	/**
	 * @var array
	 */
	private $skip = array();

	/**
	 * Push a bit of middleware onto the stack.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Email_Middleware $middleware
	 * @param string                       $named
	 *
	 * @return self
	 */
	public function push( IT_Exchange_Email_Middleware $middleware, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new InvalidArgumentException( 'Middleware with this name already exists.' );
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
	 * @param IT_Exchange_Email_Middleware $middleware
	 * @param string                       $before
	 * @param string                       $named
	 *
	 * @return $this
	 */
	public function before( IT_Exchange_Email_Middleware $middleware, $before, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new InvalidArgumentException( 'Middleware with this name already exists.' );
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
	 * @param IT_Exchange_Email_Middleware $middleware
	 * @param string                       $after
	 * @param string                       $named
	 *
	 * @return $this
	 */
	public function after( IT_Exchange_Email_Middleware $middleware, $after, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new InvalidArgumentException( 'Middleware with this name already exists.' );
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
	 * @param IT_Exchange_Email_Middleware $middleware
	 * @param string                       $named
	 *
	 * @return $this
	 */
	public function first( IT_Exchange_Email_Middleware $middleware, $named = '' ) {

		if ( $named && isset( $this->middleware[ $named ] ) ) {
			throw new InvalidArgumentException( 'Middleware with this name already exists.' );
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
	 * Execute all middleware on an email.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		reset( $this->middleware );

		foreach ( $this->middleware as $name => $middleware ) {

			if ( $name && in_array( $name, $this->skip, true ) ) {
				continue;
			}

			if ( ! $middleware->handle( $sendable ) ) {
				return $this->cleanup( false );
			}
		}

		return $this->cleanup( true );
	}

	/**
	 * Cleanup any state left over from executing middleware.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $success
	 *
	 * @return bool
	 */
	protected function cleanup( $success ) {

		$this->skip = array();

		return $success;
	}
}
