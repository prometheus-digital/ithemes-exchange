<?php
/**
 * Base Shipping class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Base_Shipping_Line_Item
 */
class ITE_Base_Shipping_Line_Item implements ITE_Shipping_Line_Item {

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var IT_Exchange_Shipping_Method */
	private $method;

	/** @var IT_Exchange_Shipping_Provider */
	private $provider;

	/** @var bool */
	private $cart_wide = false;

	/** @var ITE_Cart_Product */
	private $aggregate;

	/** @var string|int */
	private $id;

	/**
	 * ITE_Base_Shipping_Line_Item constructor.
	 *
	 * @param \IT_Exchange_Shipping_Method   $method
	 * @param \IT_Exchange_Shipping_Provider $provider
	 * @param bool                           $cart_wide
	 */
	public function __construct(
		IT_Exchange_Shipping_Method $method,
		IT_Exchange_Shipping_Provider $provider,
		$cart_wide = false
	) {
		$this->method    = $method;
		$this->provider  = $provider;
		$this->cart_wide = $cart_wide;
		$this->id        = md5( $this->get_method()->slug . '-' . (string) $cart_wide . '-' . microtime() );
	}

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) {
		$this->aggregate = $aggregate;
	}

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return $this->get_method()->label;
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() {
		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function get_amount() {
		if ( $this->aggregate ) {
			return $this->get_method()->get_shipping_cost_for_product( $this->aggregate->get_data_to_save() );
		} else {
			return $this->get_method()->get_additional_cost_for_cart( it_exchange_get_current_cart() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Shipping', 'it-l10n-ithemes-exchange' ) : 'shipping';
	}

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( ITE_Line_Item_Repository $repository ) {
		return $repository->save( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * @inheritDoc
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * @inheritDoc
	 */
	public function is_cart_wide() {
		return $this->cart_wide;
	}

	/**
	 * @inheritDoc
	 */
	public function get_params() { return $this->bag->get_params(); }

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) { return $this->bag->has_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) { return $this->bag->get_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value, $deferred = false ) {
		return $this->bag->set_param( $param, $value, $deferred );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param, $deferred = false ) {	return $this->bag->remove_param( $param, $deferred ); }

	/**
	 * @inheritDoc
	 */
	public function persist_deferred_params() {	$this->bag->persist_deferred_params(); }

	/**
	 * @inheritDoc
	 */
	public function get_data_to_save( \ITE_Line_Item_Repository $repository = null ) {
		return array(
			'method'    => $this->get_method()->slug,
			'provider'  => $this->get_provider()->slug,
			'cart_wide' => $this->is_cart_wide(),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_data( $id, array $data, ITE_Line_Item_Repository $repository ) {

		$self = new self(
			it_exchange_get_registered_shipping_method( $data['method'] ),
			it_exchange_get_registered_shipping_provider( $data['provider'] ),
			$data['cart_wide']
		);

		$self->id = $id;

		return $self;
	}
}