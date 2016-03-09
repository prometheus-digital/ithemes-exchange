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

		$this->globalize_context( $context );

		ob_start();

		it_exchange_get_template_part( 'emails/email', $this->name );

		return ob_get_clean();
	}

	/**
	 * Get the email template file.
	 *
	 * @since 1.36
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function get_file( $context ) {

		$this->globalize_context( $context );

		return it_exchange_get_template_part( 'emails/email', $this->name, false );
	}

	/**
	 * Globalize the context for the theme API.
	 *
	 * @since 1.36
	 *
	 * @param array $context
	 */
	protected function globalize_context( $context ) {

		$GLOBALS['it_exchange']['email_context'] = $context;

		if ( ! empty( $context['transaction'] ) ) {
			$GLOBALS['it_exchange']['transaction'] = it_exchange_get_transaction( $context['transaction'] );
		}
	}
}
