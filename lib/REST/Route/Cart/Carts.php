<?php
/**
 * Contains the carts route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Postable;

/**
 * Class Carts
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Carts implements Postable {

	/** @var Cart */
	private $cart;

	/**
	 * Carts constructor.
	 *
	 * @param Cart $cart Cart route.
	 */
	public function __construct( Cart $cart ) {
		$this->cart = $cart;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( \WP_REST_Request $request ) {

		$user = it_exchange_get_current_customer();

		if ( $user instanceof \IT_Exchange_Guest_Customer ) {
			$session = \ITE_Session_Model::create( array(
				'ID' => it_exchange_create_unique_hash()
			) );

			$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );

			$cart = \ITE_Cart::create( $repo, $user );
			$cart->set_meta( 'guest-email', $user->get_email() );
			$session = \ITE_Session_Model::get( $session->ID );
		} else {
			try {
				// Guard against multiple carts per customer.
				$repo     = \ITE_Line_Item_Cached_Session_Repository::from_customer( $user );
				$response = new \WP_REST_Response();
				$response->set_status( 303 );
				$response->header( 'Location', r\get_rest_url( $this->cart, array( 'id' => $repo->get_cart_id() ) ) );

				return $response;
			}
			catch ( \InvalidArgumentException $e ) {

			}

			$session = \ITE_Session_Model::create( array(
				'ID'       => it_exchange_create_unique_hash(),
				'customer' => $user->id,
			) );

			$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );
			$cart = \ITE_Cart::create( $repo, $user );
		}

		$session->cart_id = $cart->get_id();
		$session->data    = array_merge( $session->data, array( 'cart_id' => $cart->get_id() ) );
		$session->save();

		$response = new \WP_REST_Response();
		$response->set_status( 303 );
		$response->header( 'Location', r\get_rest_url( $this->cart, array( 'id' => $cart->get_id() ) ) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( \WP_REST_Request $request, \IT_Exchange_Customer $user ) {
		return (bool) $user->id;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'carts/';
	}

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return $this->cart->get_schema();
	}

	/**
	 * @inheritDoc
	 */
	public function has_parent() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_parent() {
		throw new \UnexpectedValueException( "No parent exists for {$this->get_path()}" );
	}
}