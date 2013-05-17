<?php
/**
 * This file contains the default template part for the cart's content
 * @since 0.4.0
 * @package IT_Exchange
*/
?>

<?php if ( it_exchange( 'messages', 'has-errors' ) ) : ?>
	<ul class='notices'>
		<?php while( it_exchange( 'messages', 'errors' ) ) : ?>
			<li><?php it_exchange( 'messages', 'error' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>
<?php if ( it_exchange( 'messages', 'has-notices' ) ) : ?>
	<ul class='notices'>
		<?php while( it_exchange( 'messages', 'notices' ) ) : ?>
			<li><?php it_exchange( 'messages', 'notice' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>

<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
	<?php it_exchange( 'cart', 'form-open' ); ?>
		<div class="it-exchange-cart-items">
			<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
				<div class="cart-item-title"><?php it_exchange( 'cart-item', 'title' ) ?></div>
				<!-- Justin, for the quantity - you will have a field returned if they're allowed to up the quantity. A number '1' returned if they can't -->
				<div class="cart-item-quantity"><?php it_exchange( 'cart-item', 'quantity', 'format=form-field' ) ?></div>
				<div class="cart-item-subtotal"><?php it_exchange( 'cart-item', 'subtotal' ); ?></div>
				<div class="cart-item-remove"><?php it_exchange( 'cart-item', 'remove' ) ?></div>
			<?php endwhile; ?>
		</div>
        
		<hr />
		Sub-total: <?php it_exchange( 'cart', 'subtotal' ); ?>
		<hr />
		<?php 
		// Do we have a coupon add-on enabled that supports cart coupons
		if ( it_exchange( 'coupons', 'supported', 'type=cart' ) ) { 

			// Does the current cart have any coupons applied to it?
			if ( it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) {
				_e( 'Applied Coupon(s):', 'LION' );
				echo '<ul class="applied-coupons">';

				// Loop through coupons currently applied to this cart
				while( it_exchange( 'coupons', 'applied', 'type=cart' ) ) {
					?>
					<li class='coupon'>
						<?php it_exchange( 'coupons', 'code' ); ?>:&nbsp;<?php it_exchange( 'coupons', 'discount' ); ?>&nbsp;<?php it_exchange( 'coupons', 'remove', 'type=cart' ); ?>
					</li>
					<?php
				}
				echo '</ul>';
			}

			// Add new coupon if accepting them
			if ( it_exchange( 'coupons', 'accepting', 'type=cart' ) ) {  
				_e( 'Coupon Code?', 'LION' );
				it_exchange( 'coupons', 'apply', 'type=cart' );
				it_exchange( 'cart', 'update', 'label=' . __( 'Apply Coupon', 'LION' ) );
			}
		}
		?>
		<hr />
		Total: <?php it_exchange( 'cart', 'total' ); ?><br />
		<hr />
		<?php it_exchange( 'cart', 'update' ); ?><br/>
		<?php it_exchange( 'cart', 'checkout' ); ?><br/>
		<?php it_exchange( 'cart', 'empty' ); ?><br/>

	<?php it_exchange( 'cart', 'form-close' ); ?>
<?php else: ?>
	<p><?php _e( 'There are no items in your cart', 'LION' ); ?></p>
<?php endif; ?>
