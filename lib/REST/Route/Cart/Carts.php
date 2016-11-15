<?php
/**
 * Contains the carts route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Carts
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Carts extends Base implements Postable {

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
	public function handle_post( Request $request ) {

		$user = it_exchange_get_current_customer();

		if ( $user instanceof \IT_Exchange_Guest_Customer ) {
			$session = \ITE_Session_Model::create( array(
				'ID' => it_exchange_create_unique_hash()
			) );

			$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );

			$cart = \ITE_Cart::create( $repo, $user );
			$cart->set_meta( 'guest-email', $user->get_email() );
			$session = \ITE_Session_Model::get( $session->ID );
		} elseif ( $request['is_main'] ) {
			try {
				// Guard against multiple carts per customer.
				$repo    = \ITE_Line_Item_Cached_Session_Repository::from_customer( $user );
				$cart_id = $repo->get_cart_id();

				if ( ! $cart_id ) {
					$cart    = \ITE_Cart::create( $repo, $user );
					$cart_id = $cart->get_id();

					$repo->get_model()->cart_id = $cart_id;
					$repo->get_model()->save();
				}

				$response = new \WP_REST_Response();
				$response->set_status( \WP_Http::SEE_OTHER );
				$response->header( 'Location', r\get_rest_url( $this->cart, array( 'cart_id' => $cart_id ) ) );

				return $response;
			} catch ( \InvalidArgumentException $e ) {

			}

			$session = \ITE_Session_Model::create( array(
				'ID'       => it_exchange_create_unique_hash(),
				'customer' => $user->id,
			) );

			$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );
			$cart = \ITE_Cart::create( $repo, $user );
		} else {
			$session = \ITE_Session_Model::create( array(
				'ID'       => it_exchange_create_unique_hash(),
				'customer' => $user->id,
				'is_main'  => false,
			) );

			$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );
			$cart = \ITE_Cart::create( $repo, $user );
		}

		$session->cart_id = $cart->get_id();
		$session->data    = array_merge( $session->data, array( 'cart_id' => $cart->get_id() ) );
		$session->save();

		$location = r\get_rest_url( $this->cart, array( 'cart_id' => $cart->get_id() ) );
		$request  = Request::from_url( $location );
		$request->set_url_params( array( 'cart_id' => $cart->get_id() ) );

		$response = $this->cart->handle_get( $request );
		$response->set_status( \WP_Http::CREATED );
		$response->header( 'Location', $location );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {
		return (bool) $user && $user->id;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'carts/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->cart->get_schema(); }
}
