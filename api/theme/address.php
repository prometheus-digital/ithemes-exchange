<?php
/**
 * Address Theme API.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Theme_API_Address
 */
class IT_Theme_API_Address implements IT_Theme_API {

	/** @var ITE_Location */
	private $address;

	/**
	 * IT_Theme_API_Address constructor.
	 */
	public function __construct() {
		$this->address = it_exchange_get_global( 'address' );
	}

	public function get_api_context() { return 'address'; }

	public $_tag_map = array(
		'companyname' => 'company_name',
		'firstname'   => 'first_name',
		'lastname'    => 'last_name',
		'address1'    => 'address1',
		'address2'    => 'address2',
		'city'        => 'city',
		'state'       => 'state',
		'country'     => 'country',
		'zip'         => 'zip',
		'email'       => 'email',
		'phone'       => 'phone',
		'label'       => 'label',
		'id'          => 'id',
		'formatted'   => 'formatted',
		'radioinput'  => 'radio',
	);

	/**
	 * Retrieve the address label.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function label( $options = array() ) {

		if ( ! $this->address ) {
			return '';
		}

		$options = ITUtility::merge_defaults( $options, array(
			'wrap'   => 'span',
			'class'  => 'it-exchange-address--label',
			'format' => 'html',
		) );

		if ( $this->address instanceof ITE_Saved_Address ) {
			$label = $this->address->label ?: $this->address['address1'];
		} else {
			$label = $this->address['address1'];
		}

		if ( $options['has'] ) {
			return (bool) $label;
		}

		switch ( $options['format'] ) {
			case 'value':
				return $label;
			case 'html':
			default:
				return "<{$options['wrap']} class='{$options['class']}'>" . esc_html( $label ) . "<{$options['wrap']}/>";
		}
	}

	/**
	 * Retrieve the address ID if saved address.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function id( $options = array() ) {

		if ( ! $this->address ) {
			return '';
		}

		if ( $options['has'] ) {
			return $this->address instanceof ITE_Saved_Address && $this->address->exists();
		}

		return $this->address instanceof ITE_Saved_Address ? $this->address->get_pk() : 0;
	}

	/**
	 * Retrieve a formatted address.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function formatted( $options = array() ) {

		if ( ! $this->address ) {
			return '';
		}

		$options = ITUtility::merge_defaults( $options, array(
			'format' => null,
		) );

		$format = $options['format'];
		unset( $options['format'] );

		return it_exchange_format_address( $this->address, $options, $format );
	}

	/**
	 * Dynamically retrieve part of the address.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return string
	 */
	public function __call( $name, $arguments ) {

		if ( ! $this->address ) {
			return '';
		}

		$key = str_replace( '_', '-', $name );

		$options = isset( $arguments[0] ) && is_array( $arguments[0] ) ? $arguments[0] : array();
		$options = ITUtility::merge_defaults( $options, array(
			'wrap'   => 'span',
			'class'  => "it-exchange-address--{$key}",
			'format' => 'html',
		) );


		$value = $this->address[ $key ];

		if ( $options['has'] ) {
			return (bool) $value;
		}

		switch ( $options['format'] ) {
			case 'value':
				return $value;
			case 'html':
			default:
				return "<{$options['wrap']} class='{$options['class']}'>" . esc_html( $value ) . "<{$options['wrap']}/>";
		}
	}

	/**
	 * Output a radio selector for this address.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function radio( $options = array() ) {

		if ( ! $this->address instanceof ITE_Saved_Address ) {
			return '';
		}

		$options = ITUtility::merge_defaults( $options, array(
			'name'    => 'address',
			'current' => 0,
		) );

		$id      = $this->address->get_pk();
		$label   = $this->address->label ?: $this->address['address1'];
		$checked = checked( $id, $options['current'], false );

		return "<label><input type='radio' name='{$options['name']}' value='{$id}'{$checked}>" . esc_html( $label ) . "</label>";
	}
}