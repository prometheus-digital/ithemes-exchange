<?php
/**
 * Contains the carts route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Carts
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
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

		if ( $request['customer'] ) {
			$user = it_exchange_get_customer( $request['customer'] );
		} else {
			$user = it_exchange_get_current_customer();
		}

		$expires_at         = null;
		$default_expiration = time() + (int) apply_filters( 'it_exchange_db_session_expiration', 2 * DAY_IN_SECONDS );

		if ( $request['expires_at'] ) {
			$expires_at = new \DateTime( $request['expires_at'], new \DateTimeZone( 'UTC' ) );
		}

		if ( ! $expires_at || $expires_at->getTimestamp() > $default_expiration ) {
			$expires_at = new \DateTime( "@$default_expiration", new \DateTimeZone( 'UTC' ) );
		}

		if ( $user instanceof \IT_Exchange_Guest_Customer ) {
			$session = \ITE_Session_Model::create( array(
				'ID'         => it_exchange_create_unique_hash(),
				'expires_at' => $expires_at,
			) );

			$repo = \ITE_Cart_Cached_Session_Repository::from_session_id( $user, $session->ID );

			$cart    = \ITE_Cart::create( $repo, $user );
			$session = \ITE_Session_Model::get( $session->ID );
		} elseif ( $request['is_main'] === true ) {
			try {
				// Guard against multiple carts per customer.
				$repo    = \ITE_Cart_Cached_Session_Repository::from_customer( $user );
				$cart_id = $repo->get_cart_id();
				$save    = false;

				if ( $expires_at ) {
					$save = true;

					$repo->get_model()->expires_at = $expires_at;
				}

				if ( ! $cart_id ) {
					$save    = true;
					$cart    = \ITE_Cart::create( $repo, $user );
					$cart_id = $cart->get_id();

					$repo->get_model()->cart_id = $cart_id;
				}

				if ( $save ) {
					$repo->get_model()->save();
				}

				$response = new \WP_REST_Response();
				$response->set_status( \WP_Http::SEE_OTHER );
				$response->header( 'Location', r\get_rest_url( $this->cart, array( 'cart_id' => $cart_id ) ) );

				return $response;
			} catch ( \InvalidArgumentException $e ) {

			}

			$session = \ITE_Session_Model::create( array(
				'ID'         => it_exchange_create_unique_hash(),
				'customer'   => $user->id,
				'expires_at' => $expires_at,
			) );

			$repo = \ITE_Cart_Cached_Session_Repository::from_session_id( $user, $session->ID );
			$cart = \ITE_Cart::create( $repo, $user );
		} else {
			$session = \ITE_Session_Model::create( array(
				'ID'         => it_exchange_create_unique_hash(),
				'customer'   => $user->id,
				'is_main'    => false,
				'expires_at' => $expires_at,
			) );

			$repo = \ITE_Cart_Cached_Session_Repository::from_session_id( $user, $session->ID );
			$cart = \ITE_Cart::create( $repo, $user );
		}

		$session->cart_id = $cart->get_id();
		$session->data    = array_merge( $session->data, array( 'cart_id' => $cart->get_id() ) );
		$session->save();

		$location = r\get_rest_url( $this->cart, array( 'cart_id' => $cart->get_id() ) );
		$request  = Request::from_url( $location );
		$request->set_url_params( array( 'cart_id' => $cart->get_id() ) );
		$request->set_route_object( 'cart_id', $cart );

		$response = $this->cart->handle_get( $request );
		$response->set_status( \WP_Http::CREATED );
		$response->header( 'Location', $location );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {
		if ( ! $scope->can( 'it_create_carts' ) ) {
			return Errors::cannot_create();
		}

		if ( $request['customer'] && ! $scope->can( 'it_create_others_carts' ) ) {
			return Errors::cannot_create();
		}

		return true;
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
