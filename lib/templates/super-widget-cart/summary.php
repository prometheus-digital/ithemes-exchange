<?php $count = it_exchange( 'cart', 'get-item-count' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_before_summary' ); ?>
<div class="item-count">
	<?php do_action( 'it_exchange_super_widget_cart_begin_summary' ); ?>
	<?php if ( $count === 1 ) : ?>
		<?php printf( __( 'You have 1 item in your <a href="%s">%s</a>', 'LION' ), it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
	<?php else : ?>
		<?php printf( __( 'You have %s items in your <a href="%s">%s</a>', 'LION' ), $count, it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
	<?php endif; ?>
	<?php do_action( 'it_exchange_super_widget_cart_end_summary' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_after_summary' ); ?>
