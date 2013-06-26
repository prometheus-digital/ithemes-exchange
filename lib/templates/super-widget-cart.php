<?php it_exchange_get_template_part( 'messages' ); ?>

<div class="it-exchange-sw-processing it-exchange-sw-processing-cart">

<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
	
	<?php if ( ( it_exchange_is_page( 'product' ) && it_exchange_is_current_product_in_cart() )
			|| it_exchange( 'cart', 'focus', array( 'type' => 'coupon' ) )
			|| it_exchange( 'cart', 'focus', array( 'type' => 'quantity' ) ) ) : ?>
    
        <?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
            
            <?php it_exchange( 'cart', 'form-open' ); ?>
                        
                <div class="cart-items-wrapper">
                    <?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
                        <div class="cart-item">
                            <div class="title-remove">
                                <?php it_exchange( 'cart-item', 'title' ) ?>
                                <?php it_exchange( 'cart-item', 'remove' ); ?>
                            </div>
                            <div class="item-info">
                                <?php if ( it_exchange( 'cart-item', 'has-purchase-quantity' ) ) : ?>
                                
                                     <?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity' ); ?>
                                     
                                <?php else : ?>
                                    
                                    <?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
                                         <?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart-item', 'subtotal' ); ?>
                                    <?php else : ?>
                                         <?php it_exchange( 'cart-item', 'price' ); ?>
                                    <?php endif; ?>
                                    
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
            
                <?php if ( it_exchange( 'coupons', 'supported', array( 'type' => 'cart' ) ) && it_exchange( 'cart', 'focus', array( 'type' => 'coupon' ) ) ) : ?>
                    <div class="coupons-wrapper">
                        <?php if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) : ?>
                            <ul class="applied-coupons">
                                <?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
                                    <li class="coupon">
                                        <?php it_exchange( 'coupons', 'code' ); ?> &ndash; <?php it_exchange( 'coupons', 'discount-label' ); ?>&nbsp;<?php it_exchange( 'coupons', 'remove', array( 'type' => 'cart' ) ); ?>
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
                        
                        <div class="cart-actions-wrapper">
                            <div class="cart-action cancel-update">
							<?php it_exchange( 'cart', 'checkout', array( 'class' => 'sw-cart-focus-checkout', 'focus' => 'checkout', 'label' =>  __( 'Cancel', 'LION' ) ) ); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            
                <?php if ( it_exchange( 'cart', 'focus', 'type=quantity' ) ) : ?>
                    <div class="cart-actions-wrapper">
                        <div class="cart-action cancel-update">
						<?php it_exchange( 'cart', 'update', 'class=it-exchange-update-quantity-button&label=' . __( 'Update Quantity', 'LION' ) ); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
                    <div class="cart-actions-wrapper two-actions">
                        <div class="cart-action view-cart">
							<?php it_exchange( 'cart', 'view-cart', array( 'class' => 'sw-cart-focus-cart', 'focus' => 'cart' ) ); ?>
                        </div>     
                        <div class="cart-action checkout">
                            <?php it_exchange( 'cart', 'checkout', array( 'class' => 'sw-cart-focus-checkout', 'focus' => 'checkout' ) ); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php it_exchange( 'cart', 'form-close' ); ?>
            
        <?php endif; ?>
    
    <?php else : ?>
    
	<?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
	
		<?php $count = it_exchange( 'cart', 'get-item-count' ); ?>
        <?php // just display a cart summary ?>
            <div class="item-count">
    
            <?php if ( $count === 1 ) : ?>
                <?php printf( __( 'You have 1 item in your <a href="%s">%s</a>', 'LION' ), it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
            <?php else : ?>
                <?php printf( __( 'You have %s items in your <a href="%s">%s</a>', 'LION' ), $count, it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
            <?php endif; ?>
            
            </div>
        <?php endif; ?>
        
    <?php endif; ?>

<?php elseif ( ! it_exchange_is_page( 'product' ) ) : ?>

    <?php printf( __( 'Your %s is empty', 'LION' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>

<?php endif; ?>

</div>