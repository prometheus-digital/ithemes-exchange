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
class ITE_Coupon_Line_Item implements ITE_Aggregatable_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware, ITE_Cart_Aware {

	/** @var IT_Exchange_Coupon */
	private $coupon;

	/** @var string */
	private $id;

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var ITE_Aggregate_Line_Item|ITE_Cart_Product */
	private $aggregate;

	/** @var ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/** @var ITE_Cart */
	private $cart;

	/** @var ITE_Parameter_Bag */
	private $frozen;

	/**
	 * ITE_Coupon_Line_Item constructor.
	 *
	 * @param string             $id
	 * @param \ITE_Parameter_Bag $bag
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen ) {
		$this->id     = $id;
		$this->bag    = $bag;
		$this->coupon = it_exchange_get_coupon( $this->get_param( 'id' ), $this->get_param( 'type' ) );
		$this->frozen = $frozen;
	}

	/**
	 * Create a coupon line item.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Coupon    $coupon
	 * @param \ITE_Cart_Product|null $product
	 *
	 * @return \ITE_Coupon_Line_Item
	 * @throws \InvalidArgumentException
	 */
	public static function create( IT_Exchange_Coupon $coupon, ITE_Cart_Product $product = null ) {

		if ( ! $coupon->get_type() ) {
			throw new InvalidArgumentException(
				sprintf( 'Coupon of class %s needs to provide a valid get_type().', get_class( $coupon ) )
			);
		}

		$bag = new ITE_Array_Parameter_Bag( array(
			'id'   => $coupon->get_ID(),
			'type' => $coupon->get_type(),
		) );

		if ( $product ) {
			$id = md5( $coupon->get_code() . '-' . $product->get_id() );
		} else {
			$id = md5( $coupon->get_code() );
		}

		$self = new self( $id, $bag, new ITE_Array_Parameter_Bag() );

		if ( $product ) {
			$self->set_aggregate( $product );
		}

		return $self;
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
	 * Create a duplicate of this coupon, scoped for a given product.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Cart_Product $product
	 *
	 * @return \ITE_Coupon_Line_Item
	 */
	public function create_scoped_for_product( ITE_Cart_Product $product ) {
		$coupon = self::create( $this->get_coupon(), $product );

		if ( $this->repository ) {
			$coupon->set_line_item_repository( $this->repository );
		}

		if ( $this->cart ) {
			$coupon->set_cart( $this->cart );
		}

		return $coupon;
	}

	/**
	 * Calculate the number of items this coupon applies to.
	 *
	 * @since 1.36.0
	 *
	 * @return int
	 */
	protected function calculate_num_items() {

		if ( $this->get_coupon()->get_application_method() === IT_Exchange_Coupon::APPLY_PRODUCT ) {
			return 1;
		}

		if ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_PERCENT ) {
			return 1;
		}

		$cart = $this->cart ? $this->cart : it_exchange_get_current_cart();
		$i    = 0;

		foreach ( $cart->get_items( 'product' ) as $product ) {
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
	 * @inheritdoc
	 */
	public function set_cart( ITE_Cart $cart ) { $this->cart = $cart; }

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

		if ( $item instanceof ITE_Taxable_Line_Item ) {
			foreach ( $this->get_taxes() as $tax ) {
				if ( $tax->applies_to( $item ) ) {
					$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
				}
			}
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
	public function is_tax_exempt( ITE_Tax_Provider $for ) {
		return $this->get_aggregate() ? $this->get_aggregate()->is_tax_exempt( $for ) : false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) {
		return $this->get_aggregate() ? $this->get_aggregate()->get_tax_code( $for ) : 0;
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
	public function get_description() {
		if ( $this->frozen->has_param( 'description' ) ) {
			return $this->frozen->get_param( 'description' );
		} else {
			return $this->get_coupon()->get_code();
		}
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
			if ( ! $aggregatable->is_summary_only() ) {
				$base += $aggregatable->get_amount() * $aggregatable->get_quantity();
			}
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
	 * Get the base amount to be charged.
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	protected function get_base_amount() {

		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		$amount = $this->get_coupon()->get_amount_number() / $this->calculate_num_items();

		if ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_FLAT ) {
			return - $amount;
		} elseif ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_PERCENT ) {
			$product = $this->get_aggregate();

			if ( ! $product ) {
				return 0.00;
			}

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
	public function set_param( $param, $value ) {
		return $this->bag->set_param( $param, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) { return $this->bag->remove_param( $param ); }
}