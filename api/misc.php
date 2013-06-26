<?php
/**
 * These are hooks that add-ons should use for form actions
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns a field name used in links and forms
 *
 * @since 0.4.0
 * @param string $var var being requested
 * @return string var used in links / forms for different actions
*/
function it_exchange_get_field_name( $var ) {
	$field_names = it_exchange_get_field_names();
	$field_name = empty( $field_names[$var] ) ? false : $field_names[$var];
	return apply_filters( 'it_exchange_get_field_name', $field_name, $var );
}

/**
 * Returns an array of all field names registered with iThemes Exchange
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_get_field_names() {
	// required field names
	$required = array(
		'add_product_to_cart'      => 'it-exchange-add-product-to-cart',
		'buy_now'                  => 'it-exchange-buy-now',
		'remove_product_from_cart' => 'it-exchange-remove-product-from-cart',
		'update_cart_action'       => 'it-exchange-update-cart-request',
		'empty_cart'               => 'it-exchange-empty-cart',
		'proceed_to_checkout'      => 'it-exchange-proceed-to-checkout',
		'view_cart'                => 'it-exchange-view-cart',
		'purchase_cart'            => 'it-exchange-purchase-cart',
		'alert_message'            => 'it-exchange-messages',
		'error_message'            => 'it-exchange-errors',
		'transaction_id'           => 'it-exchange-transaction-id',
		'transaction_method'       => 'it-exchange-transaction-method',
		'sw_cart_focus'            => 'ite-sw-cart-focus',
		'sw_ajax_call'             => 'it-exchange-sw-ajax',
		'sw_ajax_action'           => 'sw-action',
		'sw_ajax_product'          => 'sw-product',
		'sw_ajax_quantity'         => 'sw-quantity',
	);
	//We don't want users to modify the core vars, but we should let them add new ones.
	return apply_filters( 'it_exchange_get_field_names', array_merge( $required, apply_filters( 'it_exchange_default_field_names', array() ) ) );
}

/**
 * Grabs the current URL, removes all registerd exchange query_args from it
 *
 * Exempts args in first paramater
 * Cleans additional args in second paramater
 *
 * @since 0.4.0
 *
 * @param array $exempt optional array of query args not to clean
 * @param array $additional opitonal array of params to clean even if not found in register params
 * @return string
*/
function it_exchange_clean_query_args( $exempt=array(), $additional=array() ) {
	// Get registered
	$registered = array_values( (array) it_exchange_get_field_names() );
	$registered = array_merge( $registered, (array) array_values( $additional ) );

	// Additional args
	$registered[] = '_wpnonce';
	$registered[] = apply_filters( 'it_exchange_purchase_product_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_cart_action_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_checkout_action_nonce_var' , '_wpnonce' );
	$registered[] = 'it-exchange-basic-coupons-remove-coupon-cart';

	$registered = array_unique( $registered );

	$url = false;
	foreach( $registered as $key => $param ) {
		if ( ! in_array( $param, $exempt ) )
			$url = remove_query_arg( $param, $url );
	}

	return apply_filters( 'it_exchange_clean_query_args', $url );
}

/**
 * Replace Log in text with Log out text in nav menus
 *
 * @since 0.4.0
 * @todo re-evaluate whether or not we want to proceed with this hackery.
 * @todo verify without permalinks!
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_wp_get_nav_menu_items_filter( $items, $menu, $args ) {
	if ( is_user_logged_in() ) {
		foreach ( $items as $item ) {
			if ( $item->url == it_exchange_get_page_url( 'log_in' ) ) {

				$item->url = it_exchange_get_page_url( 'log_out' );
				$item->title = it_exchange_get_page_name( 'log_out' );
			}
		}
	}
	return apply_filters( 'it_exchange_wp_get_nav_menu_items_filter', $items, $menu, $args );

}
add_filter( 'wp_get_nav_menu_items', 'it_exchange_wp_get_nav_menu_items_filter', 10, 3 );

if ( ! function_exists( 'wp_nav_menu_disabled_check' ) && version_compare( $GLOBALS['wp_version'], '3.5.3', '<=' ) ) {

	/**
	 * From WordPress 3.6.0 for back-compat
	 * Check whether to disable the Menu Locations meta box submit button
	 *
	 * @since 0.4.0
	 *
	 * @uses global $one_theme_location_no_menus to determine if no menus exist
	 * @uses disabled() to output the disabled attribute in $other_attributes param in submit_button()
	 *
	 * @param int|string $nav_menu_selected_id (id, name or slug) of the currently-selected menu
	 * @return string Disabled attribute if at least one menu exists, false if not
	*/
	function wp_nav_menu_disabled_check( $nav_menu_selected_id ) {
		global $one_theme_location_no_menus;

		if ( $one_theme_location_no_menus )
			return false;

		return disabled( $nav_menu_selected_id, 0 );
	}

}

