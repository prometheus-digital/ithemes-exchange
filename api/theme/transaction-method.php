<?php

/**
 * Theme API class for Transaction_Method
 * @package IT_Exchange
 * @since   0.4.0
 */
class IT_Theme_API_Transaction_Method implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	 */
	private $_context = 'transaction-method';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	 */
	public $_tag_map = array(
		'makepayment'             => 'make_payment',
		'interstitialdescription' => 'interstitial_description',
		'interstitialtarget'      => 'interstitial_target',
		'interstitialvars'        => 'interstitial_vars',
		'interstitialvar'         => 'interstitial_var',
		'interstitialvarkey'      => 'interstitial_var_key',
		'interstitialvarvalue'    => 'interstitial_var_value',
	);

	/**
	 * The current transaction method
	 * @var array $_transaction_method
	 * @since 0.4.0
	 */
	private $_transaction_method = false;

	/** @var array */
	private $interstitial = array();

	/** @var array */
	private static $interstitial_vars = array();

	/** @var string */
	private static $interstitial_var = '';

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 */
	function __construct() {
		$this->_transaction_method = empty( $GLOBALS['it_exchange']['transaction_method'] ) ? false : $GLOBALS['it_exchange']['transaction_method'];
		$this->interstitial        = it_exchange_get_global( 'purchase_interstitial' ) ?: array();
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Transaction_Method() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns the payment action data/html
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	function make_payment( $options = array() ) {
		return it_exchange_get_transaction_method_make_payment_button( $this->_transaction_method['slug'], $options );
	}

	/**
	 * Print the interstitial description.
	 *
	 * @since 1.36.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function interstitial_description( array $options = array() ) {

		if ( empty( $this->interstitial['gateway'] ) ) {
			return '';
		}

		$gateway = ITE_Gateways::get( $this->interstitial['gateway'] );

		if ( ! $gateway ) {
			return '';
		}

		return sprintf(
			__( 'You are being redirected to %s to complete your transaction.', 'it-l10n-ithemes-exchange' ),
			$gateway->get_name()
		);
	}

	/**
	 * Print the target of the interstitial.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function interstitial_target() {
		return empty( $this->interstitial['url'] ) ? '' : esc_url( $this->interstitial['url'] );
	}

	/**
	 * Loop over interstitial vars.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function interstitial_vars() {

		if ( empty( $this->interstitial['vars'] ) ) {
			return false;
		}

		if ( ! self::$interstitial_vars ) {
			self::$interstitial_vars = $this->interstitial['vars'];
			reset( self::$interstitial_vars );
			self::$interstitial_var = key( self::$interstitial_vars );

			return true;
		}

		if ( next( self::$interstitial_vars ) ) {
			self::$interstitial_var = key( self::$interstitial_vars );

			return true;
		} else {
			self::$interstitial_vars = array();
			self::$interstitial_var  = '';

			return false;
		}
	}

	/**
	 * Get the interstitial var key.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function interstitial_var_key() {
		return esc_attr( self::$interstitial_var );
	}

	/**
	 * Get the interstitial var value.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function interstitial_var_value() {
		if ( isset( self::$interstitial_vars[ self::$interstitial_var ] ) ) {
			return esc_attr( self::$interstitial_vars[ self::$interstitial_var ] );
		}

		return '';
	}
}
