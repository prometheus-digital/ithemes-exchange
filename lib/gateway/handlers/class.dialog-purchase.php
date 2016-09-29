<?php
/**
 * Dialog Purchase Request Handler.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Dialog_Purchase_Request_Handler
 */
abstract class ITE_Dialog_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 */
	public function render_payment_button( ITE_Gateway_Purchase_Request $request ) {
		return it_exchange_generate_purchase_dialog( $this->gateway->get_slug(), $this->get_dialog_options() );
	}

	/**
	 * Get the purchase dialog options.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_dialog_options() {
		return array(
			'purchase-label'  => $this->get_payment_button_label(),
			'form-attributes' => array(
				'nonce-action' => $this->get_nonce_action()
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		return array(
			'method' => 'dialog'
		);
	}
}