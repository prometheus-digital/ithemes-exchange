<?php
/**
 * Billing class for THEME API
 *
 * @since 1.2.2
*/

class IT_Theme_API_Billing implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.2.2
	*/
	private $_context = 'billing';

	/**
	 * Current customer Billing Address
	 * @var string $_billing_address
	 * @since 1.2.2
	*/
	private $_billing_address = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.2.2
	*/
	public $_tag_map = array(
		'firstname'   => 'first_name',
		'lastname'    => 'last_name',
		'companyname' => 'company_name',
		'address1'    => 'address1',
		'address2'    => 'address2',
		'city'        => 'city',
		'state'       => 'state',
		'zip'         => 'zip',
		'country'     => 'country',
		'email'       => 'email',
		'phone'       => 'phone',
		'submit'      => 'submit',
		'cancel'      => 'cancel',
	);

	/**
	 * Constructor
	 *
	 * @since 1.2.2
	 * @todo get working for admins looking at other users profiles
	 * @return void
	*/
	function IT_Theme_API_Billing() {
		$this->_billing_address = it_exchange_get_cart_billing_address();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.2.2
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Outputs the billing address first name data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function first_name( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'First Name', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-first-name';
		$options['field_name'] = 'it-exchange-billing-address-first-name';
		$options['value']      = empty( $this->_billing_address['first-name'] ) ? '' : $this->_billing_address['first-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address last name data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function last_name( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Last Name', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-last-name';
		$options['field_name'] = 'it-exchange-billing-address-last-name';
		$options['value']      = empty( $this->_billing_address['last-name'] ) ? '' : $this->_billing_address['last-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address compnay name data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function company_name( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Company Name', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-company-name';
		$options['field_name'] = 'it-exchange-billing-address-company-name';
		$options['value']      = empty( $this->_billing_address['company-name'] ) ? '' : $this->_billing_address['company-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address address 1 data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function address1( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Address 1', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-address1';
		$options['field_name'] = 'it-exchange-billing-address-address1';
		$options['value']      = empty( $this->_billing_address['address1'] ) ? '' : $this->_billing_address['address1'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address address 2data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function address2( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Address 2', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-address2';
		$options['field_name'] = 'it-exchange-billing-address-address2';
		$options['value']      = empty( $this->_billing_address['address2'] ) ? '' : $this->_billing_address['address2'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address city data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function city( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'City', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-city';
		$options['field_name'] = 'it-exchange-billing-address-city';
		$options['value']      = empty( $this->_billing_address['city'] ) ? '' : $this->_billing_address['city'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address state data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function state( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'State', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-state';
		$options['field_name'] = 'it-exchange-billing-address-state';
		$options['value']      = empty( $this->_billing_address['state'] ) ? '' : $this->_billing_address['state'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address zip data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function zip( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Zip Code', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-zip';
		$options['field_name'] = 'it-exchange-billing-address-zip';
		$options['value']      = empty( $this->_billing_address['zip'] ) ? '' : $this->_billing_address['zip'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address country data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function country( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Country', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-country';
		$options['field_name'] = 'it-exchange-billing-address-country';
		$options['value']      = empty( $this->_billing_address['country'] ) ? '' : $this->_billing_address['country'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address email data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Email', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-email';
		$options['field_name'] = 'it-exchange-billing-address-email';
		$options['value']      = empty( $this->_billing_address['email'] ) ? '' : $this->_billing_address['email'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address phone data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function phone( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Phone', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-phone';
		$options['field_name'] = 'it-exchange-billing-address-phone';
		$options['value']      = empty( $this->_billing_address['phone'] ) ? '' : $this->_billing_address['phone'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address submit button 
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function submit( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Submit', 'LION' ),
			'name'   => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-billing-address-submit';

		return $output = '<input type="submit" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['name'] ) . '" value="'. esc_attr( $options['label'] ) .'" />';
	}

	/**
	 * Outputs the billing address phone data
	 *
	 * @since 1.2.2
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Cancel', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		return '<a class="it-exchange-billing-address-requirement-cancel" href="' . it_exchange_get_page_url( 'checkout' ) . '">' . $options['label'] . '</a>';
	}

	/**
	 * Gets the HTML is the desired format
	 *
	 * @since 1.2.2
	 *
	 * @param array $options
	 * @return mixed
	*/
	function get_fields( $options ) {
		
		$value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );

		switch( $options['format'] ) {

			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = '<input type="text" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="" />';
				break;
			case 'value':
				$output = $value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="' . $value . '" />';
		}

		return $output;
	}
}
