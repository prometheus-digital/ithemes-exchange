<?php
/**
 * Test the Simple Zone class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Simple_Zone_Test
 *
 * @group location
 */
class IT_Exchange_Simple_Zone_Test extends IT_Exchange_UnitTestCase {

	public function test_single_level() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US'
		) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY'
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'CA',
			'state'   => 'ON'
		) ) ) );
	}

	public function test_multi_level() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10021',
		) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10021',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NJ',
			'zip'     => '10021',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'CA',
			'state'   => 'ON',
			'zip'     => '10021',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10019',
		) ) ) );
	}

	public function test_upper_bound() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10021',
		) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10021',
		) ), 'country' ) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NJ',
			'zip'     => '10021',
		) ), 'country' ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NJ',
			'zip'     => '10021',
		) ), 'state' ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'CA',
			'state'   => 'ON',
			'zip'     => '10021',
		) ), 'country' ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10019',
		) ) ), 'city' );
	}

	public function test_multiple_per_level() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => array( '10019', '10021', '10023', '10025', '10027' ),
		) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10021',
		) ) ) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10027',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10020',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NJ',
			'zip'     => '10021',
		) ) ) );
	}

	public function test_skipped_level() {
		$zone = new ITE_Simple_Zone( array(
			'state' => 'NY'
		) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'state'   => 'NY',
		) ) ) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NY',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'NJ',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'state'   => 'NJ',
		) ) ) );
	}

	public function test_gap_level() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'zip'     => '02861',
		) );

		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'MA',
			'zip'     => '02861',
		) ) ) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'RI',
			'zip'     => '02861',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'RI',
			'zip'     => '10021',
		) ) ) );
	}

	public function test_wild() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => ITE_Location::WILD,
			'zip'     => '02861',
		) );

		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'MA',
			'zip'     => '02861',
		) ) ) );
		$this->assertTrue( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'RI',
			'zip'     => '02861',
		) ) ) );
		$this->assertFalse( $zone->contains( new ITE_In_Memory_Address( array(
			'country' => 'US',
			'state'   => 'RI',
			'zip'     => '10021',
		) ) ) );
	}

	public function test_get_precision() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => 'NY',
			'zip'     => '10019',
		) );
		$this->assertEquals( 'zip', $zone->get_precision() );
	}

	public function test_get_precision_with_wild() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => ITE_Location::WILD,
		) );
		$this->assertEquals( 'country', $zone->get_precision() );
	}

	public function test_get_precision_with_wild_and_next() {
		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => ITE_Location::WILD,
			'zip'     => '02861',
		) );
		$this->assertEquals( 'zip', $zone->get_precision() );

		$zone = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => ITE_Location::WILD,
			'zip'     => ITE_Location::WILD,
			'city'    => 'New York'
		) );
		$this->assertEquals( 'city', $zone->get_precision() );
	}

	public function test_get_precision_without_country() {
		$zone = new ITE_Simple_Zone( array( 'state' => 'NY' ) );
		$this->assertEquals( 'state', $zone->get_precision() );
	}

	public function test_mask() {
		$zone     = new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => 'NY',
		) );
		$location = new ITE_In_Memory_Address( array(
			'country'  => 'CA',
			'state'    => 'ON',
			'zip'      => '10021',
			'address1' => '123 Main Street',
		) );
		$masked   = $zone->mask( $location );

		$this->assertNotSame( $location, $masked );
		$this->assertEquals( 'CA', $masked['country'] );
		$this->assertEquals( 'ON', $masked['state'] );
		$this->assertEquals( ITE_Location::WILD, $masked['zip'] );
		$this->assertEquals( ITE_Location::WILD, $masked['address1'] );
	}
}