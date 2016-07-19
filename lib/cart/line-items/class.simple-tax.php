<?php
/**
 * Tax Line Item class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Tax_Line_Item
 */
class ITE_Simple_Tax_Line_Item implements ITE_Tax_Line_Item {

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var ITE_Taxable_Line_Item */
	private $taxable;

	/** @var string */
	private $id;

	/** @var ITE_Parameter_Bag */
	private $frozen;

	/**
	 * ITE_Simple_Tax_Line_Item constructor.
	 *
	 * @param string             $id
	 * @param ITE_Parameter_Bag  $bag
	 *
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen ) {
		$this->id     = $id;
		$this->bag    = $bag;
		$this->frozen = $frozen;
	}

	/**
	 * Create a new tax line item.
	 *
	 * @since 1.36.0
	 *
	 * @param float                       $rate
	 * @param array                       $codes
	 * @param \ITE_Taxable_Line_Item|null $item
	 *
	 * @return self
	 */
	public static function create( $rate, array $codes = array(), ITE_Taxable_Line_Item $item = null ) {

		if ( ! is_numeric( $rate ) || $rate < 0 || $rate > 100 ) {
			throw new InvalidArgumentException( "Invalid rate '$rate'." );
		}

		$bag = new ITE_Array_Parameter_Bag();
		$bag->set_param( 'rate', (float) $rate );
		$bag->set_param( 'codes', $codes );

		if ( $item ) {
			$id = md5( json_encode( $codes ) . '-' . $rate . '-' . $item->get_id() );
		} else {
			$id = md5( json_encode( $codes ) . '-' . $rate );
		}

		$self = new self( $id, $bag, new ITE_Array_Parameter_Bag() );

		if ( $item ) {
			$self->set_aggregate( $item );
		}

		return $self;
	}

	/**
	 * @inheritdoc
	 */
	public function get_provider() { return new ITE_Simple_Taxes_Provider(); }

	/**
	 * @inheritdoc
	 */
	public function get_rate() { return $this->get_param( 'rate' ); }

	/**
	 * @inheritdoc
	 */
	public function applies_to( ITE_Taxable_Line_Item $item ) {

		if ( $item->is_tax_exempt( $this->get_provider() ) ) {
			return false;
		}

		foreach ( $item->get_taxes() as $tax ) {
			if ( $tax instanceof ITE_Simple_Tax_Line_Item ) {
				return false; // Duplicate Simple Taxes are not allowed.
			}
		}

		$codes = $this->get_param( 'codes' );

		if ( count( $codes ) !== 0 && ! in_array( $item->get_tax_code( $this->get_provider() ), $codes ) ) {
			return false;
		}

		$settings = it_exchange_get_option( 'addon_taxes_simple', true );

		if ( empty( $settings['calculate-after-discounts'] ) && $item->get_type() === 'coupon' ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item ) {
		return self::create( $this->get_rate(), $this->get_param( 'codes' ), $item );
	}

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
		if ( function_exists( 'it_exchange_add_simple_taxes_get_label' ) ) {
			return it_exchange_add_simple_taxes_get_label( 'taxes' );
		} else {
			return __( 'Taxes', 'it-l10n-ithemes-exchange' );
		}
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

		if ( $this->taxable ) {
			return $this->taxable->get_taxable_amount() * ( $this->get_rate() / 100 );
		} else {
			return 0;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_total() {
		return $this->get_amount() * $this->get_quantity();
	}

	/**
	 * @inheritDoc
	 */
	final public function get_type( $label = false ) {
		return $label ? __( 'Tax', 'it-l10n-ithemes-exchange' ) : 'tax';
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
	public function has_param( $param ) { return $this->bag->has_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) { return $this->bag->get_param( $param ); }

	/**
	 * @inheritDoc
	 */
	public function get_params() { return $this->bag->get_params(); }

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value ) {
		return $this->bag->set_param( $param, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) {
		return $this->bag->remove_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->taxable = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->taxable; }
}