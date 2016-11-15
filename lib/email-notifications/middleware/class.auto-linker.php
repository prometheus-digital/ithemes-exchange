<?php
/**
 * Contains middleware to autolink urls.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use Windwalker\Dom\HtmlElement;

/**
 * Class IT_Exchange_Email_Middleware_Auto_Linker
 */
class IT_Exchange_Email_Middleware_Auto_Linker implements IT_Exchange_Email_Middleware {

	/**
	 * @var \Asika\Autolink\Autolink
	 */
	private $autolink;

	/**
	 * IT_Exchange_Email_Middleware_Auto_Linker constructor.
	 *
	 * @param \Asika\Autolink\Autolink $autolink
	 */
	public function __construct( \Asika\Autolink\Autolink $autolink ) {
		$this->autolink = $autolink;
		$this->autolink->setLinkBuilder( function ( $url, $attributes ) {

			$url = strip_tags( $url );

			return (string) new HtmlElement( 'a', htmlspecialchars( $url ), $attributes );
		} );

		$self = $this;

		add_action( 'it_exchange_email_notifications_register_middleware',
			function ( IT_Exchange_Email_Middleware_Handler $middleware ) use ( $self ) {
				$middleware->before( $self, 'style-links', 'auto-linker' );
			} );
	}

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		$sendable->override_body( $this->autolink->convertEmail( $this->autolink->convert( $sendable->get_body() ) ) );

		return true;
	}
}

new IT_Exchange_Email_Middleware_Auto_Linker( new \Asika\Autolink\Autolink() );
