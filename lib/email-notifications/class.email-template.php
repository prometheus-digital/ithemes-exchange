<?php
/**
 * Contains the email template class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Template
 */
class IT_Exchange_Email_Template {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * IT_Exchange_Email_Template constructor.
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Get the template HTML.
	 *
	 * @since 1.36
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function get_html( $context ) {

		$GLOBALS['it_exchange']['email_context'] = $context;

		ob_start();

		it_exchange_get_template_part( 'email', $this->name );

		return ob_get_clean();
	}
}
