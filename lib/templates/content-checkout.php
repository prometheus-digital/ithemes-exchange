<?php
/**
 * This is the default template part for the checkout page
 * @package IT_Exchange
 * @since 0.4.0
*/
?>
<?php it_exchange_get_template_part( 'messages' ); ?>

<div class="it-exchange-order-summary">
	<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
		<div class="it-exchange-cart-items">
			<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
				<div class="cart-item-title"><?php it_exchange( 'cart-item', 'title' ) ?></div>
				<div class="cart-item-quantity"><?php it_exchange( 'cart-item', 'quantity', 'format=var_value' ) ?></div>
				<div class="cart-item-subtotal"><?php it_exchange( 'cart-item', 'subtotal' ); ?></div>
			<?php endwhile; ?>
		</div>	
		<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) ) : ?> 
			<div class='it-exchange-coupons'>
				<?php if ( it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
				<?php _e( 'Coupon(s):', 'LION' ); ?>
					<ul class="applied-coupons">
						<?php while( it_exchange( 'coupons', 'applied', 'type=cart' ) ) : ?>
							<li class='coupon'>
								<?php it_exchange( 'coupons', 'code' ); ?>:&nbsp;<?php it_exchange( 'coupons', 'discount' ); ?>
							</li>
						<?php endwhile; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php echo __( 'Order Total', 'LION' ) . ': '; it_exchange( 'cart', 'total' ); ?>

</div>

<div class="it-exchange-payment-methods">
	<?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
		<p><?php _e( 'No Payment add-ons enabled.', 'LION' ); ?></p>
	<?php else : ?>
		<?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
			<?php it_exchange( 'transaction-method', 'make-payment' ); ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>
<?php it_exchange( 'checkout', 'cancel' ); ?>
