<?php
/**
 * Gateways Settings Page.
 *
 * @since   2.0.0
 * @license GPLv2
 */
/**
 * @var $form   ITForm
 * @var $is_ssl bool
 * @var $gateways ITE_Gateway[]
 */
?>
<div class="wrap">
	<?php do_action( 'it_exchange_general_settings_gateways_page_top' ); ?>

	<h1 class="screen-reader-text">
		<?php _e( 'Gateway Settings', 'it-l10n-ithemes-exchange' ); ?>
	</h1>

	<?php $this->print_general_settings_tabs(); ?>

	<div class="it-exchange-gateways-settings">
		<?php $form->start_form( array( 'action' => $_SERVER['REQUEST_URI'] ) ); ?>
		<?php do_action( 'it_exchange_general_settings_gateways_form_top', $form ); ?>

		<table class="widefat striped gateways">
			<thead>
			<tr>
				<th><?php _e( 'Gateway', 'it-l10n-ithemes-exchange' ); ?></th>
				<th><?php _e( 'Accepting Payments', 'it-l10n-ithemes-exchange' ); ?></th>
				<th><?php _e( 'SSL', 'it-l10n-ithemes-exchange' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $gateways as $gateway ): ?>
				<tr>
					<td><?php echo $gateway->get_name(); ?></td>
					<td>
						<?php $form->set_input_group( 'accepting' ); ?>
						<label class="screen-reader-text" for="accepting-<?php echo $gateway->get_slug(); ?>">
							<?php _e( 'Is this gateway accepting new payments.', 'it-l10n-ithemes-exchange' ); ?>
						</label>
						<?php $form->add_check_box( $gateway->get_slug() ); ?>
					</td>
					<td>
						<?php if ( $gateway->get_ssl_mode() === ITE_Gateway::SSL_REQUIRED && $is_ssl ) : ?>
							<span class="dashicons dashicons-lock it-exchange-dashicons-tip" style="color: #A0B046;"
							      title="<?php esc_attr_e( 'SSL is required.', 'it-l10n-ithemes-exchange' ); ?>">
							</span>
						<?php elseif ( $gateway->get_ssl_mode() === ITE_Gateway::SSL_REQUIRED && ! $is_ssl ): ?>
							<span class="dashicons dashicons-unlock it-exchange-dashicons-tip" style="color: #F24E4E;"
							      title="<?php esc_attr_e( 'SSL is required.', 'it-l10n-ithemes-exchange' ); ?>">
							</span>
						<?php elseif ( $gateway->get_ssl_mode() === ITE_Gateway::SSL_SUGGESTED && $is_ssl ): ?>
							<span class="dashicons dashicons-lock it-exchange-dashicons-tip" style="color: #A0B046"
							      title="<?php esc_attr_e( 'SSL is suggested.', 'it-l10n-ithemes-exchange' ); ?>">
							</span>
						<?php elseif ( $gateway->get_ssl_mode() === ITE_Gateway::SSL_SUGGESTED && ! $is_ssl ): ?>
							<span class="dashicons dashicons-lock it-exchange-dashicons-tip" style="color: #F2C94E"
							      title="<?php esc_attr_e( 'SSL is suggested.', 'it-l10n-ithemes-exchange' ); ?>">
							</span>
						<?php elseif ( $gateway->get_ssl_mode() === ITE_Gateway::SSL_NONE ): ?>
							-
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<th><?php _e( 'Gateway', 'it-l10n-ithemes-exchange' ); ?></th>
				<th><?php _e( 'Accepting Payments', 'it-l10n-ithemes-exchange' ); ?></th>
				<th><?php _e( 'SSL', 'it-l10n-ithemes-exchange' ); ?></th>
			</tr>
			</tfoot>
		</table>

		<p class="submit">
			<input type="submit" value="<?php _e( 'Save Changes', 'it-l10n-ithemes-exchange' ); ?>"
			       class="button button-primary"/>
		</p>

		<?php do_action( 'it_exchange_general_settings_gateways_form_bottom', $form ); ?>
		<?php wp_nonce_field( 'exchange-gateway-settings' ); ?>
		<?php $form->end_form(); ?>
	</div>
	<?php do_action( 'it_exchange_general_settings_gateways_page_bottom' ); ?>
</div>

<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		$( '.it-exchange-dashicons-tip' ).tooltip();
	} );
</script>
