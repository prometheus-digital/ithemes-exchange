<?php
/**
 * IFrame purchase request handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_IFrame_Purchase_Request_Handler
 */
abstract class ITE_IFrame_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * Retrieve any inline JS necessary for completing a checkout.
	 *
	 * The inline JS should listen for an Exchange Event Manager hook matching iFramePurchaseBegin.gatewaySlug,
	 * where 'gatewaySlug' is the slug of your gateway. For example 'stripe' would be iFramePurchaseBegin.stripe
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	protected abstract function get_inline_js( ITE_Gateway_Purchase_Request $request );

	/**
	 * @inheritDoc
	 */
	protected function get_html_before_form_end( ITE_Gateway_Purchase_Request $request ) {

		$html = parent::get_html_before_form_end( $request );

		if ( $this->get_gateway()->can_handle( 'tokenize' ) ) {
			$html .= $this->get_token_selector_html( $request );
		}

		$html .= $this->get_inline_js( $request );

		return $html;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_form_element_attributes( ITE_Gateway_Purchase_Request $request ) {
		return array_merge( parent::get_form_element_attributes( $request ), array(
			'data-type' => 'iframe',
		) );
	}

	/**
	 * Get token selector HTML.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	protected function get_token_selector_html( ITE_Gateway_Purchase_Request $request ) {
		$tokens = $request->get_customer()->get_tokens( array( 'gateway' => $this->get_gateway()->get_slug() ) );

		if ( ! $tokens->count() ) {
			return '';
		}

		$method = $this->get_gateway()->get_slug();
		$html   = "<div class=\"it-exchange-payment-tokens-selector--list\" style=\"display: none\" data-method=\"{$method}\">";

		foreach ( $tokens as $token ) {

			$label = $token->get_label();

			$selected = checked( $token->primary, true, false );

			$html .= '<div class="it-exchange-payment-tokens-selector--payment-token">';
			$html .= "<label><input type='radio' name='purchase_token' value='{$token->ID}' {$selected}>&nbsp;{$label}</label>";
			$html .= '</div>';
		}

		$new_method = __( 'New Payment Method', 'it-l10n-ithemes-exchange' );

		$html .= '<div class="it-exchange-payment-tokens-selector--payment-token it-exchange-payment-tokens-selector--add-new">';
		$html .= "<label><input type='radio' name='purchase_token' value='new_method' id='new-method-{$this->get_gateway()->get_slug()}'> ";
		$html .= $new_method;
		$html .= '</label>';
		$html .= '</div>';

		$label = __( 'Complete Purchase', 'it-l10n-ithemes-exchange' );
		$html .= "<input type=\"submit\" class=\"it-exchange-checkout-complete-purchase\" value=\"{$label}\"><br>";

		$label = __( 'Cancel', 'it-l10n-ithemes-exchange' );
		$html .= "<a href=\"#\" class=\"it-exchange-checkout-cancel-complete\">{$label}</a>";

		$html .= '</div>';

		return $html;
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		$data = array_merge_recursive( parent::get_data_for_REST( $request ), array(
			'html' => $this->render_payment_button( $request ),
		) );

		$data['method'] = 'iframe';

		return $data;
	}
}
