<?php
/**
 * Contains the coupon line item class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Coupon_Line_Item
 */
class ITE_Coupon_Line_Item implements ITE_Aggregatable_Line_Item, ITE_Taxable_Line_Item, ITE_Aggregate_Line_Item, ITE_Line_Item_Repository_Aware {

	const PERCENT = '%';
	const FLAT = 'flat';

	const PRODUCT = 'per-product';
	const CART = 'cart';

	/** @var IT_Exchange_Coupon */
	private $coupon;

	/** @var string */
	private $id;

	/** @var float */
	private $amount;

	/** @var string */
	private $type;

	/** @var string */
	private $method;

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var ITE_Aggregate_Line_Item|ITE_Cart_Product */
	private $aggregate;

	/** @var ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/**
	 * ITE_Coupon_Line_Item constructor.
	 *
	 * @param \IT_Exchange_Coupon $coupon
	 * @param \ITE_Cart_Product   $product
	 * @param float               $amount Amount to discount.
	 * @param string              $type   Either '%' or 'flat'.
	 * @param string              $method Either 'per-product' or 'cart'.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( IT_Exchange_Coupon $coupon, ITE_Cart_Product $product = null, $amount, $type, $method ) {

		if ( ! $coupon->get_type() ) {
			throw new InvalidArgumentException(
				sprintf( 'Coupon of class %s needs to provide a valid get_type().', get_class( $coupon ) )
			);
		}

		if ( $product ) {
			$this->id = md5( $coupon->get_code() . '-' . $product->get_id() );
		} else {
			$this->id = md5( $coupon->get_code() );
		}

		$this->coupon = $coupon;
		$this->amount = $amount;
		$this->type   = $type;
		$this->method = $method;

		if ( $product ) {
			$this->set_aggregate( $product );
		}
	}

	/**
	 * Get the coupon.
	 *
	 * @since 1.36.0
	 *
	 * @return \IT_Exchange_Coupon
	 */
	public function get_coupon() { return $this->coupon; }

	/**
	 * Calculate the number of items this coupon applies to.
	 *
	 * @since 1.36.0
	 *
	 * @return int
	 */
	protected function calculate_num_items() {

		if ( $this->method === 'per-product' || $this->type === self::PERCENT ) {
			return 1;
		}

		$i = 0;

		foreach ( it_exchange_get_current_cart()->get_items( 'product' ) as $product ) {
			if ( $this->get_coupon()->valid_for_product( $product ) ) {
				$i += $product->get_quantity();
			}
		}

		return $i;
	}

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->aggregate = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->aggregate; }

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
		return $this;
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

		$this->aggregatables[] = $item;

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
	public function is_tax_exempt() {
		return $this->get_aggregate() ? $this->get_aggregate()->is_tax_exempt() : false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code() {
		return $this->get_aggregate() ? $this->get_aggregate()->get_tax_code() : 0;
	}

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
		return $this->get_line_items()->with_only_instances_of( 'ITE_Tax_Line_Item' )->to_array();
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->add_item( $tax );

		foreach ( $this->get_line_items()->with_only_instances_of( 'ITE_Taxable_Line_Item' ) as $item ) {
			if ( $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_tax( $id ) {
		return $this->remove_item( 'tax', $id );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_all_taxes() {
		foreach ( $this->get_taxes() as $tax ) {
			$this->remove_tax( $tax->get_id() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_id() { return $this->id; }

	/**
	 * @inheritDoc
	 */
	public function get_name() { return __( 'Savings', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_description() { return $this->get_coupon()->get_code(); }

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
			if ( ! $aggregatable->is_summary_only() ) {
				$base += $aggregatable->get_amount() * $aggregatable->get_quantity();
			}
		}

		return $base;
	}

	/**
	 * Get the base amount to be charged.
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	protected function get_base_amount() {

		$amount = $this->amount / $this->calculate_num_items();

		if ( $this->type === self::FLAT ) {
			return - $amount;
		} elseif ( $this->type === self::PERCENT ) {
			$product = $this->get_aggregate();

			return - ( ( $amount / 100 ) * ( $product->get_amount_to_discount() * $product->get_quantity() ) );
		} else {
			return 0.00;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Coupon', 'it-l10n-ithemes-exchange' ) : 'coupon';
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
	public function persist_deferred_params() { return $this->bag->persist_deferred_params(); }

	/**
	 * @inheritDoc
	 */
	public function get_data_to_save( \ITE_Line_Item_Repository $repository = null ) {
		return array(
			'code'        => $this->get_coupon()->get_code(),
			'coupon_type' => $this->get_coupon()->get_type(),
			'amount'      => $this->amount,
			'type'        => $this->type,
			'method'      => $this->method,
			'params'      => $this->get_params(),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_data( $id, array $data, ITE_Line_Item_Repository $repository ) {

		$coupon = it_exchange_get_coupon_from_code( $data['code'], $data['coupon_type'] );

		$self     = new self( $coupon, null, $data['amount'], $data['type'], $data['method'] );
		$self->id = $id;

		if ( ! empty( $data['params'] ) ) {
			$self->bag = new ITE_Array_Parameter_Bag( $data['params'] );
		}

		return $self;
	}
}