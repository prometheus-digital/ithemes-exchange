<?php
/**
 * This file contains data sets for country states/provinces
 * @package IT_Exchagne
 * @since 1.2.0
 */

/**
 * Grabs the array we want based on country
 *
 * @since 1.2.0
 *
 * @param array $options
*/
function it_exchange_get_country_states( $options=array() ) {
	// Defaults
	$defaults = array(
		'country' => false,
	);

	$options = ITUtility::merge_defaults( $options, $defaults );

	// Core supports the following countries
	$supported_countries = array(
		'AU' => 'it_exchange_get_country_states_for_au',
		'CA' => 'it_exchange_get_country_states_for_ca',
		'DE' => 'it_exchange_get_country_states_for_de',
		'NL' => 'it_exchange_get_country_states_for_nl',
		'US' => 'it_exchange_get_country_states_for_us',
		'ZA' => 'it_exchange_get_country_states_for_za',
	);
	$supported_countries = apply_filters( 'it_exchange_get_country_states_supported_countries', $supported_countries );

	// Return the state data if its supported and we can find a callable function
	if ( ! empty( $options['country'] ) && isset( $supported_countries[$options['country']] ) && is_callable( $supported_countries[$options['country']] ) )
		return call_user_func( $supported_countries[$options['country']], $options );

	return false;
}

