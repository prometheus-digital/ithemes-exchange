<?php
/**
 * Address Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Address;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\Customer\Address
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
		return array_merge( array(
			'id'    => $address->get_pk(),
			'label' => $address->label,
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
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'address',
			'type'       => 'object',
			'properties' => array(
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
			)
		);
	}
}