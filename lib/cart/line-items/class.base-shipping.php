<?php
/**
 * Base Shipping class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Base_Shipping_Line_Item
 */
class ITE_Base_Shipping_Line_Item extends ITE_Line_Item implements
	ITE_Shipping_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware, ITE_Cart_Aware, ITE_Scopable_Line_Item {

	/** @var ITE_Aggregate_Line_Item */
	private $aggregate;

	/** @var ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/** @var ITE_Cart */
	private $cart;

	/** @var ITE_Base_Shipping_Line_Item */
	private $scoped_from;

	/**
	 * Create a new base shipping line item.
	 *
	 * @since 2.0.0
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

		$id = self::generate_id( $method, $cart_wide );

		return new self( $id, $bag, new ITE_Array_Parameter_Bag() );
	}

	/**
	 * Generate the ID.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Shipping_Method $method
	 * @param bool                        $cart_wide
	 *
	 * @return string
	 */
	protected final static function generate_id( $method, $cart_wide ) {
		return md5( $method->slug . '-' . (string) $cart_wide . '-' . microtime() );
	}

	/**
	 * @inheritDoc
	 */
	public function clone_with_new_id( $include_frozen = true ) {
		return new self(
			self::generate_id( $this->get_method(), $this->is_cart_wide() ),
			$this->bag,
			$include_frozen ? $this->frozen : new ITE_Array_Parameter_Bag()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function is_scoped() { return (bool) $this->scoped_from; }

	/**
	 * @inheritDoc
	 */
	public function scoped_from() {
		if ( $this->is_scoped() ) {
			return $this->scoped_from;
		}

		throw new UnexpectedValueException( 'Shipping item is not scoped.' );
	}

	/**
	 * @inheritDoc
	 */
	public function set_scoped_from( ITE_Scopable_Line_Item $scoped_from ) {
		$this->scoped_from = $scoped_from;
	}

	/**
	 * @inheritDoc
	 */
	public function shared_params_in_scope() {
		return array( 'method', 'provider' );
	}

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
		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		if ( ! $this->cart ) {
			$this->cart = it_exchange_get_current_cart();
		}

		if ( $this->is_cart_wide() ) {
			return $this->get_method()->get_additional_cost_for_cart( $this->cart );
		} elseif ( $this->aggregate instanceof ITE_Cart_Product ) {
			return $this->get_method()->get_shipping_cost_for_product( $this->aggregate->bc(), $this->cart );
		} else {
			return 0;
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
	public function get_provider() { return it_exchange_get_registered_shipping_provider( $this->get_provider_slug() ); }

	/**
	 * @inheritDoc
	 */
	public function get_provider_slug() { return $this->get_param( 'provider' ); }

	/**
	 * @inheritDoc
	 */
	public function get_method() { return it_exchange_get_registered_shipping_method( $this->get_method_slug() ); }

	/**
	 * @inheritDoc
	 */
	public function get_method_slug() { return $this->get_param( 'method' ); }

	/**
	 * @inheritDoc
	 */
	public function is_cart_wide() { return $this->get_param( 'cart_wide' ); }

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt( ITE_Tax_Provider $for ) {
		$settings = it_exchange_get_option( 'shipping-general' );

		return empty( $settings['taxable'] );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) {
		return $this->get_aggregate() ? $this->get_aggregate()->get_tax_code( $for ) : '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() {
		return $this->get_amount();
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxes() {
		return $this->get_line_items()->with_only_instances_of( 'ITE_Tax_Line_Item' );
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->add_item( $tax );

		foreach ( $this->get_line_items()->taxable() as $item ) {
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
	public function _set_line_items( array $items ) {
		$this->aggregatables = $items;
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
	public function set_cart( ITE_Cart $cart ) {
		$this->cart = $cart;
	}

	/**
	 * @inheritDoc
	 */
	public function __destruct() {
		unset( $this->aggregate, $this->aggregatables, $this->repository );
	}
}
