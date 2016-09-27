<?php
/**
 * Payment Token model.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Model;

/**
 * Class ITE_Payment_Token
 *
 * @property int                        $ID
 * @property-read \IT_Exchange_Customer $customer
 * @property-read string                $token
 * @property-read ITE_Gateway           $gateway
 * @property string                     $label
 * @property string                     $redacted
 * @property-read bool                  $primary
 */
class ITE_Payment_Token extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() { return $this->ID; }

	/**
	 * Make this the primary payment token for the customer.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function make_primary() {

		if ( ! $this->customer || ! $this->customer->ID ) {
			return false;
		}

		if ( $this->primary ) {
			return true;
		}

		/** @var static $other */
		$other = static::query()->where( 'customer', '=', $this->customer->ID )->and_where( 'primary', '=', true )->first();

		if ( $other ) {
			$other->set_attribute( 'primary', false );


			if ( ! $other->save() ) {
				return false;
			}
		}

		$this->set_attribute( 'primary', true );

		return $this->save();
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->redacted;
	}

	/**
	 * Get all payment tokens for a customer.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return \IronBound\DB\Collection|ITE_Payment_Token[]
	 */
	public static function for_customer( IT_Exchange_Customer $customer ) {
		return static::query()->where( 'customer', '=', $customer->ID )->results();
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() { return static::$_db_manager->get( 'ite-payment-tokens' ); }

	// Retrieve an IT_Exchange_Customer instead of WP_User for 'customer'
	protected function _access_customer( $value ) {
		return it_exchange_get_customer( $value );
	}

	protected function _mutate_customer( $value ) {

		if ( $value instanceof IT_Exchange_Customer ) {
			$value = $value->wp_user;
		}

		return $value;
	}

	// Retrieve an ITE_Gateway instead of the gateway slug.
	protected function _access_gateway( $value ) {
		return ITE_Gateways::get( $value );
	}
}