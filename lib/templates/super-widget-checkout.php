<?php it_exchange_get_template_part( 'messages' ); ?>

<div class="it-exchange-sw-processing it-exchange-sw-processing-checkout">

<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>

	<div class="cart-items-wrapper">
		<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
			<?php $can_edit_purchase_quantity = it_exchange( 'cart-item', 'supports-purchase-quantity' ) && ( it_exchange_get_cart_products_count() < 2 ); ?>
			<div class="cart-item">
				<div class="title-remove">
					<?php it_exchange( 'cart-item', 'title' ) ?>
					<?php it_exchange( 'cart-item', 'remove' ); ?>
				</div>
				<div class="item-info">
					<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
						<?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart', 'subtotal' ); ?>
					 <?php else : ?>
						 <?php it_exchange( 'cart-item', 'price' ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endwhile; ?>
        
        <?php if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ): ?>
            <div class="cart-discount">
                <?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
                    <?php it_exchange( 'coupons', 'discount-label' ); ?> <?php _e( 'OFF', 'LION' ); ?> &#61; <?php it_exchange( 'cart', 'total' ); ?>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
	</div>

	<?php if ( ! it_exchange_is_multi_item_cart_allowed() || ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() < 2 ) ) : ?>
		<div class="payment-methods-wrapper">
			<?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
				<p><?php _e( 'No payment add-ons enabled.', 'LION' ); ?></p>
			<?php else : ?>
				<?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
					<?php it_exchange( 'transaction-method', 'make-payment' ); ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) || $can_edit_purchase_quantity ) : ?>
		<div class="cart-actions-wrapper <?php echo ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) && $can_edit_purchase_quantity ) || ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) ? ' two-actions' : ''; ?>">
			<?php if ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) : ?>
				<div class="cart-action view-cart">
					<?php it_exchange( 'checkout', 'cancel', array( 'label' => __( 'View Cart', 'LION' ) ) ); ?>
				</div>
				<div class="cart-action checkout">
					<?php it_exchange( 'cart', 'checkout' ); ?>
				</div>
			<?php else : ?>
				<?php if ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) : ?>
					<div class="cart-action add-coupon">
						<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-coupon', 'focus' => 'coupon', 'label' => __( 'Coupons', 'LION' ) ) ); ?>
					</div>
				<?php endif; ?>
			
				<?php if ( $can_edit_purchase_quantity ) : ?>
					<div class="cart-action update-quantity">
						<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-quantity', 'focus' => 'quantity', 'label' => __( 'Quantity', 'LION' ) ) ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php else: ?>
<?php printf( __( '%s Empty', 'LION' ), it_exchange_get_page_name( 'cart' ) ); ?>
<?php endif; ?>
</div>
