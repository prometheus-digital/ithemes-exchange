<?php
/**
 * This file holds the IT_Exchange_Purchase_Dialog class
 *
 * @package IT_Exchange
 * @since 1.3.0
*/
use iThemes\Exchange\REST\Route\v1\Customer\Token\Serializer;
use iThemes\Exchange\REST\Route\v1\Customer\Token\Token;
use iThemes\Exchange\REST\Route\v1\Customer\Token\Tokens;

/**
 * Transaction methods call or extend this class to create a
 * purchase dialog with a custom form in it.
 *
 * @since 1.3.0
*/
class IT_Exchange_Purchase_Dialog{

	/**
	 * @var string $addon_slug the slug for the addon invoking the class
	 * @since 1.3.0
	*/
	public $addon_slug = false;

	/**
	 * @param array a key => value array of form attributes like class, id, etc.
	 * @since 1.3.0
	*/
	public $form_attributes = array();

	/**
	 * @var array an array of the cc fields were using
	 * @since 1.3.0
	*/
	public $active_cc_fields = array();

	/**
	 * @var array an array of the required cc fields were using
	 * @since 1.3.0
	*/
	public $required_cc_fields = array();

	/**
	 * @var string the label used for the button that opens up the CC fields
	 * @since 1.3.0
	*/
	public $purchase_label;

	/**
	 * @var string the label used for the button that submits the CC fields
	 * @since 1.3.0
	*/
	public $submit_label;

	/**
	 * @var string the label used for the cancel link to close the CC fields
	 * @since 1.3.0
	*/
	public $cancel_label;

	/** @var bool */
	private $show_saved = false;

