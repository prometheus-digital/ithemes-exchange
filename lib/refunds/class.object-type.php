<?php
/**
 * Object Type for Refunds.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Refund_Object_Type
 */
class ITE_Refund_Object_Type extends ITE_Table_With_Meta_Object_Type implements ITE_RESTful_Object_Type {

	/**
	 * @inheritDoc
	 */
	protected function get_model() {
		return new ITE_Refund();
	}

	/**
	 * @inheritDoc
	 */
	public function get_collection_route() {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\Transaction\Refunds\Refunds'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_route( $object_id ) {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\Customer\Refunds\Refund'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return __( 'Refund', 'it-l10n-ithemes-exchange' );
	}
}