<?php
/**
 * This file processes AJAX call from the super widget
 * @package IT_Exchange
 * @since   0.4.0
 */
// Die if called directly
if ( ! function_exists( 'add_action' ) ) {
	turtles_all_the_way_down();
	die();
}

// Suppress PHP errors that hose ajax responses. If you turn this off, make sure you're error-free
if ( apply_filters( 'it_exchange_supress_superwidget_ajax_errors', true ) ) {
	ini_set( 'display_errors', false );
}

// Mark as in the superwidget
$GLOBALS['it_exchange']['in_superwidget'] = true;

// Provide an action for add-ons
do_action( 'it_exchange_super_widget_ajax_top' );

// Set vars
$action          = empty( $_GET['sw-action'] ) ? false : esc_attr( $_GET['sw-action'] );
$state           = empty( $_GET['state'] ) ? false : esc_attr( $_GET['state'] );
$product         = empty( $_GET['sw-product'] ) ? false : absint( $_GET['sw-product'] );
$quantity        = empty( $_GET['sw-quantity'] ) ? 1 : absint( $_GET['sw-quantity'] );
$focus           = empty( $_GET['ite-sw-cart-focus'] ) ? false : esc_attr( $_GET['ite-sw-cart-focus'] );
$coupon_type     = empty( $_GET['sw-coupon-type'] ) ? false : esc_attr( $_GET['sw-coupon-type'] );
$coupon          = empty( $_GET['sw-coupon-code'] ) ? false : esc_attr( $_GET['sw-coupon-code'] );
$cart_product    = empty( $_GET['sw-cart-product'] ) ? false : esc_attr( $_GET['sw-cart-product'] );
$shipping_method = empty( $_GET['sw-shipping-method'] ) ? '0' : esc_attr( $_GET['sw-shipping-method'] );
$get_state       = empty( $_GET['get-state'] ) ? false : esc_attr( $_GET['get-state'] );
$ajax_args       = compact( 'action', 'state', 'product', 'quantity', 'focus', 'coupon_type', 'coupon', 'cart_product', 'shipping_method', 'get_state' );

/**
 * Class IT_Exchange_Super_Widget_Ajax
 */
class IT_Exchange_Super_Widget_Ajax {

	/** @var IT_Exchange_Shopping_Cart */
	private $shopping_cart;

	/** @var ITE_Cart */
	private $cart;

	private $action;
	private $state;
	private $get_state;
	private $product;
	private $quantity;
	private $focus;
	private $coupon_type;
	private $coupon;
	private $cart_product;
	private $shipping_method;

	/** @var array */
	private $ajax_args;

	/**
	 * IT_Exchange_Super_Widget_Ajax constructor.
	 *
	 * @param \IT_Exchange_Shopping_Cart $shopping_cart
	 * @param \ITE_Cart                  $cart
	 * @param array                      $ajax_args
	 */
	public function __construct( IT_Exchange_Shopping_Cart $shopping_cart, ITE_Cart $cart, array $ajax_args ) {
		$this->shopping_cart = $shopping_cart;
		$this->cart          = $cart;

		foreach ( $ajax_args as $key => $val ) {
			$this->$key = $val;
		}

		$this->ajax_args = $ajax_args;
	}

	/**
	 * Handle the AJAX Request.
	 *
	 * @since 2.0.0
	 */
	public function handle() {

		$success = $this->do_action( $this->action );
		$this->cart->get_feedback()->to_messages();

		it_exchange_commit_session();

		if ( $this->get_state ) {
		    $this->state = $this->get_state;
		    $success     = $this->do_action( 'get-state' );
        }

		if ( $success === null ) {
			die();
		}

		die( $success );
	}

