<div class="it-exchange-sw-processing">
	<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) : ?>
		<?php it_exchange( 'cart', 'form-open' ); ?>
			<div class="item-count">
				<?php printf( __( 'You have %s item(s) in your <a href="%s">%s</a>', 'LION' ), it_exchange( 'cart', 'get-item-count' ), it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
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
		<?php it_exchange( 'cart', 'form-close' ); ?>
	<?php else: ?>
		<p class="cart-empty"><?php printf( __( 'Your %s is empty', 'LION' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?></p>
	<?php endif; ?>
</div>
