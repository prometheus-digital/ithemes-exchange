<?php
/**
 * Object Type for Tokens.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Token_Object_Type
 */
class ITE_Payment_Token_Object_Type extends ITE_Table_With_Meta_Object_Type implements ITE_RESTful_Object_Type {

	/**
	 * @inheritDoc
	 */
	protected function get_model() {
		return new ITE_Payment_Token();
	}

	/**
	 * @inheritDoc
	 */
	public function get_collection_route() {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\v1\Customer\Token\Tokens'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_route( $object_id ) {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\v1\Customer\Token\Token'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return __( 'Payment Token', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'payment-token'; }
}
