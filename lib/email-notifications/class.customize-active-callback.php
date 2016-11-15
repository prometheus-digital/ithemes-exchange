<?php
/**
 * Contains the customizer active callback class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Customize_Active_Callback
 */
class IT_Exchange_Email_Customize_Active_Callback {

	/**
	 * @var string
	 */
	private $required_setting;

	/**
	 * IT_Exchange_Email_Customize_Active_Callback constructor.
	 *
	 * @param string $required_setting
	 */
	public function __construct( $required_setting ) {
		$this->required_setting = $required_setting;
	}

	/**
	 * Active callback.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function active() {
		return (bool) trim( IT_Exchange_Email_Customizer::get_setting( $this->required_setting ) );
	}
}
