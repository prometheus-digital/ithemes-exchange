<?php
/**
 * IFrame purchase request handler.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_IFrame_Purchase_Request_Handler
 */
abstract class ITE_IFrame_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * Retrieve any inline JS necessary for completing a checkout.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	protected abstract function get_inline_js( ITE_Gateway_Purchase_Request $request );

	/**
	 * @inheritDoc
	 */
	protected function get_html_before_form_end( ITE_Gateway_Purchase_Request $request ) {
		return parent::get_html_before_form_end( $request ) . "<script type='text'javascript'>{$this->get_inline_js( $request )}</script>";
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		return array(
			'method' => 'iframe',
			'html'   => $this->render_payment_button( $request ),
		);
	}
}