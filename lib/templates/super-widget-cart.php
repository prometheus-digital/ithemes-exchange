<div class="it-exchange-sw-processing">
	<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
		<?php it_exchange( 'cart', 'form-open' ); ?>
			<!-- <div class="total-cancel-wrapper">
				<?php it_exchange( 'cart', 'subtotal' ); ?>
				<?php it_exchange( 'cart', 'empty', array( 'format' => 'link', 'label' => '&times;' ) ); ?>
			</div> -->
		
			<div class="cart-items-wrapper">
				<!--
					NOTE Still have to workout the multi-item cart markup.
				-->
				<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
					<div class="cart-item">
						<div class="title-remove">
							<?php it_exchange( 'cart-item', 'title' ) ?>
							<?php it_exchange( 'cart-item', 'remove' ); ?>
						</div>
						<div class="item-info">
							<?php if ( it_exchange( 'cart', 'focus', array( 'type' => 'quantity' ) ) ) : ?>
								 <?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity' ); ?>
							<?php else : ?>
								<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
									 <?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart', 'subtotal' ); ?>
								 <?php else : ?>
									 <?php it_exchange( 'cart-item', 'price' ); ?>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		
			<?php if ( it_exchange( 'coupons', 'supported', array( 'type' => 'cart' ) ) && it_exchange( 'cart', 'focus', array( 'type' => 'coupon' ) ) ) : ?>
				<div class="coupons-wrapper">
					<?php if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) : ?>
						<ul class="applied-coupons">
							<?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
								<li class="coupon">
									<?php it_exchange( 'coupons', 'code' ); ?> &ndash; <?php it_exchange( 'coupons', 'discount' ); ?>&nbsp;<?php it_exchange( 'coupons', 'remove', array( 'type' => 'cart' ) ); ?>
								</li>
							<?php endwhile; ?>
						</ul>
					<?php endif; ?>
				
					<?php if ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) ) : ?>
						<div class="coupon">
							<?php it_exchange( 'coupons', 'apply', array( 'type' => 'cart' ) ); ?>
							<?php it_exchange( 'cart', 'update', array( 'class' => 'it-exchange-apply-coupon-button', 'label' => __( 'Apply', 'LION' ) ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		
			<?php if ( it_exchange( 'cart', 'focus', 'type=quantity' ) ) : ?>
				<div class="payment-methods-wrapper">
					<?php it_exchange( 'cart', 'update', 'class=it-exchange-update-quantity-button&label=' . __( 'Update Quantity', 'LION' ) ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( ! it_exchange_is_multi_item_cart_allowed() ) : ?>
				<div class="cart-actions-wrapper">
					<div class="cart-action cancel-action">
						<?php it_exchange( 'cart', 'checkout', array( 'label' => __( 'Cancel', 'LION' ), 'class' => 'cancel-update' ) ); ?>
					</div>
			<?php else : ?>
				<?php if ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) || $can_edit_purchase_quantity ) : ?>
					<div class="cart-actions-wrapper <?php echo ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) && $can_edit_purchase_quantity ) ? ' two-actions' : ''; ?>">
						<?php if ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) : ?>
							<div class="cart-action add-coupon">
								<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-coupon', 'focus' => 'coupon', 'label' => __( 'Coupons', 'LION' ) ) ); ?>
							</div>
						<?php endif; ?>
		
						<?php if ( $can_edit_purchase_quantity ) : ?>
							<div class="cart-action update-quantity">
								<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-quantity', 'focus' => 'quantity', 'label' => it_exchange_is_multi_item_cart_allowed() ? __( 'View Cart', 'LION' ) : __( 'Quantity', 'LION' ) ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			
		<?php it_exchange( 'cart', 'form-close' ); ?>
	<?php endif; ?>
</div>