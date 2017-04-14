<?php
/**
 * Product Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Product;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Product
 */
class Serializer {

	/**
	 * Serialize a product.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Product $product
	 * @param string               $context
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Product $product, $context = 'view' ) {

		$p       = $product;
		$on_sale = it_exchange_is_product_sale_active( $p );

		$inventory = $product->get_feature( 'inventory' );

		$has_availability = $p->get_feature( 'availability', array( 'setting' => 'enabled' ) );
		$has_start        = $p->get_feature( 'availability', array( 'setting' => 'enabled', 'type' => 'start' ) );
		$has_end          = $p->get_feature( 'availability', array( 'setting' => 'enabled', 'type' => 'end' ) );
		$start            = $p->get_feature( 'availability', array( 'setting' => 'start', 'format' => 'mysql' ) );
		$end              = $p->get_feature( 'availability', array( 'setting' => 'end', 'format' => 'mysql' ) );

		$data = array(
			'id'             => $p->ID,
			'slug'           => $p->post_name,
			'title'          => get_the_title( $p->ID ),
			'content'        => array(
				'raw'      => $p->post_content,
				'rendered' => apply_filters( 'the_content', $p->post_content ),
			),
			'description'    => array(
				'raw'      => $p->get_feature( 'description' ),
				'rendered' => apply_filters( 'the_content', $p->get_feature( 'description' ) )
			),
			'date'           => $p->post_date_gmt ? \iThemes\Exchange\REST\format_rfc339( $p->post_date_gmt ) : '',
			'type'           => it_exchange_get_product_type( $p ),
			'visible'        => get_post_meta( $p->ID, '_it-exchange-visibility', true ) !== 'hidden',
			'price'          => $p->get_feature( $on_sale ? 'sale-price' : 'base-price' ),
			'regular_price'  => $p->get_feature( 'base-price' ),
			'sale_price'     => $p->get_feature( 'sale-price' ),
			'on_sale'        => $on_sale,
			'quantity'       => array(
				'remaining' => $inventory,
				'max'       => it_exchange_get_max_product_quantity_allowed( $p ),
				'in_stock'  => (bool) ( $p->get_feature( 'inventory', array( 'setting' => 'enabled' ) ) === 'yes' ? $inventory : 1 ),
			),
			'available'      => array(
				'now'   => $has_availability ? it_exchange_is_product_available( $p ) : true,
				'start' => $has_start && $start ? \iThemes\Exchange\REST\format_rfc339( $start ) : '',
				'end'   => $has_end && $end ? \iThemes\Exchange\REST\format_rfc339( $end ) : '',
			),
			'featured_media' => 0,
			'attachments'    => array(),
		);

		if ( $product->supports_feature( 'product-images' ) && $images = $product->get_feature( 'product-images' ) ) {

			$data['featured_media'] = $images[0];

			foreach ( $images as $image ) {
				$data['attachments'][] = $image;
			}
		}

		if ( $context === 'stats' ) {
			$data['stats']['sales'] = it_exchange_get_transactions_for_product( $product, 'count' );
		}

		return $data;
	}

	/**
	 * Generate links.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Product $product
	 *
	 * @return array
	 */
	public function generate_links( \IT_Exchange_Product $product ) {

		$links = array();

		if ( $product->supports_feature( 'product-images' ) && $images = $product->get_feature( 'product-images' ) ) {

			$image_url = rest_url( 'wp/v2/media/' . $images[0] );

			$links['https://api.w.org/featuredmedia'][] = array(
				'href'       => $image_url,
				'embeddable' => true,
			);

			foreach ( $images as $image ) {
				$image_url = rest_url( 'wp/v2/media/' . $image );

				$links['https://api.w.org/attachment'][] = array(
					'href'       => $image_url,
					'embeddable' => true,
				);
			}
		}

		return $links;
	}

	/**
	 * Get the product schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'title'      => 'product',
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'description' => __( 'Unique ID for this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'slug'           => array(
					'description' => __( 'An alphanumeric identifier for the object, based on its title.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'          => array(
					'description' => __( 'The product title.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'content'        => array(
					'description' => __( 'The main content describing this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'type'        => 'string',
							'description' => __( 'The raw content, ready for editing.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'type'        => 'string',
							'description' => __( 'The fully rendered content.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit' )
						)
					)
				),
				'description'    => array(
					'description' => __( 'A short description describing this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'type'        => 'string',
							'description' => __( 'The raw description, ready for editing.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'type'        => 'string',
							'description' => __( 'The fully rendered description.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit', 'embed' )
						)
					)
				),
				'date'           => array(
					'description' => __( 'The date the product was created.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'           => array(
					'description' => __( 'The product type.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'enum'        => array_keys( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'required'    => true,
				),
				'visible'        => array(
					'description' => __( 'Whether this product is visible in the store listing.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view', 'edit' ),
				),
				'price'          => array(
					'description' => __( 'The current price of this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'readonly'    => true,
					'minimum'     => 0,
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'regular_price'  => array(
					'description' => __( 'The regular price of this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'required'    => true,
					'minimum'     => 0,
					'context'     => array( 'view', 'edit' )
				),
				'sale_price'     => array(
					'description' => __( 'The sale price of this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'minimum'     => 0,
					'context'     => array( 'edit' ),
				),
				'on_sale'        => array(
					'description' => __( 'Whether the product is currently on sale.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
				),
				'quantity'       => array(
					'description' => __( 'The quantity available for purchase.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'remaining' => array(
							'description' => __( 'The quantity remaining for purchase.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'edit' ),
							'minimum'     => 0,
						),
						'max'       => array(
							'description' => __( 'The maximum quantity available for purchase by a customer.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit', 'embed' ),
							'oneOf'       => array(
								array( 'type' => 'string', 'enum' => array( '' ), ),
								array( 'type' => 'integer', 'minimum' => 0, )
							),
						),
						'in_stock'  => array(
							'description' => __( 'Whether the product is currently in stock.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'boolean',
							'readonly'    => true,
							'context'     => array( 'view', 'edit', 'embed' )
						),
					)
				),
				'available'      => array(
					'description' => __( 'Whether this product is available for purchase.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'now'   => array(
							'description' => __( 'Whether this product is available now.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
						'start' => array(
							'description' => __( 'The starting date the product is available.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'edit' )
						),
						'end'   => array(
							'description' => __( 'The date after which the product is no-longer available.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'date-time',
							'context'     => array( 'edit' )
						),
					)
				),
				'featured_media' => array(
					'description' => __( 'The featured media for this product.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'oneOf'       => array(
						array(
							'description' => __( 'A URL to video or audio describing the product.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'uri',
						),
						array(
							'description' => __( 'The attachment id of the featured image.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'minimum'     => 0,
						)
					)
				),
				'attachments'    => array(
					'description' => __( 'All images describing this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'    => 'integer',
						'minimum' => 0,
					)
				),
				'stats'          => array(
					'description' => __( 'Stats about this product.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'stats' ),
					'readonly'    => true,
					'properties'  => array(
						'sales' => array(
							'type'        => 'integer',
							'description' => __( 'The total number of times this product has been sold and cleared.', 'it-l10n-ithemes-exchange' )
						)
					)
				)
			)
		);
	}
}