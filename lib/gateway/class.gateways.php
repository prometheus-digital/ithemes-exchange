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
					<h1><?php echo $gateway->get_name(); ?></h1>
					<?php $gateway->get_settings_form()->print_form(); ?>
				</div>
				<?php

			};
		}

		if ( $fields = $gateway->get_settings_fields() ) {
			$defaults = array();

			foreach ( $gateway->get_settings_fields() as $field ) {
				if ( $field['type'] !== 'html' ) {
					$defaults[ $field['slug'] ] = isset( $field['default'] ) ? $field['default'] : null;
				}
			}

			add_filter( "it_storage_get_defaults_exchange_{$gateway->get_settings_name()}", function ( $values ) use ( $defaults ) {
				return ITUtility::merge_defaults( $values, $defaults );
			} );
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

		if ( $statuses = $gateway->get_statuses() ) {
			add_filter( "it_exchange_get_status_options_for_{$gateway->get_slug()}_transaction", function () use ( $statuses ) {

				$selectable = array();

				foreach ( $statuses as $status => $opts ) {
					if ( ! empty( $opts['selectable'] ) ) {
						$selectable[ $status ] = $opts['label'];
					}
				}

				return $selectable;
			} );

			add_filter( "it_exchange_{$gateway->get_slug()}_transaction_is_cleared_for_delivery",
				function ( $cleared, $transaction ) use ( $statuses ) {

					if ( ! $transaction ) {
						return $cleared;
					}

					$status = $transaction->get_status();

					if ( ! isset( $statuses[ $status ] ) ) {
						return $cleared;
					}

					$status_opts = $statuses[ $status ];

					if ( ! isset( $status_opts['cleared'] ) ) {
						return $cleared;
					}

					return (bool) $status_opts['cleared'];
				}, 9, 2 );
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

	/**
	 * Get all gateways that can handle a certain request.
	 *
	 * @since 1.36.0
	 *
	 * @param string $request_name
	 *
	 * @return \ITE_Gateway[]
	 */
	public static function handles( $request_name ) {
		return array_filter( static::all(), function ( ITE_Gateway $gateway ) use ( $request_name ) {
			return $gateway->can_handle( $request_name );
		} );
	}
}