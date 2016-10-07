<?php
/**
 * Token Serializer.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Token;

/**
 * Class Serializer
 * @package iThemes\Exchange\REST\Route\Customer\Token
 */
class Serializer {

	/**
	 * Serialize the payment token.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Payment_Token $token
	 *
	 * @return array
	 */
	public function serialize( \ITE_Payment_Token $token ) {
		return array(
			'id'       => $token->get_pk(),
			'gateway'  => $token->get_raw_attribute( 'gateway' ),
			'label'    => array(
				'raw'      => $token->label,
				'rendered' => $token->get_label()
			),
			'redacted' => $token->redacted,
			'primary'  => (bool) $token->primary,
		);
	}
}