<?php
/**
 * Dialog Purchase Request Handler.
 *
 * @since   2.0.0
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
		return it_exchange_generate_purchase_dialog(
			       $this->get_gateway()->get_slug(), $this->get_dialog_options()
		       ) . $this->get_html_before_form_end( $request );
	}

	/**
	 * Get the purchase dialog options.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_dialog_options() {
		return array(
			'purchase-label'  => $this->get_payment_button_label(),
			'form-attributes' => array(
				'nonce-action' => $this->get_nonce_action(),
				'nonce-field'  => '_wpnonce'
			)
		);
	}

	/**
	 * Get a purchase dialog controller.
	 *
	 * @since 2.0.0
	 *
	 * @return \IT_Exchange_Purchase_Dialog
	 */
	public function get_dialog_controller() {
		return new IT_Exchange_Purchase_Dialog( $this->get_gateway()->get_slug(), $this->get_dialog_options() );
	}

	/**
	 * @inheritDoc
	 */
	public function build_factory_args_from_global_state( ITE_Cart $cart, $state ) {

		$factory_args = parent::build_factory_args_from_global_state( $cart, $state );

		if ( ! empty( $factory_args['token'] ) && $this->get_gateway()->can_handle( 'tokenize' ) ) {
			return $factory_args;
		}

		if ( $error_message = $this->get_dialog_controller()->is_submitted_form_valid( false ) ) {
			throw new InvalidArgumentException( $error_message );
		}

		if ( $this->get_gateway()->can_handle( 'tokenize' ) ) {
			if ( empty( $factory_args['tokenize'] ) ) {
				$factory_args['tokenize'] = $this->get_dialog_controller()->get_card_from_submitted_values();
			}
		} else {
			if ( empty( $factory_args['card'] ) ) {
				$factory_args['card'] = $this->get_dialog_controller()->get_card_from_submitted_values();
			}
		}

		return $factory_args;
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		return array(
			'method'  => 'dialog',
			'accepts' => array( 'card' )
		);
	}
}
