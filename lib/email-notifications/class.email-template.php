<?php
/**
 * Contains the email template class.
 *
 * @since   2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * Get the template name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Globalize the context for the theme API.
	 *
	 * @since 2.0.0
	 *
	 * @param array $context
	 */
	protected function globalize_context( $context ) {

		$GLOBALS['it_exchange']['email_context'] = $context;

		if ( ! empty( $context['transaction'] ) ) {
			if ( is_numeric( $context['transaction'] ) ) {
				$GLOBALS['it_exchange']['transaction'] = it_exchange_get_transaction( $context['transaction'] );
			} else {
				$GLOBALS['it_exchange']['transaction'] = $context['transaction'];
			}
		}

		if ( ! empty( $context['transaction-activity'] ) ) {
			if ( is_numeric( $context['transaction-activity'] ) ) {
				$GLOBALS['it_exchange']['transaction-activity'] = it_exchange_get_txn_activity( $context['transaction-activity'] );
			} else {
				$GLOBALS['it_exchange']['transaction-activity'] = $context['transaction-activity'];
			}
		}

		/**
		 * Fires when additional context should be globalized.
		 *
		 * This is mainly to be provided to the Theme API.
		 *
		 * @since 2.0.0
		 *
		 * @param array $context
		 */
		do_action( 'it_exchange_email_template_globalize_context', $context );
	}
}
