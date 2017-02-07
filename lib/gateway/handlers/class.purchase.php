<?php
/**
 * Abstract purchase request handler class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Purchase_Request_Handler
 */
abstract class ITE_Purchase_Request_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @var ITE_Gateway
	 */
	protected $gateway;

	/**
	 * @var \ITE_Gateway_Request_Factory
	 */
	protected $factory;

	/**
	 * ITE_Purchase_Request_Handler constructor.
	 *
	 * @param ITE_Gateway                 $gateway
	 * @param ITE_Gateway_Request_Factory $factory
	 */
	public function __construct( ITE_Gateway $gateway, ITE_Gateway_Request_Factory $factory ) {
		$this->gateway = $gateway;
		$this->factory = $factory;

		$self = $this;

		add_filter(
			"it_exchange_get_{$gateway->get_slug()}_make_payment_button",
			function ( $_, $options ) use ( $self, $factory ) {
				try {

					$factory_opts = array();

					if ( isset( $options['cart'] ) ) {
						$factory_opts['cart'] = $options['cart'];
					}

					return $self->render_payment_button( $factory->make( 'purchase', $factory_opts ) );
				} catch ( Exception $e ) {
					return '';
				}
			}, 10, 2
		);

		add_filter(
			"it_exchange_do_transaction_{$gateway->get_slug()}",
			function ( $_, $transaction_object, ITE_Cart $cart = null ) use ( $self, $factory ) {
				if ( ! isset( $transaction_object->cart_id ) ) {
					return $_;
				}

				if ( ! $cart ) {
					return $_;
				}

				try {
					$request = $factory->make(
						'purchase',
						$self->build_factory_args_from_global_state( $cart, $_REQUEST )
					);
					$txn = $self->handle( $request );
				} catch ( Exception $e ) {
					$cart->get_feedback()->add_error( $e->getMessage() );

					return null;
				}

				return $txn ? $txn->ID : false;
			},
			10, 3
		);
	}

	/**
	 * Build factory args from the global state.
	 *
	 * This is used to build the intermediary layer between the Gateway Request framework
	 * and the legacy cart system.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param array     $state
	 *
	 * @return array
	 */
	public function build_factory_args_from_global_state( ITE_Cart $cart, $state ) {

		if ( ! empty( $state['purchase_token'] ) && $state['purchase_token'] !== 'new_method' ) {
			$token = (int) $state['purchase_token'];
		} else {
			$token = '';
		}

		return array(
			'cart'           => $cart,
			'nonce'          => empty( $state['_wpnonce'] ) ? '' : $state['_wpnonce'],
			'http_request'   => $state,
			'token'          => $token,
			'tokenize'       => empty( $state['to_tokenize'] ) ? '' : $state['to_tokenize'],
			'one_time_token' => empty( $state['one_time_token'] ) ? '' : $state['one_time_token'],
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === ITE_Gateway_Purchase_Request::get_name(); }

	/**
	 * Get the gateway for this handler.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Gateway
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Render a payment button.
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	public function render_payment_button( ITE_Gateway_Purchase_Request $request ) {

		$attributes = '';

		foreach ( $this->get_form_element_attributes( $request ) as $attr => $val ) {
			$attributes .= $attr . '="' . esc_attr( $val ) . '" ';
		}

		$label      = esc_attr( $this->get_payment_button_label() );
		$field_name = esc_attr( it_exchange_get_field_name( 'transaction_method' ) );

		return <<<HTML
<form $attributes>
	<input type="submit" class="it-exchange-purchase-button it-exchange-purchase-button-{$this->gateway->get_slug()}" 
	name="{$this->gateway->get_slug()}_purchase" value="{$label}">
	<input type="hidden" name="{$field_name}" value="{$this->gateway->get_slug()}">
	{$this->get_nonce_field()}
	{$this->get_html_before_form_end( $request )}
</form>
HTML;
	}

	/**
	 * Get attributes that should be included on the <form> element triggering this purchase.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	protected function get_form_element_attributes( ITE_Gateway_Purchase_Request $request ) {
		return array(
			'method'       => 'POST',
			'action'       => $this->get_form_action(),
			'id'           => "{$this->get_gateway()->get_slug()}-purchase-form",
			'data-gateway' => $this->get_gateway()->get_slug(),
		);
	}

	/**
	 * Get the label for the payment button.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_payment_button_label() {
		return it_exchange_get_transaction_method_name_from_slug( $this->get_gateway()->get_slug() );
	}

	/**
	 * Get the form action URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_form_action() { return it_exchange_get_page_url( 'transaction' ); }

	/**
	 * Get the action of the nonce.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() { return $this->gateway->get_slug() . '-purchase'; }

	/**
	 * Get a nonce.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_nonce() { return wp_create_nonce( $this->get_nonce_action() ); }

	/**
	 * Output the payment button nonce.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_nonce_field() { return wp_nonce_field( $this->get_nonce_action(), '_wpnonce', false, false ); }

	/**
	 * Get HTML to be rendered before the form is closed.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	protected function get_html_before_form_end( ITE_Gateway_Purchase_Request $request ) {

		$html = '';

		if ( ! $request->get_cart()->is_current() && ( it_exchange_in_superwidget() || it_exchange_is_page( 'checkout' ) ) ) {
			$html .= "<input type='hidden' name='cart_id' value='{$request->get_cart()->get_id()}'>";
			$html .= "<input type='hidden' name='cart_auth' value='{$request->get_cart()->generate_auth_secret( 3600 )}'>";
		}

		if ( $request->get_redirect_to() ) {
			$to = esc_url( $request->get_redirect_to() );
			$html .= "<input type='hidden' name='redirect_to' value='{$to}'>";
		}

		return $html;
	}

	/**
	 * Get the data for REST API Purchase endpoint.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {

		$data = array(
			'method'  => 'REST',
			'accepts' => array(),
		);

		if ( $this->get_gateway()->can_handle( 'tokenize' ) ) {
			$data['accepts'][] = 'token';
			$data['accepts'][] = 'tokenize';

			if ( $this->get_gateway()->get_handler_by_request_name( 'tokenize' ) instanceof ITE_Gateway_JS_Tokenize_Handler ) {
				$data['accepts'][] = 'one_time_token';
			} elseif ( $this instanceof ITE_Gateway_JS_Tokenize_Handler ) {
				$data['accepts'][] = 'one_time_token';
			}

			$primary_token = $request->get_customer()->get_tokens( array(
				'gateway' => $this->get_gateway()->get_slug(),
				'primary' => true,
			) );

			if ( $primary_token->count() ) {
				$token_serializer     = new \iThemes\Exchange\REST\Route\v1\Customer\Token\Serializer();
				$context_filterer     = new \iThemes\Exchange\REST\Helpers\ContextFilterer();
				$data['primaryToken'] = $context_filterer->filter(
					$token_serializer->serialize( $primary_token->first() ),
					'embed',
					$token_serializer->get_schema()
				);
			}
		}

		return $data;
	}

	/**
	 * Can this purchase handler handle a given cart.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Cart $cart
	 *
	 * @return bool
	 */
	public function can_handle_cart( ITE_Cart $cart ) {

		if ( $cart->get_total() <= 0 && ! $cart->contains_non_recurring_fee() ) {
			return false;
		}

		/** @var ITE_Requires_Optionally_Supported_Features[]|ITE_Line_Item[] $items */
		$items = $cart->get_items()->flatten()->filter( function ( ITE_Line_Item $item ) {
			return $item instanceof ITE_Requires_Optionally_Supported_Features && $item->optional_features_required();
		} )->unique();

		$gateway = $this->get_gateway();

		foreach ( $items as $item ) {
			$requirements = $item->optional_features_required();

			foreach ( $requirements as $requirement ) {
				if ( ! $gateway->supports_feature( $requirement->get_feature() ) ) {
					return false;
				}

				foreach ( $requirement->get_requirement_details() as $slug => $detail ) {
					if ( ! $gateway->supports_feature_and_detail( $requirement->get_feature(), $slug, $detail ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}
}