	/**
	 * Class Constructor
	 *
	 * @since 1.3.0
	 *
	 * @param string $transaction_method_slug
	 * @param array  $options
	*/
	public function __construct( $transaction_method_slug, $options=array() ) {

		$defaults = array(
			'form-attributes'    => array(
				'action'       => it_exchange_get_page_url( 'transaction' ),
				'method'       => 'post',
				'autocomplete' => 'on',
				'nonce-action' => $transaction_method_slug . '-checkout',
				'nonce-field'  => 'ite-' . $transaction_method_slug . '-purchase-dialog-nonce',
			),
			'required-cc-fields' => array(
				'first-name',
				'last-name',
				'number',
				'expiration-month',
				'expiration-year',
			),
			'purchase-label'   => __( 'Purchase', 'it-l10n-ithemes-exchange' ),
			'submit-label'     => __( 'Complete Purchase', 'it-l10n-ithemes-exchange' ),
			'cancel-label'     => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'show-saved-cards' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$sw = it_exchange_in_superwidget() ? ' it-exchange-sw-purchase-dialog' : '';
		// Append class name
		$class_name = 'it-exchange-purchase-dialog it-exchange-purchase-dialog-' . $transaction_method_slug . $sw;
		$options['form-attributes']['class'] = empty( $options['form-attributes']['class'] ) ? $class_name : $options['form-attributes']['class'] . ' ' . $class_name;

		$this->addon_slug         = $transaction_method_slug;
		$this->form_attributes    = (array) $options['form-attributes'];
		$this->required_cc_fields = (array) $options['required-cc-fields'];
		$this->purchase_label     = $options['purchase-label'];
		$this->submit_label       = $options['submit-label'];
		$this->cancel_label       = $options['cancel-label'];
		$this->show_saved         = (bool) $options['show-saved-cards'];
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @param string $transaction_method_slug
	 * @param array  $options
	 *
	 * @deprecated
	 */
	function IT_Exchange_Purchase_Dialog( $transaction_method_slug, $options = array() ) {

		self::__construct( $transaction_method_slug, $options );

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the HTML for the button
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function insert_dialog() {
		//$this->enqueue_js(); // We are now doing this in lib/functions/functions.php
		$wrapper_open  = $this->get_wrapper_open();
		$form          = $this->get_purchase_form();
		$wrapper_close = $this->get_wrapper_close();
		$button        = $this->get_purchase_button();

		return $wrapper_open . $form . $wrapper_close . $button;
	}

	/**
	 * Generates the opening HTML for the wrapper div
	 *
	 * @since 1.3.0
	 *
	 * @return string
	*/
	public function get_wrapper_open() {
		$ssl_class = is_ssl() ? ' it-exchange-is-ssl' : ' it-exchange-no-ssl';
		$html = '<div class="it-exchange-purchase-dialog it-exchange-purchase-dialog-' . esc_attr( $this->addon_slug ) . $ssl_class . '" data-addon-slug="' . esc_attr( $this->addon_slug ) . '">';

		return $html;
	}

	/**
	 * Generates the closing HTML for the wrapper div
	 *
	 * @since 1.3.0
	 *
	 * @return string
	*/
	public function get_wrapper_close() {
		return '</div>';
	}

	/**
	 * Generates the purchase form
	 *
	 * @since 1.3.0
	 *
	 * @return string
	*/
	public function get_purchase_form() {

		$this->enqueue_js();

		$GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'] = $this->addon_slug;

		$form_open          = $this->get_form_open();
		$form_hidden_fields = $this->get_form_hidden_fields();
		$saved_cards        = $this->get_saved_cards();
		$form_fields        = $this->get_form_fields();
		$form_actions       = $this->get_form_actions();
		$form_close         = $this->get_form_close();

		$form = $form_open . $form_hidden_fields . $saved_cards . $form_fields . $form_actions . $form_close;

		unset( $GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'] );
		return $form;
	}

	/**
	 * Gets the open form field
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function get_form_open() {
		$form_attributes = '';
		foreach( $this->form_attributes as $key => $value ) {
			$form_attributes .= $key . '="' . esc_attr( $value ) . '" ';
		}

		return '<form ' . $form_attributes . '>';
	}

	/**
	 * Get form hidden fields
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function get_form_hidden_fields() {

		$method = esc_attr( it_exchange_get_field_name('transaction_method') );

		$fields  = '<input type="hidden" name="' . $method . '" value="' . esc_attr( $this->addon_slug ) . '" />';
		$fields .= wp_nonce_field(
			$this->form_attributes['nonce-action'],
			$this->form_attributes['nonce-field'],
			true, false
		);

		return $fields;
	}

	/**
	 * Gets the form body
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function get_form_fields() {
		ob_start();
		it_exchange_get_template_part( 'content', 'purchase-dialog' );

		return ob_get_clean();
	}

	/**
	 * Gets the form actions
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function get_form_actions() {
		$actions  = '<p><input type="submit" value="' . esc_attr( $this->submit_label ) . '" /><br />';
		$actions .= '<a href="#" class="it-exchange-purchase-dialog-cancel" data-addon-slug="' . esc_attr( $this->addon_slug ) . '">' . esc_html( $this->cancel_label ) . '</a></p>';
		return $actions;
	}

	/**
	 * Get form close
	 *
	 * @since 1.3.0
	 * @return string HTML
	*/
	public function get_form_close() {
		return '</form>';
	}

	/**
	 * Generates the init button that calls the dialog
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	public function get_purchase_button() {

		it_exchange_clear_purchase_dialog_error_flag( $this->addon_slug );

		$classes = array(
			"it-exchange-purchase-dialog-trigger-{$this->addon_slug}",
			"it-exchange-purchase-dialog-trigger",
			"it-exchange-purchase-button-{$this->addon_slug}",
			"it-exchange-purchase-button",
		);

		if ( it_exchange_purchase_dialog_has_error( $this->addon_slug ) ) {
			$classes[] = 'has-errors';
		}

		$classes = esc_attr( implode( ' ', $classes ) );

		return '<input type="submit" class="' . $classes . '" value="' . esc_attr( $this->purchase_label ) . '" data-addon-slug="' . esc_attr( $this->addon_slug ) . '" />';
	}

	/**
	 * Get saved cards selector.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_saved_cards() {

		if ( ! $this->show_saved ) {
			return '';
		}

		$gateway = ITE_Gateways::get( $this->addon_slug );

		if ( ! $gateway || ! $gateway->can_handle( 'tokenize' ) )  {
			return '';
		}

		$customer = it_exchange_get_current_customer();

		if ( ! $customer ) {
			return '';
		}

		$cards = $customer->get_tokens( array( 'gateway' => $this->addon_slug ) );

		if ( ! $cards || ! $cards->count() ) {
			return '';
		}

		$html = '<div class="it-exchange-credit-card-selector">';

		foreach ( $cards as $card ) {

			$label = $card->get_label();

			$selected = checked( $card->primary, true, false );

			$html .= "<label><input type='radio' name='purchase_token' value='{$card->ID}' {$selected}>&nbsp;{$label}</label>";
		}

		$new_method = __( 'New Payment Method', 'it-l10n-ithemes-exchange' );

		$html .= "<label><input type='radio' name='purchase_token' value='new_method' id='new-method-{$this->addon_slug}'>";
		$html .= ' ' . $new_method . '</label>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Enqueues the JS for purchase dialogs
	 *
	 * @since 1.3.0
	 *
	 * @return void
	*/
	public function enqueue_js() {

		$customer = it_exchange_get_current_customer();

		if ( ! $customer instanceof IT_Exchange_Guest_Customer ) {
			$tokens_endpoint = \iThemes\Exchange\REST\get_rest_url(
				new Tokens( new Serializer(), new ITE_Gateway_Request_Factory() ),
				array( 'customer_id' => $customer->ID )
			);
			$tokens_endpoint = wp_nonce_url( $tokens_endpoint, 'wp_rest' );
		} else {
			$tokens_endpoint = '';
		}

		$file = dirname( dirname( __FILE__ ) ) . '/purchase-dialog/js/exchange-purchase-dialog.js';
		wp_enqueue_script(
			'exchange-purchase-dialog', ITUtility::get_url_from_file( $file ),
			array( 'jquery', 'detect-credit-card-type', 'jquery.payment' ), false, true
		);
		wp_localize_script( 'exchange-purchase-dialog', 'itExchangePurchaseDialog', array(
			'tokensEndpoint' => $tokens_endpoint
		) );
	}

	/**
	 * Grabs the credit card fields from $_POST
	 *
	 * @since 1.3.0
	 *
	 * @return array
	*/
	public function get_submitted_form_values() {
		$fields = array(
			'it-exchange-purchase-dialog-cc-first-name'		   => 'first-name',
			'it-exchange-purchase-dialog-cc-last-name'		   => 'last-name',
			'it-exchange-purchase-dialog-cc-number'            => 'number',
			'it-exchange-purchase-dialog-cc-expiration-month'  => 'expiration-month',
			'it-exchange-purchase-dialog-cc-expiration-year'   => 'expiration-year',
			'it-exchange-purchase-dialog-cc-code'              => 'code',
		);

		$cc_data = array();
		foreach( $fields as $key => $value ) {
			$cc_data[$value] = empty( $_POST[$key] ) ? false : $_POST[$key];
		}

		$cc_data['number'] = str_replace( ' ', '', $cc_data['number'] );

		// Filter available in ithemes-exchange/api/purchase-dialog.php
		return $cc_data;
	}

	/**
	 * Get a Gateway Card from the submitted form values.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	public function get_card_from_submitted_values() {
		$cc = $this->get_submitted_form_values();

		if ( empty( $cc['number'] ) || empty( $cc['expiration-month'] ) || empty( $cc['expiration-year'] ) ) {
			return null;
		}

		return new ITE_Gateway_Card(
			$cc['number'],
			$cc['expiration-year'],
			$cc['expiration-month'],
			$cc['code'],
			trim( $cc['first-name'] . ' ' . $cc['last-name'] )
		);
	}

	/**
	 * Validates the credit card fields were populated
	 *
	 * It is up to the transaction method add-on to validate if its a good/acceptible CC
	 * This only confirms that the fields exists. Use the filter at the bottom of the function
	 * to modify validation of CC data.
	 *
	 * @since 1.3.0
	 *
	 * @todo this method could use some TLC
	 *
	 * @param bool $add_message Whether to add the error as a message. Defaults to true.
	 *
	 * @return bool|string Error message if $add_message is false.
	*/
	public function is_submitted_form_valid( $add_message = true ) {
		// Grab the values
		$values = $this->get_submitted_form_values();

		// Validate nonce
		$nonce = empty( $_POST[ $this->form_attributes['nonce-field'] ] ) ? false : $_POST[ $this->form_attributes['nonce-field'] ];

		if ( ! wp_verify_nonce( $nonce, $this->form_attributes['nonce-action'] ) ) {

			$message = __( 'Transaction Failed, unable to verify security token.', 'it-l10n-ithemes-exchange' );

			if ( $add_message ) {
				it_exchange_add_message( 'error', $message );
			}

			it_exchange_flag_purchase_dialog_error( $this->addon_slug );

			return $add_message ? false : $message;
		}

		foreach ( (array) $values as $key => $value ) {
			$invalid  = false;
			$required = in_array( $key, $this->required_cc_fields );

			// Make sure its not empty if its required
			if ( $required && empty( $value ) )
				$invalid = __( 'Please make sure all required fields have a value.', 'it-l10n-ithemes-exchange' );

			// Make sure card number, expiration, and code have a number
			if ( ! empty( $value ) && ! in_array( $key, array( 'first-name', 'last-name' ) ) && ! is_numeric( $value ) )
				$invalid = __( 'Please make sure all fields are formatted properly.', 'it-l10n-ithemes-exchange' );

			// Filter makes it possible for add-on to make something valid that would be invalid otherwise.
			$invalid = apply_filters( 'it_exchange_validate_' . $key . '_credit_cart_field', $invalid, $value, $this->addon_slug );

			if ( $invalid ) {

				if ( $add_message ) {
					it_exchange_add_message( 'error', $invalid );
				}

				it_exchange_flag_purchase_dialog_error( $this->addon_slug );

				return $add_message ? false : $invalid;
			}
		}

		return $add_message ? true : '';
	}
}
