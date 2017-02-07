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

		$args = parent::build_factory_args_from_global_state( $cart, $state );

		if ( $this->get_gateway()->can_handle( 'tokenize' ) && ( ! empty( $args['token'] ) || ! empty( $args['tokenize'] ) ) ) {
			return $args;
		}

		if ( ! empty( $args['one_time_token'] ) && ( $this->get_gateway()->get_handler_by_request_name( 'tokenize' ) ?: $this instanceof ITE_Gateway_JS_Tokenize_Handler ) ) {
		    return $args;
        }

		if ( $error_message = $this->get_dialog_controller()->is_submitted_form_valid( false ) ) {
			throw new InvalidArgumentException( $error_message );
		}

		if ( $this->get_gateway()->can_handle( 'tokenize' ) ) {
			if ( empty( $args['tokenize'] ) ) {
				$args['tokenize'] = $this->get_dialog_controller()->get_card_from_submitted_values();
			}
		} else {
			if ( empty( $args['card'] ) ) {
				$args['card'] = $this->get_dialog_controller()->get_card_from_submitted_values();
			}
		}

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_html_before_form_end( ITE_Gateway_Purchase_Request $request ) {
		$html = parent::get_html_before_form_end( $request );

		$handler = $this->get_gateway()->get_handler_by_request_name( 'tokenize' ) ?: $this;

		if ( $handler instanceof ITE_Gateway_JS_Tokenize_Handler && $handler->is_js_tokenizer_configured() ) {
			$html .= $this->generate_tokenize_js( $request, $handler );
		}

		return $html;
	}

	/**
	 * Generate tokenize JS.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request    $request
	 * @param ITE_Gateway_JS_Tokenize_Handler $tokenizer
	 *
	 * @return string
	 */
	protected function generate_tokenize_js( ITE_Gateway_Purchase_Request $request, ITE_Gateway_JS_Tokenize_Handler $tokenizer ) {

		ob_start();
		?>
        <script type="text/javascript">
			(function ( $, gateway, inSuperWidget, canMakeTokens, tokenize ) {
				"use strict";

				if ( inSuperWidget ) {

					if ( !itExchange || !itExchange.hooks ) {
						throw new Error( 'itExchange.hooks not available.' );
					}

					itExchange.hooks.addAction( 'itExchangeSW.preSubmitPurchaseDialog_' + gateway, function ( deferred ) {

						if ( !$( '#new-method-' + gateway ).is( ':checked' ) && canMakeTokens && itExchange.common.config.currentUser > 0 ) {

							deferred.resolve( { alreadyProcessed: true } );

							return;
						}

						var $form = $( 'form.it-exchange-purchase-dialog-' + gateway );

						if ( $( "input[name='to_tokenize'], input[name='one_time_token']", $form ).length ) {
							deferred.resolve( { alreadyProcessed: true } );

							return;
						}

						tokenizeThenAddInput( deferred );
					} );
				} else {
					$( document ).on( 'submit', 'form.it-exchange-purchase-dialog-' + gateway, function ( e ) {

						if ( !$( '#new-method-' + gateway ).is( ':checked' ) && canMakeTokens && itExchange.common.config.currentUser > 0 ) {
							return;
						}

						var $form = $( this );

						if ( $( "input[name='to_tokenize'], input[name='one_time_token']", $form ).length ) {
							return;
						}

						e.preventDefault();

						var $submit = $( ':submit', $form );
						$submit.data( 'old-value', $submit.val() );
						$submit.val( 'Processing' ).attr( 'disabled', true );

						var deferred = $.Deferred();
						tokenizeThenAddInput( deferred );

						deferred.done( function () {
							$form.submit();
						} ).fail( function () {
							$submit.removeAttr( 'disabled' );
							$submit.val( $submit.data( 'old-value' ) );
						} );
					} );
				}

				function tokenizeThenAddInput( deferred ) {

					var $form = $( 'form.it-exchange-purchase-dialog-' + gateway );

					var name = $( "#it-exchnage-purchase-dialog-cc-first-name-for-" + gateway ).val()
						+ ' ' +
						$( "#it-exchnage-purchase-dialog-cc-last-name-for-" + gateway ).val();

					var data = {
						name  : name,
						number: $( '#it-exchnage-purchase-dialog-cc-number-for-' + gateway ).val().replace( /\s+/g, '' ),
						cvc   : $( '#it-exchnage-purchase-dialog-cc-code-for-' + gateway ).val(),
						month : $( '#it-exchnage-purchase-dialog-cc-expiration-month-for-' + gateway ).val(),
						year  : $( '#it-exchnage-purchase-dialog-cc-expiration-year-for-' + gateway ).val(),
					};

					tokenize( 'card', data ).done( function ( token ) {

						$( '.it-exchange-visual-cc-wrap', $form ).hide();
						$( ".it-exchange-visual-cc input[type!='hidden']", $form ).each( function () {
							$( this ).val( '' );
						} );

						var fieldName = itExchange.common.config.currentUser > 0 && canMakeTokens ? 'to_tokenize' : 'one_time_token';
						console.log(fieldName);
						$form.append( $( '<input type="hidden">' ).val( token ).attr( 'name', fieldName ) );

						deferred.resolve();
					} ).fail( function ( error ) {
						$( '.it-exchange-visual-cc-wrap', $form ).prepend(
							'<div class="notice notice-error"><p>' + error + '</p></div>'
						);

						$( 'input[type="submit"]', $form ).removeAttr( 'disabled' );

						deferred.reject();
					} );
				}
			})(
				jQuery,
				'<?php echo $this->get_gateway()->get_slug(); ?>',
				<?php echo it_exchange_in_superwidget() ? 'true' : 'false'; ?>,
				<?php echo $this->get_gateway()->can_handle( 'tokenize' ) ? 'true' : 'false'; ?>,
				<?php echo $tokenizer->get_tokenize_js_function(); ?>
			);
        </script>

		<?php

		return ob_get_clean();
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		$data = array_merge_recursive( parent::get_data_for_REST( $request ), array(
			'accepts' => array( 'card' )
		) );

		$data['method'] = 'dialog';

		return $data;
	}
}
