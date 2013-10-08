<?php
/**
 * This is the default template for the Subtotal
 * detail in the totals loop of the content-confirmation
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

<?php do_action( 'it_exchange_content_confirmation_before_totals_subtotal_element' ); ?>
<div class="it-exchange-confirmation-totals-title it-exchange-table-column">
	<?php do_action( 'it_exchange_content_confirmation_begin_totals_subtotal_element_label' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php _e( 'Subtotal', 'LION' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_confirmation_end_totals_subtotal_element_label' ); ?>
</div>
<div class="it-exchange-confirmation-totals-amount it-exchange-table-column">
	<?php do_action( 'it_exchange_content_confirmation_begin_totals_subtotal_element_value' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'transaction', 'subtotal' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_confirmation_end_totals_subtotal_element_value' ); ?>
</div>
<?php do_action( 'it_exchange_content_confirmation_after_totals_subtotal_element' ); ?>
