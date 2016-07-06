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
class ITE_Base_Shipping_Line_Item implements ITE_Shipping_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware {

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var ITE_Aggregate_Line_Item */
	private $aggregate;

	/** @var array ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Tax_Line_Item[] */
	private $taxes = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/** @var string|int */
	private $id;

	/** @var ITE_Parameter_Bag */
	private $frozen;

	/**
	 * ITE_Base_Shipping_Line_Item constructor.
	 *
	 * @param string             $id
	 * @param \ITE_Parameter_Bag $bag
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen ) {
		$this->id     = $id;
		$this->bag    = $bag;
		$this->frozen = $frozen;
	}

	/**
	 * Create a new base shipping line item.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Shipping_Method   $method
	 * @param \IT_Exchange_Shipping_Provider $provider
	 * @param bool                           $cart_wide
	 *
	 * @return \ITE_Base_Shipping_Line_Item
	 */
	public static function create(
		IT_Exchange_Shipping_Method $method,
		IT_Exchange_Shipping_Provider $provider,
		$cart_wide = false
	) {
		$bag = new ITE_Array_Parameter_Bag();
		$bag->set_param( 'method', $method->slug );
		$bag->set_param( 'provider', $provider->get_slug() );
		$bag->set_param( 'cart_wide', $cart_wide );

		$id = md5( $method->slug . '-' . (string) $cart_wide . '-' . microtime() );

		return new self( $id, $bag, new ITE_Array_Parameter_Bag() );
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
	public function get_id() { return $this->id; }

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return $this->frozen->has_param( 'name' ) ? $this->frozen->get_param( 'name' ) : $this->get_method()->label;
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return $this->frozen->has_param( 'description' ) ? $this->frozen->get_param( 'description' ) : '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_amount() {

		$base = $this->get_base_amount();

		foreach ( $this->aggregatables as $aggregatable ) {
			$base += $aggregatable->get_amount() * $aggregatable->get_quantity();
		}

		return $base;
	}

	/**
	 * @inheritDoc
	 */
	public function get_total() {
		return $this->get_amount() * $this->get_quantity();
	}

	/**
	 * Get the base amount.
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	protected function get_base_amount() {

		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		if ( $this->aggregate ) {
			return $this->get_method()->get_shipping_cost_for_product( $this->aggregate->bc() );
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
		return $this->frozen->has_param( 'summary_only' ) ? $this->frozen->get_param( 'summary_only' ) : true;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( ITE_Line_Item_Repository $repository ) { return $repository->save( $this ); }

	/**
	 * @inheritDoc
	 */
	public function get_provider() {
		return it_exchange_get_registered_shipping_provider( $this->get_param( 'provider' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_method() {
		return it_exchange_get_registered_shipping_method( $this->get_param( 'method' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function is_cart_wide() { return $this->get_param( 'cart_wide' ); }

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt() {
		$settings = it_exchange_get_option( 'shipping-general' );

		return empty( $settings['taxable'] );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code() { return $this->get_aggregate() ? $this->get_aggregate()->get_tax_code() : ''; }

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() {
		return $this->get_base_amount();
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxes() {
		return $this->taxes;
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->taxes[] = $tax;
		$this->add_item( $tax );

		foreach ( $this->get_line_items() as $item ) {
			if ( $item instanceof ITE_Taxable_Line_Item && $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_tax( $id ) {

		$found = false;

		foreach ( $this->taxes as $i => $tax ) {
			if ( $tax->get_id() === $id ) {
				unset( $this->taxes[ $i ] );
				$found = true;

				break;
			}
		}

		if ( $found ) {
			reset( $this->taxes );
			$this->remove_item( 'tax', $id );
		}

		return $found;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_all_taxes() {

		$taxes = $this->get_taxes();

		foreach ( $taxes as $tax ) {
			$this->remove_tax( $tax->get_id() );
		}
	}

	/**
	 * @inheritDoc
	 * @throws \UnexpectedValueException
	 */
	public function get_line_items() {

		if ( ! $this->repository ) {
			throw new UnexpectedValueException( sprintf(
				'Repository service not available. See %s.', __CLASS__ . '::set_line_item_repository'
			) );
		}

		return new ITE_Line_Item_Collection( $this->aggregatables, $this->repository );
	}

	/**
	 * @inheritDoc
	 */
	public function add_item( ITE_Aggregatable_Line_Item $item ) {

		$item->set_aggregate( $this );

		if ( $item instanceof ITE_Tax_Line_Item && ! in_array( $item, $this->get_taxes(), true ) ) {
			$this->add_tax( $item );
		} else {
			$this->aggregatables[] = $item;
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_item( $type, $id ) {

		$found = false;

		foreach ( $this->aggregatables as $i => $item ) {
			if ( $item->get_type() === $type && $item->get_id() === $id ) {
				unset( $this->aggregatables[ $i ] );
				$found = true;

				break;
			}
		}

		reset( $this->aggregatables );

		return $found;
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
	public function remove_param( $param, $deferred = false ) { return $this->bag->remove_param( $param, $deferred ); }

	/**
	 * @inheritDoc
	 */
	public function persist_deferred_params() { $this->bag->persist_deferred_params(); }

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}
}