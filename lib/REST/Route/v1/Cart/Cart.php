<?php
/**
 * Contains the cart route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;

/**
 * Class Cart
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Cart extends r\Route\Base implements Getable, Putable, Deletable, r\RouteObjectExpandable {

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
		return $this->prepare_item_for_response( $request->get_route_object( 'cart_id' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {
		return $this->permission_check( $request, $scope );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );

		if ( $e = $this->handle_address_update( $cart, $request['billing_address'], 'billing' ) ) {
			return $e;
		}

		if ( $e = $this->handle_address_update( $cart, $request['shipping_address'], 'shipping' ) ) {
			return $e;
		}

		if ( is_array( $request['meta'] ) ) {
			foreach ( $request['meta'] as $key => $value ) {
				if ( ( $config = \ITE_Cart_Meta_Registry::get( $key ) ) && $config->editable_in_rest() ) {
					$value === null ? $cart->remove_meta( $key ) : $cart->set_meta( $key, $value );
				}
			}
		}

		return $this->prepare_item_for_response( $cart );
	}

	/**
	 * Handle a meta update.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param array     $meta
	 */
	protected function handle_meta( \ITE_Cart $cart, array $meta ) {
		foreach ( $meta as $key => $value ) {
			$config = \ITE_Cart_Meta_Registry::get( $key );

			if ( ! $config || ! $config->editable_in_rest() ) {
				continue;
			}

			if ( ! $config->has_rest_edit_permission( $this->get_manager()->get_auth_scope() ) ) {
				continue; // Throw permission error
			}

			if ( $value !== null ) {
				$cart->set_meta( $key, $value );
			} else {
				$cart->remove_meta( $key );
			}
		}
	}

	/**
	 * Handle an address update.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param array|int $input
	 * @param string    $type
	 *
	 * @return null|\WP_Error
	 */
	protected function handle_address_update( \ITE_Cart $cart, $input, $type ) {

		/** @var \ITE_Location $current */
		$current = call_user_func( array( $cart, "get_{$type}_address" ) );
		$address = null;

		if ( is_int( $input ) ) {
			$address = $this->update_address_by_id( $cart, $input, $current );
		} elseif ( is_array( $input ) && isset( $input['id'] ) ) {
			$address = $this->update_address_by_id( $cart, (int) $input['id'], $current );
		} elseif ( is_array( $input ) ) {
			$address = new \ITE_In_Memory_Address( $input );

			if ( $address->equals( $current ) ) {
				$address = null;
			}
		}

		if ( ! $address instanceof \ITE_Location ) {
			return $address;
		}

		if ( call_user_func( array( $cart, "set_{$type}_address" ), $address ) ) {
			return null;
		}

		return new \WP_Error(
			'it_exchange_rest_address_failed_validation',
			__( 'Address failed to verification.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => \WP_Http::BAD_REQUEST )
		);
	}

	/**
	 * Update an address by id.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param int       $id
	 * @param           $current
	 *
	 * @return \ITE_Saved_Address|\WP_Error|null
	 */
	protected function update_address_by_id( \ITE_Cart $cart, $id, $current ) {

		if ( $current instanceof \ITE_Saved_Address && $current->get_pk() === $id ) {
			return null;
		}

		$address = \ITE_Saved_Address::get( $id );

		if ( ! $address || $address->is_trashed() || ! $address->customer || $address->customer->get_ID() != $cart->get_customer()->get_ID() ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_address',
				__( 'Invalid address ID.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		return $address;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) {
		return $this->permission_check( $request, $scope );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$cart->empty_cart();

		return new \WP_REST_Response( '', 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {
		return $this->permission_check( $request, $scope );
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
	public function get_route_object_map() { return array( 'cart_id' => 'it_exchange_get_cart' ); }

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
	 * @param AuthScope                      $scope
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( Request $request, AuthScope $scope ) {

		/** @var \ITE_Cart $cart */
		if ( ! $cart = $request->get_route_object( 'cart_id' ) ) {
			return Errors::not_found();
		}

		if ( ! $scope->can( 'it_edit_cart', $cart ) ) {
			return Errors::cannot_edit();
		}

		return true;
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

		$shipping_methods = new Shipping();
		$shipping_methods->set_parent( $this );

		if ( $cart->requires_shipping() ) {
			$response->add_link(
				'shipping_methods',
				r\get_rest_url( $shipping_methods, array( 'cart_id' => $cart->get_id() ) )
			);
		}

		if ( $cart->get_customer() && ! $cart->get_customer() instanceof \IT_Exchange_Guest_Customer ) {
			$response->add_link(
				'customer',
				r\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Customer' ),
					array( 'customer_id' => $cart->get_customer()->ID )
				),
				array( 'embeddable' => true )
			);
		}

		$response->add_link(
			'purchase-methods',
			r\get_rest_url(
				$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Cart\Purchase' ),
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
