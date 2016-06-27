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
class ITE_Base_Shipping_Line_Item implements ITE_Shipping_Line_Item, ITE_Taxable_Line_Item, ITE_Aggregate_Line_Item, ITE_Line_Item_Repository_Aware {

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var IT_Exchange_Shipping_Method */
	private $method;

	/** @var IT_Exchange_Shipping_Provider */
	private $provider;

	/** @var bool */
	private $cart_wide = false;

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
		$this->bag       = new ITE_Array_Parameter_Bag();
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
	public function get_id() { return $this->id; }

	/**
	 * @inheritDoc
	 */
	public function get_name() { return $this->get_method()->label; }

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() { return 1; }

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
	public function is_summary_only() { return true; }

	/**
	 * @inheritDoc
	 */
	public function persist( ITE_Line_Item_Repository $repository ) { return $repository->save( $this ); }

	/**
	 * @inheritDoc
	 */
	public function get_provider() { return $this->provider; }

	/**
	 * @inheritDoc
	 */
	public function get_method() { return $this->method; }

	/**
	 * @inheritDoc
	 */
	public function is_cart_wide() { return $this->cart_wide; }

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

		$base = it_exchange_get_cart_product_base_price( $this->get_data_to_save(), false );

		foreach ( $this->aggregate as $aggregate ) {
			if ( $aggregate instanceof ITE_Taxable_Line_Item ) {
				$base += $aggregate->get_taxable_amount();
			}
		}

		return $base;
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
			if ( $item instanceof ITE_Taxable_Line_Item ) {
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
		$this->taxes = array();
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
	public function has_primary() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary() {
		$clone                = clone $this;
		$clone->aggregatables = array();

		return $clone;
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

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}
}