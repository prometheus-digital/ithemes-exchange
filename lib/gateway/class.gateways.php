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

		if ( $gateway->is_currency_support_limited() ) {
			add_action( "it_exchange_{$gateway->get_settings_name()}_top", function () use ( $gateway ) {

				$general_settings = it_exchange_get_option( 'settings_general' );

				if ( array_key_exists( $general_settings['default-currency'], $gateway->get_supported_currencies() ) ) {
					return;
				}

				echo '<div class="notice notice-error"><p>';
				printf(
					__( 'You are currently using a currency that is not supported by %1$s. %2$sPlease update your currency settings%3$s.', 'LION' ),
					$gateway->get_name(),
					'<a>' . admin_url( 'admin.php?page=it-exchange-settings' ),
					'</a>'
				);
				echo '</p></div>';
			} );

			add_filter( 'it_exchange_get_currencies', function ( $currencies ) use ( $gateway ) {

				if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
					return $currencies;
				}

				$screen = get_current_screen();

				if ( empty( $screen->base ) ) {
					return $currencies;
				}

				$screens = array(
					'exchange_page_it-exchange-settings',
					'exchange_page_it-exchange-setup'
				);

				if ( ! in_array( $screen->base, $screens, true ) ) {
					return $currencies;
				}

				$supported = $gateway->get_supported_currencies();

				if ( empty( $supported ) ) {
					return $currencies;
				}

				/*var_dump(array_flip( $supported));
				var_dump($currencies);*/

				return array_intersect_key( $currencies, array_flip( $supported ) );
			} );
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

		if ( $wizard = $gateway->get_wizard_settings() ) {
			add_action( "it_exchange_print_{$gateway->get_slug()}_wizard_settings", function ( ITForm $form ) use ( $wizard, $gateway ) {
				$form_values = ITUtility::merge_defaults( ITForm::get_post_data(), $gateway->settings()->all() );

				foreach ( $form_values as $key => $value ) {
					$form->set_option( "{$gateway->get_slug()}-wizard-{$key}", $value );
				}

				foreach ( $wizard as &$setting ) {
					$setting['slug'] = "{$gateway->get_slug()}-wizard-{$setting['slug']}";
				}
				unset( $setting );

				$settings_form = new IT_Exchange_Admin_Settings_Form( array(
					'form'        => $form,
					'form-fields' => $wizard
				) );
				$hide_if_js    = it_exchange_is_addon_enabled( $gateway->get_slug() ) ? '' : 'hide-if-js';
				?>

				<div class="field <?php echo $gateway->get_slug(); ?>-wizard <?php echo $hide_if_js; ?>">
					<h3><?php echo $gateway->get_name(); ?></h3>
					<?php if ( empty( $hide_if_js ) ) { ?>
						<input class="enable-<?php echo $gateway->get_slug(); ?>"
						       type="hidden" name="it-exchange-transaction-methods[]"
						       value="<?php echo $gateway->get_slug(); ?>"
						/>
					<?php } ?>
					<?php $settings_form->print_fields(); ?>
				</div>
				<?php
			} );

			add_action( 'it_exchange_save_wizard_settings', function () use ( $wizard, $gateway ) {

				if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) ) {
					return;
				}

				$settings_form = new IT_Exchange_Admin_Settings_Form( array(
					'form-prefix' => 'it_exchange_settings-wizard-' . $gateway->get_slug(),
					'form-fields' => $wizard
				) );

				$settings   = array();
				$controller = $gateway->settings();

				foreach ( $wizard as $setting ) {

					$key = $setting['slug'];

					if ( isset( $_REQUEST["it_exchange_settings-{$gateway->get_slug()}-wizard-{$key}"] ) ) {
						$settings[ $key ] = $_REQUEST["it_exchange_settings-{$gateway->get_slug()}-wizard-{$key}"];
					}
				}

				$settings_or_error = $settings_form->validate_settings( $settings );

				if ( is_wp_error( $settings_or_error ) ) {
					it_exchange_add_message( 'error', $settings_or_error->get_error_message() );

					return;
				}

				foreach ( $settings_or_error as $key => $value ) {
					$controller->set( $key, $value, false );
				}

				$controller->save();
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
	 * Retrieve all gateways accepting payments.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway[]
	 */
	public static function accepting() {
		return array_filter(
			static::all(),
			function ( $gateway ) { return it_exchange_is_gateway_accepting_payments( $gateway ); }
		);
	}

	/**
	 * Get all gateways except Zero Sum Checkout.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway[]
	 */
	public static function non_zero_sum() {
		return array_filter(
			static::all(),
			function ( $gateway ) { return ! $gateway instanceof ITE_Zero_Sum_Checkout_Gateway; }
		);
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