	/**
	 * Do an action.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action
	 *
	 * @return bool|null Return null to silently die. Return bool to die with a "status code".
	 */
	private function do_action( $action ) {

		switch ( $action ) {

			case 'get-state':

				if ( ! $this->state ) {
					return false;
				}

				if ( $this->product ) {
					it_exchange_set_product( $this->product );
				}

				$state = $this->state;

				if ( $state === 'cart' && ! it_exchange_is_current_product_in_cart() ) {
				    $state = 'product';
                }

				// Allow 3rd party add-ons to filter
				$state = apply_filters( 'it_exchange_get_sw_state_via_ajax_call', $state, $this->ajax_args );

				// If requesting checkout, make sure that all requirements are met first
				if ( 'checkout' === $state ) {
					$state = it_exchange_get_next_purchase_requirement_property( 'sw-template-part' );

					if ( $state === 'checkout' ) {
						it_exchange_get_current_cart()->prepare_for_purchase();
					}

					it_exchange_get_template_part( 'super-widget', $state );
				} else {
					it_exchange_get_template_part( 'super-widget', $state );
				}

				return null;

			case 'add-to-cart':
			case 'buy-now':

				if ( $this->product && $this->quantity ) {
					return it_exchange_add_product_to_shopping_cart( $this->product, $this->quantity );
				}

				return false;

			case 'empty-cart':
				it_exchange_empty_shopping_cart();

				return true;

			case 'remove-from-cart':

				if ( $this->cart_product ) {
					return $this->cart->remove_item( 'product', $this->cart_product );
				}

				return false;

			case 'apply-coupon':

				if ( $this->coupon && $this->coupon_type ) {
					return it_exchange_apply_coupon( $this->coupon_type, $this->coupon );
				}

				if ( 'rblhkh' === strtolower( $this->coupon ) ) {
					die( 'levelup' );
				}

				return false;

			case 'remove-coupon':

				if ( $this->coupon && $this->coupon_type ) {
					return it_exchange_remove_coupon( $this->coupon_type, $this->coupon );
				}

				return false;

			case 'update-quantity':

				if ( $this->quantity && $this->cart_product ) {
					return it_exchange_update_cart_product_quantity( $this->cart_product, $this->quantity, false );
				}

				return false;

			case 'login':

				$creds['user_login']    = empty( $_POST['log'] ) ? '' : urldecode( $_POST['log'] );
				$creds['user_password'] = empty( $_POST['pwd'] ) ? '' : urldecode( $_POST['pwd'] );
				$creds['remember']      = empty( $_POST['rememberme'] ) ? '' : urldecode( $_POST['rememberme'] );

				/**
				 * Pre-login SW errors.
				 *
				 * @since 1.34
				 *
				 * @param WP_Error $pre_login_errors
				 */
				$pre_login_errors = apply_filters( 'it_exchange_pre_sw_login_errors', null );

				if ( is_wp_error( $pre_login_errors ) ) {
					it_exchange_add_message( 'error', $pre_login_errors->get_error_message() );

					return false;
				}

				$user = wp_signon( $creds, false );

				if ( ! is_wp_error( $user ) ) {
					it_exchange_add_message( 'notice', sprintf(
						__( 'Logged in as %s', 'it-l10n-ithemes-exchange' ), $user->user_login
					) );

					return true;
				} else {
					$error_message = $user->get_error_message();
					$error_message = empty( $error_message ) ? __( 'Error. Please try again.', 'it-l10n-ithemes-exchange' ) : $error_message;
					it_exchange_add_message( 'error', $error_message );

					return false;
				}

			case 'register':
				$user_id = it_exchange_register_user();

				if ( ! is_wp_error( $user_id ) ) {

					$creds = array(
						'user_login'    => urldecode( $_POST['user_login'] ),
						'user_password' => urldecode( $_POST['pass1'] ),
					);

					$user = wp_signon( $creds );

					if ( ! is_wp_error( $user ) ) {
						it_exchange_add_message( 'notice', sprintf(
							__( 'Registered and logged in as %s', 'it-l10n-ithemes-exchange' ), $user->user_login
						) );
					} else {
						it_exchange_add_message( 'error', $user->get_error_message() );
					}

					// Clear form values we saved in case of error
					it_exchange_clear_session_data( 'sw-registration' );

					return true;
				} else {
					it_exchange_add_message( 'error', $user_id->get_error_message() );

					// clear out the passwords before we save the data to the session
					unset( $_POST['pass1'], $_POST['pass2'] );

					if ( $user_id->get_error_message( 'user_login' ) ) {
						unset( $_POST['user_login'] );
					}

					if ( $user_id->get_error_message( 'invalid_email' ) || $user_id->get_error_message( 'email_exists' ) ) {
						unset( $_POST['email'] );
					}

					it_exchange_update_session_data( 'sw-registration', $_POST );

					return false;
				}

			case 'update-shipping':

				// This function will either updated the value or create an error and return 1 or 0
				$shipping_result = $this->shopping_cart->handle_update_shipping_address_request();

				if ( ! $shipping_result ) {
					it_exchange_update_session_data( 'sw-shipping', $_POST );
				} else {
					it_exchange_clear_session_data( 'sw-shipping' );
				}

				return $shipping_result;

			case 'update-billing':

				// This function will either updated the value or create an error and return 1 or 0
				$billing_result = $this->shopping_cart->handle_update_billing_address_request();

				if ( ! $billing_result ) {
					it_exchange_update_session_data( 'sw-billing', $_POST );
				} else {
					it_exchange_clear_session_data( 'sw-billing' );
				}

				return $billing_result;

			case 'submit-purchase-dialog':

				$transaction_id = $this->shopping_cart->handle_purchase_cart_request( false );

				if ( $transaction_id ) {
					return it_exchange_get_transaction_confirmation_url( $transaction_id, ! is_user_logged_in() );
                }

                return false;

			case 'update-shipping-method':
				return $this->shipping_method && $this->cart->set_shipping_method( $this->shipping_method );
		}

		// If we made it this far, allow addons to hook in and do their thing.
		return apply_filters( 'it_exchange_processing_super_widget_ajax_' . $action, false );
	}
}

$sw_ajax = new IT_Exchange_Super_Widget_Ajax( $GLOBALS['IT_Exchange_Shopping_Cart'], it_exchange_get_current_cart(), $ajax_args );
$sw_ajax->handle();

// Default
die( '0' );

/**
 * Just for fun
 *
 * @since 0.4.0
 */
function turtles_all_the_way_down() {
	?>
	<pre>
         .-""""-.\
         |"   (a \
         \--'    |
          ;,___.;.
       _ / `"""`\#'.
      | `\"==    \##\
      \   )     /`;##;
       ;-'   .-'  |##|
       |"== (  _.'|##|
       |     ``   /##/
        \"==     .##'
         ',__.--;#;`
         /  /   |\(
         \  \   (
         /  /    \
        (__(____.'
<br />
George says "You can't do that!"
</pre>
	<?php
}
