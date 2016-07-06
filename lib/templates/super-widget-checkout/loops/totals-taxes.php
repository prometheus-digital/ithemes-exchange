<?php

$cart      = it_exchange_get_current_cart();
$segmented = $cart->get_items( 'tax', true )->segment( function ( ITE_Line_Item $item ) {
	return get_class( $item ) . $item->get_name();
} );
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
