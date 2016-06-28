<?php

$cart = it_exchange_get_current_cart();
/** @var ITE_Line_Item_Collection[] $segmented */
$segmented = array();

foreach ( $cart->get_items( 'tax', true ) as $item ) {

	$key = get_class( $item ) . $item->get_name();

	if ( isset( $segmented[ $key ] ) ) {
		$segmented[ $key ]->add( $item );
	} else {
		$segmented[ $key ] = new ITE_Line_Item_Collection( array( $item ), $cart->get_repository() );
	}
}

?>

<?php do_action( 'it_exchange_super_widget_checkout_before_taxes_element' ); ?>
<?php foreach ( $segmented as $segment ): ?>
	<div class="cart-taxes cart-totals-row">
		<?php do_action( 'it_exchange_super_widget_checkout_begin_taxes_element' ); ?>
		<?php printf( __( '%s: %s', 'it-l10n-ithemes-exchange' ), $segment->first()->get_name(), it_exchange_format_price( $segment->total() ) ); ?>
		<?php do_action( 'it_exchange_super_widget_checkout_end_taxes_element' ); ?>
	</div>
<?php endforeach; ?>
<?php do_action( 'it_exchange_super_widget_checkout_after_taxes_element' ); ?>