/**
 * Returns currency data
 *
 * @since 0.3.4
 * @todo Cache in a transient
 * @todo Provide param to break cache
 * @todo Better anticipate wp_error
*/
function it_exchange_get_currency_options() {
	// Country Code => array( symbol, name )
	$currencies = array(
		'AED' => array( 'symbol' => '\u062f.\u0625;', 'name' => __( 'UAE dirham', 'LION' ) ),
		'AFN' => array( 'symbol' => 'Afs', 'name' => __( 'Afghan afghani', 'LION' ) ),
		'ALL' => array( 'symbol' => 'L', 'name' => __( 'Albanian lek', 'LION' ) ),
		'AMD' => array( 'symbol' => 'AMD', 'name' => __( 'Armenian dram', 'LION' ) ),
		'ANG' => array( 'symbol' => 'NA\u0192', 'name' => __( 'Netherlands Antillean gulden', 'LION' ) ),
		'AOA' => array( 'symbol' => 'Kz', 'name' => __( 'Angolan kwanza', 'LION' ) ),
		'ARS' => array( 'symbol' => '$', 'name' => __( 'Argentine peso', 'LION' ) ),
		'AUD' => array( 'symbol' => '$', 'name' => __( 'Australian dollar', 'LION' ) ),
		'AWG' => array( 'symbol' => '\u0192', 'name' => __( 'Aruban florin', 'LION' ) ),
		'AZN' => array( 'symbol' => 'AZN', 'name' => __( 'Azerbaijani manat', 'LION' ) ),
		'BAM' => array( 'symbol' => 'KM', 'name' => __( 'Bosnia and Herzegovina konvertibilna marka', 'LION' ) ),
		'BBD' => array( 'symbol' => 'Bds$', 'name' => __( 'Barbadian dollar', 'LION' ) ),
		'BDT' => array( 'symbol' => '\u09f3', 'name' => __( 'Bangladeshi taka', 'LION' ) ),
		'BGN' => array( 'symbol' => 'BGN', 'name' => __( 'Bulgarian lev', 'LION' ) ),
		'BHD' => array( 'symbol' => '.\u062f.\u0628', 'name' => __( 'Bahraini dinar', 'LION' ) ),
		'BIF' => array( 'symbol' => 'FBu', 'name' => __( 'Burundi franc', 'LION' ) ),
		'BMD' => array( 'symbol' => 'BD$', 'name' => __( 'Bermudian dollar', 'LION' ) ),
		'BND' => array( 'symbol' => 'B$', 'name' => __( 'Brunei dollar', 'LION' ) ),
		'BOB' => array( 'symbol' => 'Bs.', 'name' => __( 'Bolivian boliviano', 'LION' ) ),
		'BRL' => array( 'symbol' => 'R$', 'name' => __( 'Brazilian real', 'LION' ) ),
		'BSD' => array( 'symbol' => 'B$', 'name' => __( 'Bahamian dollar', 'LION' ) ),
		'BTN' => array( 'symbol' => 'Nu.', 'name' => __( 'Bhutanese ngultrum', 'LION' ) ),
		'BWP' => array( 'symbol' => 'P', 'name' => __( 'Botswana pula', 'LION' ) ),
		'BYR' => array( 'symbol' => 'Br', 'name' => __( 'Belarusian ruble', 'LION' ) ),
		'BZD' => array( 'symbol' => 'BZ$', 'name' => __( 'Belize dollar', 'LION' ) ),
		'CAD' => array( 'symbol' => '$', 'name' => __( 'Canadian dollar', 'LION' ) ),
		'CDF' => array( 'symbol' => 'F', 'name' => __( 'Congolese franc', 'LION' ) ),
		'CHF' => array( 'symbol' => 'Fr.', 'name' => __( 'Swiss franc', 'LION' ) ),
		'CLP' => array( 'symbol' => '$', 'name' => __( 'Chilean peso', 'LION' ) ),
		'CNY' => array( 'symbol' => '\u00a5', 'name' => __( 'Chinese/Yuan renminbi', 'LION' ) ),
		'COP' => array( 'symbol' => 'Col$', 'name' => __( 'Colombian peso', 'LION' ) ),
		'CRC' => array( 'symbol' => '\u20a1', 'name' => __( 'Costa Rican colon', 'LION' ) ),
		'CUC' => array( 'symbol' => '$', 'name' => __( 'Cuban peso', 'LION' ) ),
		'CVE' => array( 'symbol' => 'Esc', 'name' => __( 'Cape Verdean escudo', 'LION' ) ),
		'CZK' => array( 'symbol' => 'K\u010d', 'name' => __( 'Czech koruna', 'LION' ) ),
		'DJF' => array( 'symbol' => 'Fdj', 'name' => __( 'Djiboutian franc', 'LION' ) ),
		'DKK' => array( 'symbol' => 'Kr', 'name' => __( 'Danish krone', 'LION' ) ),
		'DOP' => array( 'symbol' => 'RD$', 'name' => __( 'Dominican peso', 'LION' ) ),
		'DZD' => array( 'symbol' => '\u062f.\u062c', 'name' => __( 'Algerian dinar', 'LION' ) ),
		'EEK' => array( 'symbol' => 'KR', 'name' => __( 'Estonian kroon', 'LION' ) ),
		'EGP' => array( 'symbol' => '\u00a3', 'name' => __( 'Egyptian pound', 'LION' ) ),
		'ERN' => array( 'symbol' => 'Nfa', 'name' => __( 'Eritrean nakfa', 'LION' ) ),
		'ETB' => array( 'symbol' => 'Br', 'name' => __( 'Ethiopian birr', 'LION' ) ),
		'EUR' => array( 'symbol' => '\u20ac', 'name' => __( 'European Euro', 'LION' ) ),
		'FJD' => array( 'symbol' => 'FJ$', 'name' => __( 'Fijian dollar', 'LION' ) ),
		'FKP' => array( 'symbol' => '\u00a3', 'name' => __( 'Falkland Islands pound', 'LION' ) ),
		'GBP' => array( 'symbol' => '\u00a3', 'name' => __( 'British pound', 'LION' ) ),
		'GEL' => array( 'symbol' => 'GEL', 'name' => __( 'Georgian lari', 'LION' ) ),
		'GHS' => array( 'symbol' => 'GH\u20b5', 'name' => __( 'Ghanaian cedi', 'LION' ) ),
		'GIP' => array( 'symbol' => '\u00a3', 'name' => __( 'Gibraltar pound', 'LION' ) ),
		'GMD' => array( 'symbol' => 'D', 'name' => __( 'Gambian dalasi', 'LION' ) ),
		'GNF' => array( 'symbol' => 'FG', 'name' => __( 'Guinean franc', 'LION' ) ),
		'GQE' => array( 'symbol' => 'CFA', 'name' => __( 'Central African CFA franc', 'LION' ) ),
		'GTQ' => array( 'symbol' => 'Q', 'name' => __( 'Guatemalan quetzal', 'LION' ) ),
		'GYD' => array( 'symbol' => 'GY$', 'name' => __( 'Guyanese dollar', 'LION' ) ),
		'HKD' => array( 'symbol' => 'HK$', 'name' => __( 'Hong Kong dollar', 'LION' ) ),
		'HNL' => array( 'symbol' => 'L', 'name' => __( 'Honduran lempira', 'LION' ) ),
		'HRK' => array( 'symbol' => 'kn', 'name' => __( 'Croatian kuna', 'LION' ) ),
		'HTG' => array( 'symbol' => 'G', 'name' => __( 'Haitian gourde', 'LION' ) ),
		'HUF' => array( 'symbol' => 'Ft', 'name' => __( 'Hungarian forint', 'LION' ) ),
		'IDR' => array( 'symbol' => 'Rp', 'name' => __( 'Indonesian rupiah', 'LION' ) ),
		'ILS' => array( 'symbol' => '\u20aa', 'name' => __( 'Israeli new sheqel', 'LION' ) ),
		'INR' => array( 'symbol' => '\u2089', 'name' => __( 'Indian rupee', 'LION' ) ),
		'IQD' => array( 'symbol' => '\u062f.\u0639', 'name' => __( 'Iraqi dinar', 'LION' ) ),
		'IRR' => array( 'symbol' => 'IRR', 'name' => __( 'Iranian rial', 'LION' ) ),
		'ISK' => array( 'symbol' => 'kr', 'name' => __( 'Icelandic kr\u00f3na', 'LION' ) ),
		'JMD' => array( 'symbol' => 'J$', 'name' => __( 'Jamaican dollar', 'LION' ) ),
		'JOD' => array( 'symbol' => 'JOD', 'name' => __( 'Jordanian dinar', 'LION' ) ),
		'JPY' => array( 'symbol' => '\u00a5', 'name' => __( 'Japanese yen', 'LION' ) ),
		'KES' => array( 'symbol' => 'KSh', 'name' => __( 'Kenyan shilling', 'LION' ) ),
		'KGS' => array( 'symbol' => '\u0441\u043e\u043c', 'name' => __( 'Kyrgyzstani som', 'LION' ) ),
		'KHR' => array( 'symbol' => '\u17db', 'name' => __( 'Cambodian riel', 'LION' ) ),
		'KMF' => array( 'symbol' => 'KMF', 'name' => __( 'Comorian franc', 'LION' ) ),
		'KPW' => array( 'symbol' => 'W', 'name' => __( 'North Korean won', 'LION' ) ),
		'KRW' => array( 'symbol' => 'W', 'name' => __( 'South Korean won', 'LION' ) ),
		'KWD' => array( 'symbol' => 'KWD', 'name' => __( 'Kuwaiti dinar', 'LION' ) ),
		'KYD' => array( 'symbol' => 'KY$', 'name' => __( 'Cayman Islands dollar', 'LION' ) ),
		'KZT' => array( 'symbol' => 'T', 'name' => __( 'Kazakhstani tenge', 'LION' ) ),
		'LAK' => array( 'symbol' => 'KN', 'name' => __( 'Lao kip', 'LION' ) ),
		'LBP' => array( 'symbol' => '\u00a3', 'name' => __( 'Lebanese lira', 'LION' ) ),
		'LKR' => array( 'symbol' => 'Rs', 'name' => __( 'Sri Lankan rupee', 'LION' ) ),
		'LRD' => array( 'symbol' => 'L$', 'name' => __( 'Liberian dollar', 'LION' ) ),
		'LSL' => array( 'symbol' => 'M', 'name' => __( 'Lesotho loti', 'LION' ) ),
		'LTL' => array( 'symbol' => 'Lt', 'name' => __( 'Lithuanian litas', 'LION' ) ),
		'LVL' => array( 'symbol' => 'Ls', 'name' => __( 'Latvian lats', 'LION' ) ),
		'LYD' => array( 'symbol' => 'LD', 'name' => __( 'Libyan dinar', 'LION' ) ),
		'MAD' => array( 'symbol' => 'MAD', 'name' => __( 'Moroccan dirham', 'LION' ) ),
		'MDL' => array( 'symbol' => 'MDL', 'name' => __( 'Moldovan leu', 'LION' ) ),
		'MGA' => array( 'symbol' => 'FMG', 'name' => __( 'Malagasy ariary', 'LION' ) ),
		'MKD' => array( 'symbol' => 'MKD', 'name' => __( 'Macedonian denar', 'LION' ) ),
		'MMK' => array( 'symbol' => 'K', 'name' => __( 'Myanma kyat', 'LION' ) ),
		'MNT' => array( 'symbol' => '\u20ae', 'name' => __( 'Mongolian tugrik', 'LION' ) ),
		'MOP' => array( 'symbol' => 'P', 'name' => __( 'Macanese pataca', 'LION' ) ),
		'MRO' => array( 'symbol' => 'UM', 'name' => __( 'Mauritanian ouguiya', 'LION' ) ),
		'MUR' => array( 'symbol' => 'Rs', 'name' => __( 'Mauritian rupee', 'LION' ) ),
		'MVR' => array( 'symbol' => 'Rf', 'name' => __( 'Maldivian rufiyaa', 'LION' ) ),
		'MWK' => array( 'symbol' => 'MK', 'name' => __( 'Malawian kwacha', 'LION' ) ),
		'MXN' => array( 'symbol' => '$', 'name' => __( 'Mexican peso', 'LION' ) ),
		'MYR' => array( 'symbol' => 'RM', 'name' => __( 'Malaysian ringgit', 'LION' ) ),
		'MZM' => array( 'symbol' => 'MTn', 'name' => __( 'Mozambican metical', 'LION' ) ),
		'NAD' => array( 'symbol' => 'N$', 'name' => __( 'Namibian dollar', 'LION' ) ),
		'NGN' => array( 'symbol' => '\u20a6', 'name' => __( 'Nigerian naira', 'LION' ) ),
		'NIO' => array( 'symbol' => 'C$', 'name' => __( 'Nicaraguan c\u00f3rdoba', 'LION' ) ),
		'NOK' => array( 'symbol' => 'kr', 'name' => __( 'Norwegian krone', 'LION' ) ),
		'NPR' => array( 'symbol' => 'NRs', 'name' => __( 'Nepalese rupee', 'LION' ) ),
		'NZD' => array( 'symbol' => 'NZ$', 'name' => __( 'New Zealand dollar', 'LION' ) ),
		'OMR' => array( 'symbol' => 'OMR', 'name' => __( 'Omani rial', 'LION' ) ),
		'PAB' => array( 'symbol' => 'B./', 'name' => __( 'Panamanian balboa', 'LION' ) ),
		'PEN' => array( 'symbol' => 'S/.', 'name' => __( 'Peruvian nuevo sol', 'LION' ) ),
		'PGK' => array( 'symbol' => 'K', 'name' => __( 'Papua New Guinean kina', 'LION' ) ),
		'PHP' => array( 'symbol' => '\u20b1', 'name' => __( 'Philippine peso', 'LION' ) ),
		'PKR' => array( 'symbol' => 'Rs.', 'name' => __( 'Pakistani rupee', 'LION' ) ),
		'PLN' => array( 'symbol' => 'z\u0142', 'name' => __( 'Polish zloty', 'LION' ) ),
		'PYG' => array( 'symbol' => '\u20b2', 'name' => __( 'Paraguayan guarani', 'LION' ) ),
		'QAR' => array( 'symbol' => 'QR', 'name' => __( 'Qatari riyal', 'LION' ) ),
		'RON' => array( 'symbol' => 'L', 'name' => __( 'Romanian leu', 'LION' ) ),
		'RSD' => array( 'symbol' => 'din.', 'name' => __( 'Serbian dinar', 'LION' ) ),
		'RUB' => array( 'symbol' => 'R', 'name' => __( 'Russian ruble', 'LION' ) ),
		'SAR' => array( 'symbol' => 'SR', 'name' => __( 'Saudi riyal', 'LION' ) ),
		'SBD' => array( 'symbol' => 'SI$', 'name' => __( 'Solomon Islands dollar', 'LION' ) ),
		'SCR' => array( 'symbol' => 'SR', 'name' => __( 'Seychellois rupee', 'LION' ) ),
		'SDG' => array( 'symbol' => 'SDG', 'name' => __( 'Sudanese pound', 'LION' ) ),
		'SEK' => array( 'symbol' => 'kr', 'name' => __( 'Swedish krona', 'LION' ) ),
		'SGD' => array( 'symbol' => 'S$', 'name' => __( 'Singapore dollar', 'LION' ) ),
		'SHP' => array( 'symbol' => '\u00a3', 'name' => __( 'Saint Helena pound', 'LION' ) ),
		'SLL' => array( 'symbol' => 'Le', 'name' => __( 'Sierra Leonean leone', 'LION' ) ),
		'SOS' => array( 'symbol' => 'Sh.', 'name' => __( 'Somali shilling', 'LION' ) ),
		'SRD' => array( 'symbol' => '$', 'name' => __( 'Surinamese dollar', 'LION' ) ),
		'SYP' => array( 'symbol' => 'LS', 'name' => __( 'Syrian pound', 'LION' ) ),
		'SZL' => array( 'symbol' => 'E', 'name' => __( 'Swazi lilangeni', 'LION' ) ),
		'THB' => array( 'symbol' => '\u0e3f', 'name' => __( 'Thai baht', 'LION' ) ),
		'TJS' => array( 'symbol' => 'TJS', 'name' => __( 'Tajikistani somoni', 'LION' ) ),
		'TMT' => array( 'symbol' => 'm', 'name' => __( 'Turkmen manat', 'LION' ) ),
		'TND' => array( 'symbol' => 'DT', 'name' => __( 'Tunisian dinar', 'LION' ) ),
		'TRY' => array( 'symbol' => 'TRY', 'name' => __( 'Turkish new lira', 'LION' ) ),
		'TTD' => array( 'symbol' => 'TT$', 'name' => __( 'Trinidad and Tobago dollar', 'LION' ) ),
		'TWD' => array( 'symbol' => 'NT$', 'name' => __( 'New Taiwan dollar', 'LION' ) ),
		'TZS' => array( 'symbol' => 'TZS', 'name' => __( 'Tanzanian shilling', 'LION' ) ),
		'UAH' => array( 'symbol' => 'UAH', 'name' => __( 'Ukrainian hryvnia', 'LION' ) ),
		'UGX' => array( 'symbol' => 'USh', 'name' => __( 'Ugandan shilling', 'LION' ) ),
		'USD' => array( 'symbol' => '$', 'name' => __( 'United States dollar', 'LION' ) ),
		'UYU' => array( 'symbol' => '$U', 'name' => __( 'Uruguayan peso', 'LION' ) ),
		'UZS' => array( 'symbol' => 'UZS', 'name' => __( 'Uzbekistani som', 'LION' ) ),
		'VEB' => array( 'symbol' => 'Bs', 'name' => __( 'Venezuelan bolivar', 'LION' ) ),
		'VND' => array( 'symbol' => '\u20ab', 'name' => __( 'Vietnamese dong', 'LION' ) ),
		'VUV' => array( 'symbol' => 'VT', 'name' => __( 'Vanuatu vatu', 'LION' ) ),
		'WST' => array( 'symbol' => 'WS$', 'name' => __( 'Samoan tala', 'LION' ) ),
		'XAF' => array( 'symbol' => 'CFA', 'name' => __( 'Central African CFA franc', 'LION' ) ),
		'XCD' => array( 'symbol' => 'EC$', 'name' => __( 'East Caribbean dollar', 'LION' ) ),
		'XDR' => array( 'symbol' => 'SDR', 'name' => __( 'Special Drawing Rights', 'LION' ) ),
		'XOF' => array( 'symbol' => 'CFA', 'name' => __( 'West African CFA franc', 'LION' ) ),
		'XPF' => array( 'symbol' => 'F', 'name' => __( 'CFP franc', 'LION' ) ),
		'YER' => array( 'symbol' => 'YER', 'name' => __( 'Yemeni rial', 'LION' ) ),
		'ZAR' => array( 'symbol' => 'R', 'name' => __( 'South African rand', 'LION' ) ),
		'ZMK' => array( 'symbol' => 'ZK', 'name' => __( 'Zambian kwacha', 'LION' ) ),
		'ZWR' => array( 'symbol' => 'Z$', 'name' => __( 'Zimbabwean dollar', 'LION' ) ),
	);
	
	return apply_filters( 'it_exchange_get_currency_options', $currencies );
}

/**
 * Returns the currency symbol based on the currency key
 *
 * @since 0.4.0
 *
 * @param string $country_code country code for the currency
 * @return string
*/
function it_exchange_get_currency_symbol( $country_code ) {
	$currencies = it_exchange_get_currency_options();
	$symbol = empty( $currencies[$country_code] ) ? '$' : $currencies[$country_code];
	return apply_filters( 'it_exchange_get_currency_symbol', $symbol );
}
