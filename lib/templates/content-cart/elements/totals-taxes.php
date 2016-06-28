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
<?php do_action( 'it_exchange_content_cart_before_totals_taxes_element' ); ?>

<?php foreach ( $segmented as $segment ) : ?>
	<div class="it-exchange-cart-totals-title it-exchange-table-column">
		<?php do_action( 'it_exchange_content_cart_begin_totals_taxes_element_label' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php do_action( 'it_exchange_content_cart_begin_totals_taxes_inner_element_label' ); ?>
			<?php echo $segment->first()->get_name(); ?>
			<?php do_action( 'it_exchange_content_cart_end_totals_taxes_inner_element_label' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_cart_end_totals_taxes_element_label' ); ?>
	</div>
	<div class="it-exchange-cart-totals-amount it-exchange-table-column">
		<?php do_action( 'it_exchange_content_cart_begin_totals_taxes_element_value' ); ?>
		<div class="it-exchange-table-column-inner">
			<?php do_action( 'it_exchange_content_cart_begin_totals_taxes_inner_element_value' ); ?>
			<?php echo it_exchange_format_price( $segment->total() ); ?>
			<?php do_action( 'it_exchange_content_cart_end_totals_taxes_inner_element_value' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_cart_end_totals_taxes_element_value' ); ?>
	</div>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_cart_after_totals_taxes_element' ); ?>
