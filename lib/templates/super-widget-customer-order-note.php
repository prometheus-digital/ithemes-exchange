<?php
/**
 * Customer order note template.
 *
 * @since   1.34
 * @license GPLv2
 */
?>

<?php do_action( 'it_exchange_super_widget_customer_order_note_before_wrap' ); ?>
	<div class="customer-order-note it-exchange-sw-processing it-exchange-sw-processing-customer-order-note">
		<?php do_action( 'it_exchange_super_widget_customer_order_note_begin_wrap' ); ?>
		<form class="it-exchange-sw-customer-order-note-form">
			<label for="customer-order-note"><?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?></label>
			<textarea id="customer-order-note"><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></textarea>

			<p class="description">
				<?php _e( 'Notes about your order. Such as delivery instructions or customizations.', 'it-l10n-ithemes-exchange' ); ?>
			</p>

			<div class="it-exchange-customer-order-note-actions">
				<a href="javascript:" class="it-exchange-customer-order-note-cancel"><?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?></a>
				<input type="submit" class="it-exchange-submit" value="<?php _e( 'Submit', 'it-l10n-ithemes-exchange' ); ?>">
			</div>
		</form>
		<?php do_action( 'it_exchange_super_widget_customer_order_note_end_wrap' ); ?>
	</div>
<?php do_action( 'it_exchange_super_widget_customer_order_note_after_wrap' ); ?>