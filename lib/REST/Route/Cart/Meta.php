<?php
/**
 * Contains the meta route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Meta
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Meta extends Base implements Getable, Putable {

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$cart = $request->get_cart();

		return new \WP_REST_Response( $this->format_meta_for_cart( $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$cart = $request->get_cart();

		$keys = array();

		foreach ( $request->get_body_params() as $entry ) {

			$keys[ $entry['key'] ] = 1;

			if ( ! ( $meta = \ITE_Cart_Meta_Registry::get( $entry['key'] ) ) || ! $meta->editable_in_rest() ) {
				continue;
			}

			if ( ! $cart->has_meta( $entry['key'] ) || $cart->get_meta( $entry['key'] ) !== $entry['value'] ) {
				$cart->set_meta( $entry['key'], $entry['value'] );
			}
		}

		foreach ( \ITE_Cart_Meta_Registry::editable_in_rest() as $editable ) {
			if ( empty( $keys[ $editable->get_key() ] ) ) {
				$cart->remove_meta( $editable->get_key() );
			}
		}

		return new \WP_REST_Response( $this->format_meta_for_cart( $cart ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * Format metadata for cart.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return array
	 */
	protected function format_meta_for_cart( \ITE_Cart $cart ) {

		$data     = array();
		$all_meta = $cart->get_all_meta();

		foreach ( $all_meta as $key => $value ) {
			if ( ( ! $meta = \ITE_Cart_Meta_Registry::get( $key ) ) || ! $meta->show_in_rest() ) {
				continue;
			}

			$data[] = array(
				'key'      => $key,
				'value'    => $value,
				'type'     => $meta->get_type(),
				'editable' => $meta->editable_in_rest(),
			);
		}

		return $data;
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
		return 'meta/';
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
		return array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'cart-meta',
			'type'    => 'array',
			'items'   => array(
				'type'       => 'object',
				'properties' => array(
					'key'      => array(
						'type'        => 'string',
						'description' => __( 'Meta key', 'it-l10n-ithemes-exchange' ),
						'readonly'    => true,
					),
					'value'    => array(
						'description' => __( 'Meta value', 'it-l10n-ithemes-exchange' ),
					),
					'type'     => array(
						'type'        => 'string',
						'description' => __( 'Meta value type', 'it-l10n-ithemes-exchange' ),
						'readonly'    => true,
					),
					'editable' => array(
						'type'        => 'bool',
						'description' => __( 'Whether this value can be edited or deleted.', 'it-l10n-ithemes-exchange' ),
						'readonly'    => true,
					)
				)
			)
		);
	}
}