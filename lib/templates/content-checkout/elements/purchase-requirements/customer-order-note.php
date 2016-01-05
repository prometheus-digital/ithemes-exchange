<?php
/**
 * Checkout customer order note purchase requirement.
 *
 * @since   1.34
 * @license GPLv2
 */
?>

<div class="it-exchange-customer-order-note">

	<h3><?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?></h3>

	<div class="it-exchange-customer-order-notes-summary">
		<p><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></p>

		<a href="javascript:" class="it-exchange-edit-customer-order-notes">
			<?php _e( 'Edit Order Notes', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</div>

	<form method="POST" class="it-exchange-customer-order-notes-form it-exchange-hidden" action="<?php echo it_exchange_get_page_url( 'checkout' ); ?>">
		<label for="it-exchange-customer-order-note" class="screen-reader-text it-exchange-hidden">
			<?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?>
		</label>
		<textarea id="it-exchange-customer-order-note" name="it-exchange-customer-order-note"><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></textarea>

		<div class="it-exchange-customer-order-note-actions">
			<a href="javascript:" class="it-exchange-customer-order-note-cancel"><?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?></a>
			<input type="submit" name="it-exchange-edit-customer-order-note" class="it-exchange-submit" value="<?php _e( 'Submit', 'it-l10n-ithemes-exchange' ); ?>">
		</div>
	</form>
</div>