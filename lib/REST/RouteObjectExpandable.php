<?php
/**
 * Route Object Expandable interface.
 *
 * @since 2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface RouteObjectExpandable
 *
 * @package iThemes\Exchange\REST
 */
interface RouteObjectExpandable extends Route {

	/**
	 * Return a map of route object IDs to factory callables that accept the object ID as the first parameter.
	 *
	 * For example.
	 *
	 * return array(
	 *      'transaction_id' => 'it_exchange_get_transaction'
	 *
	 * );
	 *
	 * Objects declared in this way can be accessed from the Request object.
	 *
	 * $transaction = $request->get_route_object( 'transaction_id' );
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_route_object_map();
}