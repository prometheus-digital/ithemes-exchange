<?php
/**
 * Address Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Customer\Address;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Customer\Address
 */
class Serializer {

	/**
	 * Serialize an address.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Saved_Address $address
	 *
	 * @return array
	 */
	public function serialize( \ITE_Saved_Address $address ) {

		$used = $address->get_last_used_date();

		return array_merge( array(
			'id'        => $address->get_pk(),
			'label'     => $address->label,
			'type'      => $address->get_type(),
			'last_used' => $used ? \iThemes\Exchange\REST\format_rfc339( $used ) : '',
		), $address->to_array() );
	}

	/**
	 * Get the schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'customer-address',
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'id'           => array(
					'description' => __( 'The unique identifier for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'label'        => array(
					'description' => __( 'User provided address label.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' )
				),
				'first-name'   => array(
					'description' => __( 'The first name for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'last-name'    => array(
					'description' => __( 'The last name for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'company-name' => array(
					'description' => __( 'The company name for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'address1'     => array(
					'description' => __( 'The address line 1 for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'address2'     => array(
					'description' => __( 'The address line 2 for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'city'         => array(
					'description' => __( 'The city for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'state'        => array(
					'description' => __( 'The state two-letter abbreviation for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'country'      => array(
					'description' => __( 'The country two-letter abbreviation for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'zip'          => array(
					'description' => __( 'The zip code for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'email'        => array(
					'description' => __( 'The email address for the address.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'oneOf'       => array(
						array(
							'type'   => 'string',
							'format' => 'email',
						),
						array(
							'type' => 'string',
							'enum' => array( '' )
						),
					)
				),
				'phone'        => array(
					'description' => __( 'The phone number for the address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'type'         => array(
					'description' => __( 'The address type.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'enum'        => array( '', 'both', 'billing', 'shipping' ),
				),
				'last_used'    => array(
					'description' => __( 'The date the address was last used.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'edit' ),
					'oneOf'       => array(
						array(
							'type'   => 'string',
							'format' => 'date-time',
						),
						array(
							'type' => 'string',
							'enum' => array( '' )
						)
					)
				)
			)
		);
	}
}