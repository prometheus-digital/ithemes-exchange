<?php
/**
 * Fee line item.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Fee_Line_Item
 */
class ITE_Fee_Line_Item extends ITE_Line_Item implements ITE_Aggregatable_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware {

	/** @var ITE_Aggregate_Line_Item */
	private $aggregate;

	/** @var  ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/**
	 * Create a new Fee.
	 *
	 * @since 1.36.0
	 *
	 * @param string $name
	 * @param float  $amount
	 * @param bool   $tax_exempt
	 *
	 * @return \ITE_Fee_Line_Item
	 */
	public static function create( $name, $amount, $tax_exempt = false ) {

		$id = md5( uniqid( 'FEE', true ) );

		$bag = new ITE_Array_Parameter_Bag( array(
			'name'       => $name,
			'amount'     => $amount,
			'tax_exempt' => $tax_exempt
		) );

		return new self( $id, $bag, new ITE_Array_Parameter_Bag() );
	}

	/**
	 * Get the base amount of this fee.
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	protected function get_base_amount() { return (float) $this->get_param( 'amount' ); }

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
	public function get_name() {
		return $this->get_param( 'name' );
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
	public function get_quantity() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_amount() {
		$base = $this->get_base_amount();

		foreach ( $this->aggregatables as $aggregatable ) {
			if ( ! $aggregatable->is_summary_only() ) {
				$base += $aggregatable->get_total();
			}
		}

		return $base;
	}


	/**
	 * @inheritDoc
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Fee', 'it-l10n-ithemes-exchange' ) : 'fee';
	}

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() { return false; }

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt( ITE_Tax_Provider $for ) {
		return (bool) $this->get_param( 'tax_exempt' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) {

		if ( $this->aggregate instanceof ITE_Taxable_Line_Item ) {
			return $this->aggregate->get_tax_code( $for );
		} elseif ( $this->has_param( 'tax_code' ) ) {
			return $this->get_param( 'tax_code' );
		} else {
			return '';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() { return $this->get_taxable_amount(); }

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
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->aggregate = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->aggregate; }

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}
}