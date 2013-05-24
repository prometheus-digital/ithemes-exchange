<?php
/**
 * This file outputs the cart summary for the superwidget
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
	<?php it_exchange( 'cart', 'form-open' ); ?>
	<div class="it-exchange-cart-subtotal"><?php _e( 'Sub-total:', 'LION' ); ?> <?php it_exchange( 'cart', 'subtotal' ); ?></div>
	<?php 
	// Do we have a coupon add-on enabled that supports cart coupons
	if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'cart', 'focus', 'type=coupon' ) ) { 

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
			it_exchange( 'cart', 'update', 'class=it-exchange-apply-coupon-button&label=' . __( 'Apply Coupon', 'LION' ) );
		}   
		?>
		<?php
	}

	
	if ( it_exchange( 'cart', 'focus', 'type=quantity' ) ) : ?>
		<div class="it-exchange-cart-items">
			<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
				<div class="cart-item-title"><?php it_exchange( 'cart-item', 'title' ) ?></div>
                <!-- Justin, for the quantity - you will have a field returned if they're allowed to up the quantity. A number '1' returned if they can't -->
                <div class="cart-item-quantity"><?php it_exchange( 'cart-item', 'quantity', 'format=form-field' ) ?></div>
				<div class="cart-item-subtotal"><?php it_exchange( 'cart-item', 'subtotal' ); ?></div>
				<?php if ( it_exchange( 'cart', 'supports-multiple-items' ) ) : ?>
					<div class="cart-item-remove"><?php it_exchange( 'cart-item', 'remove' ); ?></div>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
		<div class="it-exchange-cart-subtotal"><?php _e( 'Sub-total:', 'LION' ); ?> <?php it_exchange( 'cart', 'subtotal' ); ?></div>
		<div class="it-exchange-cart-update"><?php it_exchange( 'cart', 'update', 'class=it-exchange-update-quantity-button&label=' . __( 'Update Quantity', 'LION' ) ); ?></div>
	<?php endif; ?>
	<div class="it-exchange-cart-total"><?php _e( 'Total:', 'LION' ); ?> <?php it_exchange( 'cart', 'total' ); ?></div>
	<div class="it-exchange-checkout-link"><?php it_exchange( 'cart', 'checkout' ); ?></div>
	<div class="it-exchnange-cancel"><?php it_exchange( 'cart', 'empty', 'format=link&label=' . __( 'Cancel Purchase', 'LION' ) ); ?></div>
	<?php it_exchange( 'cart', 'form-close' ); ?>
<?php endif; ?>