/**
 * Returns an array of states for AU
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_au( $options=array() ) {
	// Defaults
	$defaults = array(
		'include-territories'             => true,
		'sort-territories-alphabetically' => false,
	);

	$options = ITUtility::merge_defaults( $options, $defaults );
	// States
	$states = array(
		'NSW' => __( 'New South Wales', 'LION' ),
		'QLD' => __( 'Queensland', 'LION' ),
		'SA'  => __( 'South Australia', 'LION' ),
		'TAS' => __( 'Tasmania', 'LION' ),
		'VIC' => __( 'Victoria', 'LION' ),
		'WA'  => __( 'Western Australia', 'LION' )
	);
	$states = apply_filters( 'it_exchange_au_states', $states );

	// Territories
	$territories = array(
		'ACT' => __( 'Australian Capital Territory', 'LION' ),
		'JBT' => __( 'Jervis Bay Territory', 'LION' ),
		'NT'  => __( 'Northern Territory', 'LION' ),
		
	);
	$territories = apply_filters( 'it_exchange_au_territories', $territories );

	// Merge territories and states if needed
	if ( ! empty( $options['include-territories'] ) )
		$states = array_merge( $states, $territories );

	// Sort alphabetically or keep territories at the end
	if ( ! empty( $options['sort-territories-alphabetically'] ) )
		ksort( $states );

	$states = apply_filters( 'it_exchange_get_country_states_for_au', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the Canada
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_ca( $options=array() ) {
	// States
	$states = array(
		'AB' => __( 'Alberta', 'LION' ),
		'BC' => __( 'British Columbia', 'LION' ),
		'MB' => __( 'Manitoba', 'LION' ),
		'NB' => __( 'New Brunswick', 'LION' ),
		'NF' => __( 'Newfoundland', 'LION' ),
		'NT' => __( 'Northwest Territories', 'LION' ),
		'NS' => __( 'Nova Scotia', 'LION' ),
		'NU' => __( 'Nunavut', 'LION' ),
		'ON' => __( 'Ontario', 'LION' ),
		'PE' => __( 'Prince Edward Island', 'LION' ),
		'QC' => __( 'Quebec', 'LION' ),
		'SK' => __( 'Saskatchewan', 'LION' ),
		'YT' => __( 'Yukon Territory', 'LION' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_ca', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the Germany
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_de( $options=array() ) {
	// States
	$states = array(
		'BW' => __( 'Baden-Württemberg', 'LION' ),
		'BY' => __( 'Bayern', 'LION' ),
		'BE' => __( 'Berlin', 'LION' ),
		'BB' => __( 'Brandenburg', 'LION' ),
		'HB' => __( 'Bremen', 'LION' ),
		'HH' => __( 'Hamburg', 'LION' ),
		'HE' => __( 'Hessen', 'LION' ),
		'MV' => __( 'Mecklenburg-Vorpommern', 'LION' ),
		'NI' => __( 'Niedersachsen', 'LION' ),
		'NW' => __( 'Nordrhein-Westfalen', 'LION' ),
		'RP' => __( 'Rheinland-Pfalz', 'LION' ),
		'SL' => __( 'Saarland', 'LION' ),
		'SN' => __( 'Sachsen', 'LION' ),
		'ST' => __( 'Sachsen-Anhalt', 'LION' ),
		'SH' => __( 'Schleswig-Holstein', 'LION' ),
		'TH' => __( 'Thüringen', 'LION' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_de', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the Netherlands
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_nl( $options=array() ) {
	// States
	$states = array(
		'DR' => __( 'Drenthe', 'LION' ),
		'FL' => __( 'Flevoland', 'LION' ),
		'FR' => __( 'Fryslân', 'LION' ),
		'GE' => __( 'Gelderland', 'LION' ),
		'GR' => __( 'Groningen', 'LION' ),
		'LI' => __( 'Limburg', 'LION' ),
		'NB' => __( 'Noord-Brabant', 'LION' ),
		'NH' => __( 'Noord-Holland', 'LION' ),
		'OV' => __( 'Overijssel', 'LION' ),
		'UT' => __( 'Utrecht', 'LION' ),
		'ZE' => __( 'Zeeland', 'LION' ),
		'ZH' => __( 'Zuid-Holland', 'LION' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_nl', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the US
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_us( $options=array() ) {

	// Defaults
	$defaults = array(
		'include-territories'             => false,
		'sort-territories-alphabetically' => false,
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// States array
	$states = array(
		'AL' => __( 'Alabama', 'LION' ),
		'AK' => __( 'Alaska', 'LION' ),
		'AZ' => __( 'Arizona', 'LION' ),
		'AR' => __( 'Arkansas', 'LION' ),
		'CA' => __( 'California', 'LION' ),
		'CO' => __( 'Colorado', 'LION' ),
		'CT' => __( 'Connecticut', 'LION' ),
		'DE' => __( 'Delaware', 'LION' ),
		'DC' => __( 'District Of Columbia', 'LION' ),
		'FL' => __( 'Florida', 'LION' ),
		'GA' => __( 'Georgia', 'LION' ),
		'HI' => __( 'Hawaii', 'LION' ),
		'ID' => __( 'Idaho', 'LION' ),
		'IL' => __( 'Illinois', 'LION' ),
		'IN' => __( 'Indiana', 'LION' ),
		'IA' => __( 'Iowa', 'LION' ),
		'KS' => __( 'Kansas', 'LION' ),
		'KY' => __( 'Kentucky', 'LION' ),
		'LA' => __( 'Louisiana', 'LION' ),
		'ME' => __( 'Maine', 'LION' ),
		'MD' => __( 'Maryland', 'LION' ),
		'MA' => __( 'Massachusetts', 'LION' ),
		'MI' => __( 'Michigan', 'LION' ),
		'MN' => __( 'Minnesota', 'LION' ),
		'MS' => __( 'Mississippi', 'LION' ),
		'MO' => __( 'Missouri', 'LION' ),
		'MT' => __( 'Montana', 'LION' ),
		'NE' => __( 'Nebraska', 'LION' ),
		'NV' => __( 'Nevada', 'LION' ),
		'NH' => __( 'New Hampshire', 'LION' ),
		'NJ' => __( 'New Jersey', 'LION' ),
		'NM' => __( 'New Mexico', 'LION' ),
		'NY' => __( 'New York', 'LION' ),
		'NC' => __( 'North Carolina', 'LION' ),
		'ND' => __( 'North Dakota', 'LION' ),
		'OH' => __( 'Ohio', 'LION' ),
		'OK' => __( 'Oklahoma', 'LION' ),
		'OR' => __( 'Oregon', 'LION' ),
		'PA' => __( 'Pennsylvania', 'LION' ),
		'RI' => __( 'Rhode Island', 'LION' ),
		'SC' => __( 'South Carolina', 'LION' ),
		'SD' => __( 'South Dakota', 'LION' ),
		'TN' => __( 'Tennessee', 'LION' ),
		'TX' => __( 'Texas', 'LION' ),
		'UT' => __( 'Utah', 'LION' ),
		'VT' => __( 'Vermont', 'LION' ),
		'VA' => __( 'Virginia', 'LION' ),
		'WA' => __( 'Washington', 'LION' ),
		'WV' => __( 'West Virginia', 'LION' ),
		'WI' => __( 'Wisconsin', 'LION' ),
		'WY' => __( 'Wyoming', 'LION' ),
	);
	$states = apply_filters( 'it_exchange_us_states', $states );

	// Territories
	$territories = array(
		'AS' => __( 'American Samoa', 'LION' ),
		'FM' => __( 'Federated States of Micronesia', 'LION' ),
		'GU' => __( 'Guam', 'LION' ),
		'MH' => __( 'Marshall Islands', 'LION' ),
		'MP' => __( 'Northern Mariana Islands', 'LION' ),
		'PR' => __( 'Puerto Rico', 'LION' ),
		'PW' => __( 'Palau', 'LION' ),
		'VI' => __( 'Virgin Islands', 'LION' ),
	);
	$territories = apply_filters( 'it_exchange_us_territories', $territories );

	// Include territories?
	if ( ! empty( $options['include-territories'] ) )
		$states = array_merge( $states, $territories );

	// Sort alphabetically or keep territories at the end
	if ( ! empty( $options['sort-territories-alphabetically'] ) )
		ksort( $states );

	$states = apply_filters( 'it_exchange_get_country_states_for_us', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the South Africa
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_za( $options=array() ) {
	// States
	$states = array(
		'EC'  => __( 'Eastern Cape', 'LION' ) ,
		'FS'  => __( 'Free State', 'LION' ) ,
		'GP'  => __( 'Gauteng', 'LION' ) ,
		'KZN' => __( 'KwaZulu-Natal', 'LION' ) ,
		'LP'  => __( 'Limpopo', 'LION' ) ,
		'MP'  => __( 'Mpumalanga', 'LION' ) ,
		'NC'  => __( 'Northern Cape', 'LION' ) ,
		'NW'  => __( 'North West', 'LION' ) ,
		'WC'  => __( 'Western Cape', 'LION' )
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_za', $states, $options );
	return $states;
}
