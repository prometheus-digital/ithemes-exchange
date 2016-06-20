<?php

$cart      = it_exchange_get_current_cart();
$taxes     = $cart->get_items( 'tax', true );
$segmented = array();

foreach ( $taxes as $item ) {

	$key = get_class( $item ) . $item->get_name();

	if ( isset( $segmented[ $key ] ) ) {
		$segmented[ $key ]->add( $item );
	} else {
		$segmented[ $key ] = new ITE_Line_Item_Collection( array( $item ), $cart->get_repository() );
	}
}

?>
<?php do_action( 'it_exchange_content_checkout_before_totals_taxes_element' ); ?>

<?php foreach ( $segmented as $segment ) : ?>
	<div class="it-exchange-checkout-totals-title it-exchange-table-column">
		<?php do_action( 'it_exchange_content_checkout_begin_totals_taxes_element_label' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php echo $segment->first()->get_name(); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_end_totals_taxes_element_label' ); ?>
	</div>
	<div class="it-exchange-checkout-totals-amount it-exchange-table-column">
		<?php do_action( 'it_exchange_content_checkout_begin_totals_taxes_element_value' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php echo it_exchange_format_price( $segment->total() ); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_end_totals_taxes_element_value' ); ?>
	</div>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_checkout_after_totals_taxes_element' ); ?>
