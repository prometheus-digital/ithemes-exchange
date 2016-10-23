<?php
/**
 * REST Request.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Class Request
 * @package iThemes\Exchange\REST
 */
class Request extends \WP_REST_Request {

	/** @var Route */
	private $matched_route_controller;

	/**
	 * Create a Request object from the WP_REST_Request object.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \iThemes\Exchange\REST\Request
	 */
	public static function from_wp( \WP_REST_Request $request ) {

		$self = new self();

		foreach ( get_object_vars( $request ) as $key => $value ) {
			$self->{$key} = $value;
		}

		return $self;
	}

	/**
	 * Get the matched route controller.
	 *
	 * @since 1.36.0
	 *
	 * @return \iThemes\Exchange\REST\Route|null
	 */
	public function get_matched_route_controller() {
		return $this->matched_route_controller;
	}

	/**
	 * Set the matched route controller.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $matched_route_controller
	 *
	 * @return $this
	 */
	public function set_matched_route_controller( $matched_route_controller ) {
		$this->matched_route_controller = $matched_route_controller;

		return $this;
	}
}