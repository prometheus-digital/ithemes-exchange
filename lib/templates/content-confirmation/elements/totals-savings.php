<?php
/**
 * This is the default template for the Savings
 * element in the totals loop of the content-confirmation
 * template part.
 *
 * @since 1.4.0
 * @version 1.0.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-confirmation/elements/
 * directory located in your theme.
*/
?>

<?php if ( it_exchange( 'transaction', 'has-savings-total' ) ) { ?>
<?php do_action( 'it_exchange_content_confirmation_before_totals_savings_element' ); ?>
<div class="it-exchange-cart-totals-title it-exchange-table-column">
    <?php do_action( 'it_exchange_content_confirmation_begin_totals_savings_element_label' ); ?>
    <div class="it-exchange-table-column-inner">
        <?php _e( 'Savings', 'LION' ); ?>
    </div>
    <?php do_action( 'it_exchange_content_confirmation_end_totals_savings_element_label' ); ?>
</div>
<div class="it-exchange-cart-totals-amount it-exchange-table-column">
    <?php do_action( 'it_exchange_content_confirmation_begin_totals_savings_element_value' ); ?>
    <div class="it-exchange-table-column-inner">
        <?php it_exchange( 'transaction', 'savings-total' ); ?>
    </div>
    <?php do_action( 'it_exchange_content_confirmation_end_totals_savings_element_value' ); ?>
</div>
<?php do_action( 'it_exchange_content_confirmation_after_totals_savings_element' ); ?>
<?php } ?>