<?php
$transaction = it_exchange_get_transaction( $GLOBALS['it_exchange']['transaction'] );
$taxes       = $transaction->get_items( 'tax', true );
$segmented   = $taxes->segment( function ( ITE_Line_Item $item ) {
	return get_class( $item ) . $item->get_name();
} );
?>
<?php foreach ( $segmented as $segment ): ?>
	<?php do_action( 'it_exchange_content_confirmation_before_totals_taxes_simple_element' ); ?>
	<div class="it-exchange-confirmation-totals-title it-exchange-table-column">
		<?php do_action( 'it_exchange_content_confirmation_begin_totals_taxes_simple_element_label' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php echo $segment->first()->get_name(); ?>
		</div>
		<?php do_action( 'it_exchange_content_confirmation_end_totals_taxes_simple_element_label' ); ?>
	</div>
	<div class="it-exchange-confirmation-totals-amount it-exchange-table-column">
		<?php do_action( 'it_exchange_content_confirmation_begin_totals_taxes_simple_element_value' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php echo $segment->total(); ?>
		</div>
		<?php do_action( 'it_exchange_content_confirmation_end_totals_taxes_simple_element_value' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_confirmation_after_totals_taxes_simple_element' ); ?>
<?php endforeach; ?>
