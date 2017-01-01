<?php
/**
 * Contains the cart route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;

/**
 * Class Cart
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Cart extends r\Route\Base implements Getable, Putable, Deletable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Cart constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		if ( ! $request->get_cart() ) {
			return new \WP_REST_Response( array(), 500 );
		}

		return $this->prepare_item_for_response( $request->get_cart() );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {
		$cart = $request->get_cart();

		$c_billing = $cart->get_billing_address() ? $cart->get_billing_address()->to_array() : array();
		$u_billing = $request['billing_address'];

		$c_billing = array_filter( $c_billing );
		$u_billing = array_filter( $u_billing );

		ksort( $c_billing );
		ksort( $u_billing );

		if ( $c_billing !== $u_billing ) {
			$cart->set_billing_address( $request['billing_address'] ? new \ITE_In_Memory_Address( $request['billing_address'] ) : null );
		}

		$c_shipping = $cart->get_shipping_address() ? $cart->get_shipping_address()->to_array() : array();
		$u_shipping = $request['shipping_address'];

		$c_shipping = array_filter( $c_shipping );
		$u_shipping = array_filter( $u_shipping );

		ksort( $c_shipping );
		ksort( $u_shipping );

		if ( $c_shipping !== $u_shipping ) {
			$cart->set_shipping_address( $request['shipping_address'] ? new \ITE_In_Memory_Address( $request['shipping_address'] ) : null );
		}

		return $this->prepare_item_for_response( $cart );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {
		$cart = $request->get_cart();
		$cart->empty_cart();

		return new \WP_HTTP_Response( '', 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<cart_id>\w+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function has_parent() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_parent() { return new Carts( $this ); }

	/**
	 * Perform a permission check.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer          $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $cart = it_exchange_get_cart( $request->get_param( 'cart_id', 'URL' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_cart',
				__( 'Invalid cart id.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 404 )
			);
		}

		if ( $cart->is_guest() ) {
			if ( $user instanceof \IT_Exchange_Guest_Customer && $user->get_email() === $cart->get_customer()->get_email() ) {
				return true;
			}
		}

		if ( $cart->get_customer() && $user && $cart->get_customer()->id === $user->id ) {
			return true;
		}

		return new \WP_Error(
			'it_exchange_rest_forbidden_context',
			__( 'Sorry, you are not allowed to access this cart.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Prepare a cart for response.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return \WP_REST_Response
	 */
	protected function prepare_item_for_response( \ITE_Cart $cart ) {

		$data = $this->serializer->serialize( $cart );

		$response = new \WP_REST_Response( $data );

		$shipping_methods = new Shipping_Methods();
		$shipping_methods->set_parent( $this );
		$response->add_link( 'shipping_methods', r\get_rest_url( $shipping_methods, array( 'cart_id' => $cart->get_id() ) ) );

		if ( $cart->get_customer() && ! $cart->get_customer() instanceof \IT_Exchange_Guest_Customer ) {
			$response->add_link(
				'customer',
				r\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Customer' ),
					array( 'customer_id' => $cart->get_customer()->ID )
				),
				array( 'embeddable' => true )
			);
		}

		$response->add_link(
			'purchase-methods',
			r\get_rest_url(
				$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Cart\Purchase' ),
				array( 'cart_id' => $cart->get_id() )
			),
			array( 'embeddable' => true )
		);

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
