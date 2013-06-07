<?php if ( it_exchange( 'product', 'found' ) ) : ?>
	<?php if ( it_exchange( 'cart', 'get-item-count' ) && it_exchange_is_multi_item_cart_allowed() ) : ?>
		<div class="item-count">
			<?php printf( __( 'You have %s item(s) in your <a href="%s">cart</a>', 'LION' ), it_exchange( 'cart', 'get-item-count' ), it_exchange_get_page_url( 'cart' ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( it_exchange_is_view( 'product' ) ) : ?>
	<div class="it-exchange-product">
		<?php it_exchange( 'product', 'purchase-options' ); ?>
	</div>
	<?php endif; ?>
<?php endif; ?>
