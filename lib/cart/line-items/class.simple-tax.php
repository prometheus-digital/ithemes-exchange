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

	/** @var float */
	private $rate;

	/** @var array */
	private $codes = array();

	/** @var ITE_Taxable_Line_Item */
	private $taxable;

	/** @var string */
	private $id;

	/**
	 * ITE_Simple_Tax_Line_Item constructor.
	 *
	 * @param float                       $rate Tax rate as a percentage.
	 * @param array                       $codes
	 * @param \ITE_Taxable_Line_Item|null $item
	 *
	 * @throws \InvalidArgumentException If the rate is invalid.
	 */
	public function __construct( $rate, array $codes = array(), ITE_Taxable_Line_Item $item = null ) {

		if ( ! is_numeric( $rate ) || $rate < 0 || $rate > 100 ) {
			throw new InvalidArgumentException( "Invalid rate '$rate'." );
		}

		$this->rate    = (float) $rate;
		$this->codes   = $codes;
		$this->bag     = new ITE_Array_Parameter_Bag();
		$this->taxable = $item;

		if ( $item ) {
			$this->id = md5( json_encode( $codes ) . '-' . $rate . '-' . $item->get_id() );
		} else {
			$this->id = md5( json_encode( $codes ) . '-' . $rate );
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get_rate() {
		return $this->rate;
	}

	/**
	 * @inheritdoc
	 */
	public function applies_to( ITE_Taxable_Line_Item $item ) {

		if ( $item->is_tax_exempt() ) {
			return false;
		}

		foreach ( $item->get_taxes() as $tax ) {
			if ( $tax instanceof ITE_Simple_Tax_Line_Item ) {
				return false; // Duplicate Simple Taxes are not allowed.
			}
		}

		if ( ! empty( $this->codes ) && ! in_array( $item->get_tax_code(), $this->codes ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item ) {
		return new self( $this->get_rate(), $this->codes, $item );
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
		// TODO: Implement get_description() method.
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
		if ( $this->taxable ) {
			return $this->taxable->get_taxable_amount() * ( $this->get_rate() / 100 );
		} else {
			return 0;
		}
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
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( ITE_Line_Item_Repository $repository ) {
		$repository->save( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) {
		return $this->bag->has_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) {
		return $this->bag->get_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_params() {
		return $this->bag->get_params();
	}

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value, $deferred = false ) {
		return $this->bag->set_param( $param, $value, $deferred );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param, $deferred = false ) {
		return $this->bag->remove_param( $param, $deferred );
	}

	/**
	 * @inheritDoc
	 */
	public function persist_deferred_params() {
		$this->bag->persist_deferred_params();
	}

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->taxable = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->taxable; }

	/**
	 * @inheritDoc
	 */
	public function get_data_to_save( \ITE_Line_Item_Repository $repository = null ) {
		$data = array(
			'params' => $this->get_params(),
			'rate'   => $this->get_rate(),
			'codes'  => $this->codes,
		);

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_data( $id, array $data, ITE_Line_Item_Repository $repository ) {

		$item     = new self( $data['rate'], $data['codes'] );
		$item->id = $id;

		foreach ( $data['params'] as $key => $value ) {
			$item->bag->set_param( $key, $value );
		}

		return $item;
	}
}