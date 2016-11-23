<?php
/**
 * Test the auto link styler middleware.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Middleware_Style_Links
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Middleware_Style_Links extends IT_Exchange_UnitTestCase {

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		add_filter( 'it_exchange_theme_api_email_body_highlight_color', function () {
			return '#FFFFFF';
		} );
	}

	/**
	 * @dataProvider _dataProvider
	 *
	 * @param $original
	 * @param $replaced
	 */
	public function test( $original, $replaced ) {

		$sendable = $this->getMockForAbstractClass( 'IT_Exchange_Sendable' );
		$mutable  = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$mutable->override_body( $original );

		$style = new IT_Exchange_Email_Middleware_Style_Links();
		$style->handle( $mutable );

		$this->assertEquals( $replaced, $mutable->get_body() );
	}

	public function _dataProvider() {
		return array(
			array('<a href="">Link</a>', '<a href="" style="color: #FFFFFF;">Link</a>'),
			array('<a href="" style="text-decoration: none;">Link</a>', '<a href="" style="color: #FFFFFF;text-decoration: none;">Link</a>'),
			array('<a href="" style="color: orange;">Link</a>', '<a href="" style="color: orange;">Link</a>'),
			array("<a href=\"\" style='text-decoration: none;'>Link</a>", '<a href="" style="color: #FFFFFF;text-decoration: none;">Link</a>'),
			array("<a href=\"\" style='color: orange;'>Link</a>", "<a href=\"\" style='color: orange;'>Link</a>"),
			array('<a href="" style=" color: orange;">Link</a>', '<a href="" style=" color: orange;">Link</a>'),
			array('<a href="" style="color : orange;">Link</a>', '<a href="" style="color : orange;">Link</a>'),
			array('<a href="" style=" color : orange;">Link</a>', '<a href="" style=" color : orange;">Link</a>'),
			array('<a href="" style="	color: orange;">Link</a>', '<a href="" style="	color: orange;">Link</a>'),
			array('<a href="" style="background-color: orange;">Link</a>', '<a href="" style="color: #FFFFFF;background-color: orange;">Link</a>'),
		);
	}
}
