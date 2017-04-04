<?php
/**
 * Fee line item.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Fee_Line_Item
 */
class ITE_Fee_Line_Item extends ITE_Line_Item implements ITE_Aggregatable_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware, ITE_Requires_Optionally_Supported_Features {

	/** @var ITE_Aggregate_Line_Item */
	private $aggregate;

	/** @var  ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/**
	 * Create a new Fee.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name         The human label of the fee.
	 * @param float  $amount       The total of the fee.
	 * @param bool   $is_taxable   Is this fee taxable.
	 * @param bool   $is_recurring Is this fee included in any child payments.
	 *
	 * @return \ITE_Fee_Line_Item
	 */
	public static function create( $name, $amount, $is_taxable = true, $is_recurring = true ) {

		$id = self::generate_id();

		$bag = new ITE_Array_Parameter_Bag( array(
			'name'         => $name,
			'amount'       => $amount,
			'is_taxable'   => $is_taxable,
			'is_recurring' => $is_recurring,
		) );

		return new self( $id, $bag, new ITE_Array_Parameter_Bag() );
	}

	/**
	 * Create a new One Time Fee.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name
	 * @param float  $amount
	 * @param bool   $is_taxable
	 *
	 * @return ITE_Fee_Line_Item
	 */
	public static function one_time( $name, $amount, $is_taxable = true ) {
		return self::create( $name, $amount, $is_taxable, false );
	}

	/**
	 * Generate the ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected final static function generate_id() {
		return md5( uniqid( 'FEE', true ) );
	}

	/**
	 * @inheritDoc
	 */
	public function clone_with_new_id( $include_frozen = true ) {
		return new self(
			self::generate_id(),
			$this->bag,
			$include_frozen ? $this->frozen : new ITE_Array_Parameter_Bag()
		);
	}

	/**
	 * Is this a recurring fee.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_recurring() {
		return $this->has_param( 'is_recurring' ) && $this->get_param( 'is_recurring' );
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
	public function get_amount() { return (float) $this->get_param( 'amount' ); }

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
		return ! $this->is_taxable();
	}

	/**
	 * Is this fee taxable.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_taxable() {
		return (bool) $this->get_param( 'is_taxable' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) {

		$code = '';

		if ( $for->inherit_tax_code_from_aggregate() ) {

			$aggregate = $this->get_aggregate();

			if ( $aggregate instanceof ITE_Taxable_Line_Item ) {
				$code = $aggregate->get_tax_code( $for );
			}
		}

		if ( $code ) {
			return $code;
		} elseif ( $this->has_param( 'tax_code' ) ) {
			return $this->get_param( 'tax_code' );
		} else {
			return $for->get_tax_code_for_item( $this );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() { return $this->get_amount(); }

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

	/**
	 * @inheritDoc
	 */
	public function optional_features_required() {

		if ( $this->is_recurring() || $this->has_param( 'is_free_trial' ) ) {
			return array();
		}

		$details = array();

		if ( $this->get_amount() < 0 ) {
			$details['discount'] = true;
		}

		$requirement = new ITE_Optionally_Supported_Feature_Requirement(
			new ITE_Optionally_Supported_In_Memory_Feature(
				'one-time-fee',
				__( 'Fee', 'it-l10n-ithemes-exchange' ),
				array( 'discount' )
			),
			$details
		);

		return array( $requirement );
	}

	/**
	 * @inheritDoc
	 */
	public function __destruct() {
		unset( $this->aggregate, $this->aggregatables, $this->repository );
	}
}
