<?php
/**
 * Gateways registry.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateways
 */
class ITE_Gateways {

	/** @var ITE_Gateway[] */
	private static $gateways = array();

	/**
	 * Register a gateway.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateway $gateway
	 *
	 * @return bool
	 */
	public static function register( ITE_Gateway $gateway ) {

		if ( static::get( $gateway->get_slug() ) ) {
			return false;
		}

		static::$gateways[ $gateway->get_slug() ] = $gateway;

		if (
			empty( $GLOBALS['it_exchange']['add_ons']['registered'][ $gateway->get_slug() ]['options']['settings-callback'] ) &&
			$gateway->get_settings_form()
		) {
			$GLOBALS['it_exchange']['add_ons']['registered'][ $gateway->get_slug() ]['options']['settings-callback'] = function () use ( $gateway ) {
				?>
				<div class="wrap">
					<h2><?php echo $gateway->get_name(); ?></h2>
					<?php $gateway->get_settings_form()->print_form(); ?>
				</div>
				<?php

			};
		}

		if ( $webhook_param = $gateway->get_webhook_param() ) {
			it_exchange_register_webhook( $gateway->get_slug(), $webhook_param );
		}

		if ( $gateway->can_handle( 'webhook' ) ) {
			add_action( "it_exchange_webhook_{$gateway->get_webhook_param()}", function ( $request ) use ( $gateway ) {
				$factory = new ITE_Gateway_Request_Factory();

				$request = $factory->make( 'webhook', array( 'webhook_data' => $request ) );

				/** @var WP_HTTP_Response $response */
				$response = $gateway->get_handler_for( $request )->handle( $request );

				status_header( $response->get_status() );
			} );
		}

		return true;
	}

	/**
	 * Get a gateway by its slug.
	 *
	 * @since 1.36.0
	 *
	 * @param string $slug
	 *
	 * @return \ITE_Gateway|null
	 */
	public static function get( $slug ) {
		return isset( static::$gateways[ $slug ] ) ? static::$gateways[ $slug ] : null;
	}

	/**
	 * Retrieve all registered gateways.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway[]
	 */
	public static function all() {
		return array_values( static::$gateways );
	}
}