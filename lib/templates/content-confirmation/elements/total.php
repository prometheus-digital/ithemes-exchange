<?php
/**
 * This is the default template part for the
 * total in the transaction meta loop of the
 * transaction Confirmation template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/content-confirmation/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_confirmation_before_transaction_total_element' ); ?>
<div class="it-exchange-transaction-total">
	<p><?php _e( 'Total:', 'it-l10n-ithemes-exchange' ); ?> <?php it_exchange( 'transaction', 'total' ); ?></p>
</div>
<?php do_action( 'it_exchange_confirmation_after_transaction_total_element' ); ?>
