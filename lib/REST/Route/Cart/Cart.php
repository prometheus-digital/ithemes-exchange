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
class Cart implements Getable, Putable, Deletable {

	/** @var array */
	private $schema = array();

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
			array( 'status' => \WP_Http::UNAUTHORIZED )
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

		$data = array(
			'id'               => $cart->get_id(),
			'customer'         => $cart->get_customer() ? $cart->get_customer()->id : 0,
			'is_main'          => $cart->is_main(),
			'shipping_address' => null,
			'billing_address'  => null,
			'subtotal'         => it_exchange_get_cart_subtotal( false, array( 'cart' => $cart ) ),
			'total'            => it_exchange_get_cart_total( false, array( 'cart' => $cart ) ),
		);

		if ( $cart->get_billing_address() ) {
			$data['billing_address'] = $cart->get_billing_address()->to_array();
		}

		if ( $cart->get_shipping_address() ) {
			$data['shipping_address'] = $cart->get_shipping_address()->to_array();
		}

		$items = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {
			foreach ( $cart->get_items( $item_type->get_type() ) as $item ) {
				$items[] = $item_type->get_rest_serializer()->serialize( $item, $cart );
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

		$response = new \WP_REST_Response( $data );

		$shipping_methods = new Shipping_Methods();
		$shipping_methods->set_parent( $this );
		$response->add_link( 'shipping_methods', r\get_rest_url( $shipping_methods, array( 'cart_id' => $cart->get_id() ) ) );

		if ( $cart->get_customer() && ! $cart->get_customer() instanceof \IT_Exchange_Guest_Customer ) {
			$response->add_link(
				'customer',
				r\get_rest_url( new r\Route\Customer\Customer(), array( 'customer_id' => $cart->get_customer()->ID ) ),
				array( 'embeddable' => true )
			);
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {

		if ( $this->schema ) {
			return $this->schema;
		}

		$item_definitions = array();
		$item_references  = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {

			$type        = $item_type->get_type();
			$title       = "cart_item_{$type}";
			$item_schema = $item_type->get_rest_serializer()->get_schema();
			unset( $item_schema['title'], $item_schema['$schema'] );

			$item_references[]['$ref']  = "#definitions/{$title}";
			$item_definitions[ $title ] = $item_schema;
		}

		$this->schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'definitions' => $item_definitions,
			'title'       => 'cart',
			'type'        => 'object',
			'properties'  => array(
				'id'               => array(
					'description' => __( 'The unique id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer'         => array(
					'description' => __( 'The customer id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'is_main'          => array(
					'description' => __( 'Is this the main cart for the customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'default'     => true,
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
					'oneOf' => $item_references,
				),
				'subtotal'         => array(
					'description' => __( 'The subtotal of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total'            => array(
					'description' => __( 'The total of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total_lines'      => array(
					'description' => __( 'Line item totals of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
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
								'type'        => 'number',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
						),
					),
				),
			),
		);

		return $this->schema;
	}
}
