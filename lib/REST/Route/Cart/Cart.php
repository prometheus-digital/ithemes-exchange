<?php
/**
 * Contains the cart route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;

/**
 * Class Cart
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Cart implements Getable, Putable, Deletable {

	/** @var Item_Serializer */
	private $serializer;

	/** @var \iThemes\Exchange\REST\Route\Cart\Items[] */
	private $item_routes;

	/**
	 * Cart constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Cart\Item_Serializer $serializer
	 * @param \iThemes\Exchange\REST\Route\Cart\Items[]         $item_routes
	 */
	public function __construct( Item_Serializer $serializer, array $item_routes ) {
		$this->serializer  = $serializer;
		$this->item_routes = $item_routes;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {
		return $this->prepare_item_for_response( it_exchange_get_cart( $request['id'] ), $request );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \IT_Exchange_Customer $user ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( \WP_REST_Request $request ) {
		$cart = it_exchange_get_cart( $request['id'] );

		if ( $cart->get_billing_address() ? $cart->get_billing_address()->to_array() : array() !== $request['billing'] ) {
			$cart->set_billing_address( $request['billing'] ? new \ITE_In_Memory_Address( $request['billing'] ) : null );
		}

		if ( $cart->get_shipping_address() ? $cart->get_shipping_address()->to_array() : array() !== $request['shipping'] ) {
			$cart->set_shipping_address( $request['shipping'] ? new \ITE_In_Memory_Address( $request['shipping'] ) : null );
		}

		/** @noinspection NotOptimalIfConditionsInspection */
		if (
			array_key_exists( 'shipping_method', $request->get_params() ) &&
			array_key_exists( 'id', $request['shipping_method'] )
		) {
			$cart->set_shipping_method( $request['shipping_method']['id'] );
		}

		return $this->prepare_item_for_response( $cart, $request );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( \WP_REST_Request $request, \IT_Exchange_Customer $user ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( \WP_REST_Request $request ) {
		$cart = it_exchange_get_cart( $request['id'] );
		$cart->empty_cart();

		return new \WP_HTTP_Response( '', 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( \WP_REST_Request $request, \IT_Exchange_Customer $user ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<id>\w+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array();
	}

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
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request      $request
	 * @param \IT_Exchange_Customer $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( \WP_REST_Request $request, \IT_Exchange_Customer $user ) {

		$url_params = $request->get_url_params();

		if ( ! $cart = it_exchange_get_cart( $url_params['id'] ) ) {
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

		if ( $cart->get_customer() && $cart->get_customer()->id === $user->id ) {
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
	 * @since 1.36.0
	 *
	 * @param \ITE_Cart        $cart
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	protected function prepare_item_for_response( \ITE_Cart $cart, \WP_REST_Request $request ) {

		$data = array(
			'id'               => $cart->get_id(),
			'customer'         => $cart->get_customer() ? $cart->get_customer()->id : 0,
			'shipping_method'  => new \stdClass(),
			'shipping_address' => null,
			'billing_address'  => null,
			'subtotal'         => it_exchange_get_cart_subtotal( false, array( 'cart' => $cart ) ),
			'total'            => $cart->calculate_total(),
		);

		if ( $shipping_method = $cart->get_shipping_method() ) {
			$data['shipping_method'] = array(
				'id'    => $shipping_method->slug,
				'label' => $shipping_method->label,
			);
		} elseif ( ! it_exchange_cart_requires_shipping( $cart ) ) {
			unset( $data['shipping_method'] );
		}

		if ( $cart->get_billing_address() ) {
			$data['billing_address'] = $cart->get_billing_address()->to_array();
		}

		if ( $cart->get_shipping_address() ) {
			$data['shipping_address'] = $cart->get_shipping_address()->to_array();
		}

		$items = array();

		$request = new \WP_REST_Request( 'GET' );
		$request->set_param( 'id', $cart->get_id() );

		foreach ( $this->item_routes as $type => $item_route ) {
			foreach ( $cart->get_items( $type ) as $item ) {
				$items[] = r\response_to_array( $item_route->prepare_item_for_response( $item, $request ) );
			}
		}

		$data['items'] = $items;

		$totals_info  = array();
		$summary_only = $cart->get_items( '', true )->summary_only();

		if ( $summary_only->count() ) {

			$totals = $summary_only->segment();

			foreach ( $totals as $total_by_type ) {
				$segmented = $total_by_type->segment( function ( \ITE_Line_Item $item ) {
					return get_class( $item ) . $item->get_name();
				} );

				foreach ( $segmented as $segment ) {
					$type        = $segment->first()->get_type();
					$name        = $segment->first()->get_name();
					$total       = $segment->total();
					$description = $segment->filter( function ( \ITE_Line_Item $item ) {
						return trim( $item->get_description() !== '' );
					} )->first();

					$totals_info[] = array(
						'slug'        => $type,
						'label'       => $name,
						'total'       => $total,
						'description' => $description ? $description->get_description() : ''
					);
				}
			}
		}

		$data['total_lines'] = $totals_info;

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart',
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'The unique id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'customer'         => array(
					'description' => __( 'The customer id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'shipping_method'  => array(
					'description' => __( 'The selected shipping method for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id'    => array(
							'description' => __( 'The unique id for this shipping method.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'label' => array(
							'description' => __( 'The label for this shipping method.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					)
				),
				'billing_address'  => array(
					'description' => __( 'The billing address for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'first-name'   => array(
							'description' => __( 'The first name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'last-name'    => array(
							'description' => __( 'The last name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'company-name' => array(
							'description' => __( 'The company name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address1'     => array(
							'description' => __( 'The address line 1 of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address2'     => array(
							'description' => __( 'The address line 2 of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'city'         => array(
							'description' => __( 'The city of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'state'        => array(
							'description' => __( 'The state two-letter abbreviation of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'country'      => array(
							'description' => __( 'The country two-letter abbreviation of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'zip'          => array(
							'description' => __( 'The zip code of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email'        => array(
							'description' => __( 'The email address of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'format'      => 'email',
						),
						'phone'        => array(
							'description' => __( 'The phone number of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					)
				),
				'shipping_address' => array(
					'description' => __( 'The shipping address for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'first-name'   => array(
							'description' => __( 'The first name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'last-name'    => array(
							'description' => __( 'The last name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'company-name' => array(
							'description' => __( 'The company name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address1'     => array(
							'description' => __( 'The address line 1 of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address2'     => array(
							'description' => __( 'The address line 2 of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'city'         => array(
							'description' => __( 'The city of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'state'        => array(
							'description' => __( 'The state two-letter abbreviation of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'country'      => array(
							'description' => __( 'The country two-letter abbreviation of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'zip'          => array(
							'description' => __( 'The zip code of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email'        => array(
							'description' => __( 'The email address of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'format'      => 'email',
						),
						'phone'        => array(
							'description' => __( 'The phone number of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					)
				),
				'items'            => array(
					'description' => __( 'List of all line items in the cart.' ),
					'type'        => 'list',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'subtotal'         => array(
					'description' => __( 'The subtotal of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'total'            => array(
					'description' => __( 'The total of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'total_lines'      => array(
					'description' => __( 'Line item totals of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'items'       => array(
						'title'      => __( 'Line item total lines.', 'it-l10n-ithemes-exchange' ),
						'type'       => 'object',
						'properties' => array(
							'slug'        => array(
								'description' => __( 'The slug of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'label'       => array(
								'description' => __( 'The label of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'description' => array(
								'description' => __( 'The description of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'total'       => array(
								'description' => __( 'The total of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'float',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
						),
					),
				),
			),
		);
	}
}