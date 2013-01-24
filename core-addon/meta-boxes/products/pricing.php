<?php
/**
 * This file creates the Core Product Meta box for the products page
 *
*/
if ( ! class_exists( 'IT_CartBuddy_Core_Prodcut_Pricing_MB' ) ) {
	class IT_CartBuddy_Core_Product_Pricing_MB {

		// Class constructor handles hooks
		function IT_CartBuddy_Core_Product_Pricing_MB() {
			add_action( 'init', array( $this, 'register_mb' ), 1 );
		}

        /**
         * Registers the pricing metabox
         *
        */
        function register_mb() {
            $options = array(
                'var'       => 'it_cartbuddy_pricing',
                'title'     => __( 'Price', 'LION' ),
                'context'   => 'normal',
                'priority'  => 'default',
                'callback'  => array( $this, 'do_pricing_mb' ),
            );
            $options = apply_filters( 'it_cartbuddy_core_pricing_metabox_option', $options );
            cartbuddy( 'add_meta_box', $options );
        }

        /**
         * This prints inside the pricing metabox
         *
         * @since 0.1
         * @return void
        */
        function do_pricing_mb( $post, $form ) {
            ?>
            <div class="it_cartbuddy_cb_row">
                <p>
                    <label for="amount"><?php _e( 'Amount:', 'LION' ); ?></label>
                    <?php $form->add_text_box( 'amount' ); ?>
                    <span class="description">Amount of purchase. Your currency is set to <code>USD</code>. <a href=""><?php _e( 'Change currency', 'LION' ); ?></a></span>
                </p>
            </div>
            <div class="it_cartbuddy_cb_row">
                <p>
                    <label for="max_qauntity"><?php _e( 'Max Quanity:', 'LION' ); ?></label>
                    <?php $form->add_drop_down( 'max_quantity', array( '0' => 'No Limit', '1' => '1', '2' => '2', 'custom' => 'Custom' ) ); ?>
                    <span class="description">Max quanity available per order</span>
                </p>
            </div>
            <?php
        }
	}
	new IT_CartBuddy_Core_Product_Pricing_MB();
}
