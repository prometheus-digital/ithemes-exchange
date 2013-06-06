<div class="cart-items-wrapper">
	<!--
		NOTE Still have to workout the multi-item cart markup.
	-->
	<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
		<?php $can_edit_purchase_quantity = it_exchange( 'cart-item', 'supports-purchase-quantity' ); ?>
		<div class="cart-item">
			<div class="title-remove">
				<?php it_exchange( 'cart-item', 'title' ) ?>
				<?php it_exchange( 'cart-item', 'remove' ); ?>
			</div>
			<div class="item-info">
				<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
					 <?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart', 'total' ); ?>
				 <?php else : ?>
					 <?php it_exchange( 'cart-item', 'price' ); ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endwhile; ?>
</div>

<div class="payment-methods-wrapper">
	<?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
		<p><?php _e( 'No payment add-ons enabled.', 'LION' ); ?></p>
	<?php else : ?>
		<?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
			<?php it_exchange( 'transaction-method', 'make-payment' ); ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>

<div class="coupons-quantity-wrapper">
	<?php if ( it_exchange( 'coupons', 'accepting', 'type=cart' ) || it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
		<?php $label = (boolean) it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ? __( 'Coupons', 'LION' ) : __( 'Coupon', 'LION' ); ?>
		<?php it_exchange( 'checkout', 'cancel', 'class=sw-cart-focus-coupons&focus=coupon&label=' . $label ); ?>
	<?php endif; ?>
	
	<?php if ( $can_edit_purchase_quantity ) : ?>
		<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-quantity', 'focus' => 'quantity', 'label' => it_exchange_is_multi_item_cart_allowed() ? __( ' | View Cart', 'LION' ) : __( ' | Quantity', 'LION' ) ) ); ?>
	<?php endif; ?>
</div>
