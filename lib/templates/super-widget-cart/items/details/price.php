<?php do_action( 'it_exchange_super_widget_cart_item_before_price' ); ?>
<?php if ( it_exchange( 'cart-item', 'has-purchase-quantity' ) ) : ?>
	<?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity' ); ?>  
<?php else : ?>
	<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
		<?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart-item', 'subtotal' ); ?>
	<?php else : ?>
		<?php it_exchange( 'cart-item', 'price' ); ?>
	<?php endif; ?>
<?php endif; ?>
<?php do_action( 'it_exchange_super_widget_cart_item_after_price' ); ?>
