<?php
/**
 * Stub Gateway Request Handler.
 *
 * @since   2.0.0
 * @license GPLV2
 */

/**
 * Class IT_Exchange_Stub_Gateway_Request_Handler
 */
class IT_Exchange_Stub_Gateway_Request_Handler implements ITE_Gateway_Request_Handler {

	/** @var ITE_Gateway_Request */
	private $request;

	public $callback;

	/**
	 * IT_Exchange_Stub_Gateway_Request_Handler constructor.
	 *
	 * @param $callback
	 */
	public function __construct( $callback = null ) { $this->callback = $callback; }

	/**
	 * @return ITE_Gateway_Request
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * @inheritDoc
	 */
	public function handle( $request ) {
		$this->request = $request;

		if ( $this->callback ) {
			return call_user_func( $this->callback, $request );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function __call( $name, $arguments ) { return true; }

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) {
		return true;
	}
}