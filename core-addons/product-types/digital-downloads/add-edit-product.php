<?php
/**
 * This file contains logic for the admin new / edit product screen
 * @package IT_Cart_Buddy
 * @since 0.3.3
*/

function it_cart_buddy_register_digital_downloads_metabox( $product ) {
	add_meta_box( 'it_cart_buddy_digital_downloads', __( 'Downloads', 'LION' ), 'it_cart_buddy_print_digital_downloads_meta_box', 'it_cart_buddy_prod','normal', 'default', array( 'product' => $product ) );
}
add_action( 'it_cart_buddy_product_metabox_callback_digital-downloads', 'it_cart_buddy_register_digital_downloads_metabox' );

function it_cart_buddy_print_digital_downloads_meta_box( $post, $data ) {
	?>
	<div>
	<p>Download interface will go here</p>
	</div>
	<?php
	//echo "<pre>";print_r($data['args']['product']);die();
}
