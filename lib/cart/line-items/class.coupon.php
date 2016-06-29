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
class ITE_Coupon_Line_Item implements ITE_Aggregatable_Line_Item {

	/** @var IT_Exchange_Coupon */
	private $coupon;

	/** @var ITE_Parameter_Bag */
	private $bag;

	/** @var ITE_Aggregate_Line_Item */
	private $aggregate;

	/** @var string */
	private $id;

	/** @var float */
	private $amount;

	/**
	 * ITE_Coupon_Line_Item constructor.
	 *
	 * @param \IT_Exchange_Coupon $coupon
	 * @param float               $amount
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( \IT_Exchange_Coupon $coupon, $amount ) {

		if ( ! $coupon->get_type() ) {
			throw new InvalidArgumentException(
				sprintf( 'Coupon of class %s needs to provide a valid get_type().', get_class( $coupon ) )
			);
		}

		if ( $amount >= 0 ) {
			$amount = -$amount;
		}

		$this->id     = $coupon->get_code();
		$this->coupon = $coupon;
		$this->amount = $amount;
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
	public function get_amount() { return $this->amount; }

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
			'code'   => $this->get_coupon()->get_code(),
			'type'   => $this->get_coupon()->get_type(),
			'amount' => $this->get_amount(),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_data( $id, array $data, ITE_Line_Item_Repository $repository ) {

		$self     = new self( it_exchange_get_coupon_from_code( $data['code'], $data['type'] ), $data['amount'] );
		$self->id = $id;

		return $self;
	}
}