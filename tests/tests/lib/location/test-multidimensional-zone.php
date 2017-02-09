<?php
/**
 * Test the multidimensional zone class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Multidimensional_Zone_Test
 *
 * @group location
 */
class IT_Exchange_Multidimensional_Zone_Test extends IT_Exchange_UnitTestCase {

	/**
	 * @dataProvider _test_simple_dp
	 */
	public function test_simple( $expected, $address ) {

		$us   = new ITE_Simple_Zone( array( 'state' => 'NY' ) );
		$ca   = new ITE_Simple_Zone( array( 'state' => 'ON' ) );
		$zone = new ITE_Multidimensional_Zone( array(
			'US' => $us,
			'CA' => $ca,
		) );

		$this->assertEquals( $expected, $zone->contains( $address ) );
	}

	public function _test_simple_dp() {
		return array(
			array(
				true,
				new ITE_In_Memory_Address( array(
					'country' => 'US',
					'state'   => 'NY',
					'zip'     => '10021'
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'US',
					'state'   => 'NJ',
				) )
			),
			array(
				true,
				new ITE_In_Memory_Address( array(
					'country' => 'CA',
					'state'   => 'ON',
					'zip'     => '10021'
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'CA',
					'state'   => 'AB',
					'zip'     => '10021'
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'MX',
					'state'   => 'NY',
				) )
			),
		);
	}

	/**
	 * @dataProvider _test_multi_dp
	 */
	public function test_multi( $expected, $address ) {
		$zone = new ITE_Multidimensional_Zone( array(
			'US' => new ITE_Multidimensional_Zone( array(
				'AL' => new ITE_Simple_Zone( array( 'zip' => array( '35006', '35007' ) ) ),
				'AK' => new ITE_Simple_Zone( array( 'zip' => array( '99501', '99502' ) ) ),
				'AZ' => new ITE_Simple_Zone( array( 'zip' => array( '85001', '85002' ) ) ),
			) ),
			'CA' => new ITE_Multidimensional_Zone( array(
				'ON' => new ITE_Simple_Zone( array( 'zip' => array( '12345', '12346' ) ) ),
				'AB' => new ITE_Simple_Zone( array( 'zip' => array( '54321', '54320' ) ) ),
			) ),
			'MX' => new ITE_Simple_Zone( array( 'state' => 'AGU' ) ),
		) );

		$this->assertEquals( $expected, $zone->contains( $address ) );
	}

	public function _test_multi_dp() {
		return array(
			array(
				true,
				new ITE_In_Memory_Address( array(
					'country' => 'US',
					'state'   => 'AK',
					'zip'     => '99501'
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'US',
					'state'   => 'AK',
					'zip'     => '35432',
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'US',
					'state'   => 'NY',
					'zip'     => '35432',
				) )
			),
			array(
				true,
				new ITE_In_Memory_Address( array(
					'country' => 'CA',
					'state'   => 'ON',
					'zip'     => '12345'
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'CA',
					'state'   => 'NY',
					'zip'     => '54321'
				) )
			),
			array(
				true,
				new ITE_In_Memory_Address( array(
					'country' => 'MX',
					'state'   => 'AGU',
				) )
			),
			array(
				false,
				new ITE_In_Memory_Address( array(
					'country' => 'MX',
					'state'   => 'BCN',
				) )
			),
		);
	}
}