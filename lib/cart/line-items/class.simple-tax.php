<?php
/**
 * Tax Line Item class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Tax_Line_Item
 */
class ITE_Simple_Tax_Line_Item extends ITE_Line_Item implements ITE_Tax_Line_Item {

	/** @var ITE_Taxable_Line_Item */
	private $taxable;

	/**
	 * Create a new tax line item.
	 *
	 * @since 2.0.0
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
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item ) {
		return self::create( $this->get_rate(), $this->get_codes(), $item );
	}

	/**
	 * @inheritdoc
	 */
	public function get_provider() { return new ITE_Simple_Taxes_Provider(); }

	/**
	 * Get tax codes.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_codes() {
		$codes = $this->has_param( 'codes' ) ? $this->get_param( 'codes' ) : array();

		if ( ! is_array( $codes ) ) {
			$codes = array();
		}

		return $codes;
	}

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

		$codes = $this->get_codes();

		if ( count( $codes ) !== 0 && ! in_array( $item->get_tax_code( $this->get_provider() ), $codes ) ) {
			return false;
		}

		$settings = it_exchange_get_option( 'addon_taxes_simple', true );

		return ! ( empty( $settings['calculate-after-discounts'] ) && $item->get_type() === 'coupon' );
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

		if ( $this->get_aggregate() ) {
			return $this->get_aggregate()->get_taxable_amount() * $this->get_aggregate()->get_quantity() * ( $this->get_rate() / 100 );
		} else {
			return 0;
		}
	}

	/**
	 * @inheritDoc
	 */
	final public function get_type( $label = false ) { return $label ? __( 'Tax', 'it-l10n-ithemes-exchange' ) : 'tax'; }

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() {
		return $this->frozen->has_param( 'summary_only' ) ? $this->frozen->get_param( 'summary_only' ) : true;
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
	public function __destruct() {
		unset( $this->taxable );
	}
}
