<?php
/**
 * Default template part for when no transactions are found on the Transaction Confirmation page
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_confirmation_template_part_no_transactions_found_top' ); ?>
<p class="error"><?php _e( 'No transactions found.', 'LION' ); ?></p>
<?php do_action( 'it_exchange_confrimation_template_part_no_transactions_found_bottom' ); ?>
