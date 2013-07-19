<?php do_action( 'it_exchange_super_widget_checkout_item_before_price' ); ?>
<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
	<?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart', 'subtotal' ); ?>
 <?php else : ?>
	 <?php it_exchange( 'cart-item', 'price' ); ?>
<?php endif;  ?>
<?php do_action( 'it_exchange_super_widget_checkout_item_after_price' ); ?>
