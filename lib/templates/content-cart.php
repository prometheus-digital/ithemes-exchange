<?php
/**
 * This file contains the default template part for the cart's content
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
	<?php it_exchange( 'cart', 'form-open' ); ?>
		<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
			Remove from cart field name: <?php it_exchange( 'cart-item', 'remove', 'format=field-name' ) ?><br />
			Title: <?php it_exchange( 'cart-item', 'title' ) ?><br />
			Quantity as field: <?php it_exchange( 'cart-item', 'quantity', 'format=form-field' ) ?><br />
			Price: <?php it_exchange( 'cart-item', 'price' ) ?><br />
			<hr />
		<?php endwhile; ?>

		Sub-total: <?php it_exchange( 'cart', 'subtotal' ); ?><br />
		Total: <?php it_exchange( 'cart', 'total' ); ?><br />
		<hr />
		<?php it_exchange( 'cart', 'update' ); ?><br/>
		<?php it_exchange( 'cart', 'checkout' ); ?><br/>
		<?php it_exchange( 'cart', 'empty' ); ?><br/>

	<?php it_exchange( 'cart', 'form-close' ); ?>
<?php else: ?>
	<p><?php _e( 'There are no items in your cart', 'LION' ); ?></p>
<?php endif; ?>
