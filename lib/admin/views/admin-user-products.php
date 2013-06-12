<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
 * @todo get the transaction extra buttons to work
*/

$headings = array(
	__( 'Products', 'LION' ),
	__( 'Transaction', 'LION' ),
);

$product_list = array();
foreach( (array) it_exchange_get_customer_products( $user_id ) as $product ) {
	// Build Product Link
	$product_id   = $product['product_id'];
	$product_url  = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $product_id );
	$product_name = it_exchange_get_transaction_product_feature( $product, 'product_name' );
	$product_link = '<a href="' . $product_url . '">' . $product_name . '</a>';

	// Build Transaction Link
	$transaction_id     = it_exchange_get_transaction_product_feature( $product, 'transaction_id' );
	$transaction_url    = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction_id );
	$transaction_number = it_exchange_get_transaction_order_number( $transaction_id );
	$transaction_link   = '<a href="' . $transaction_url . '">' . $transaction_number . '</a>';
	
	if ( $downloads = it_exchange_get_product_feature( $product_id, 'downloads' ) )
		$product_link .= '<span class="details">' . __( 'Details', 'LION' ) . '</span>';	

	$product_list[$product_id] = array(
		'product_link'     => $product_link,
		'transaction_link' => $transaction_link,
		'downloads'        => $downloads,
	);
}
	
?>

<div class="heading-row block-row">

	<?php $column = 0; ?>
	<?php foreach ( $headings as $heading ) : ?>
		<?php $column++ ?>
		
		<div class="heading-column block-column block-column-<?php echo $column; ?>">
			<span class="heading"><?php echo $heading; ?></span>
		</div>
		
	<?php endforeach; ?>
	
</div>

<?php foreach ( (array)$product_list as $product_id => $data ) : ?>

	<div class="item-row block-row">
    
        <div class="item-columnitem-column-50  block-column block-column-1">
            <span class="item"><?php echo $data['product_link']; ?></span>
        </div>
        <div class="item-column item-column-50 block-column block-column-2">
            <span class="item"><?php echo $data['transaction_link']; ?></span>
        </div>
        
        <?php if ( !empty( $data['downloads'] ) ) { ?>
        <div class="item-column item-column-100 block-column block-column-3">
		<h2><?php _e( 'Downloads', 'LION' ); ?></h2>
            <div>
            <?php
            foreach ( $data['downloads'] as $download ) {

				echo '<h3>' . $download['name'] . '</h3>';
				
				ITDebug::print_r( $download );
				
				$download_hash = '';
				
				echo '<span class="remaining">';
				if ( !$download['download_limit'] ) {
					_e( 'Unlimited Downloads', 'LION' );	
				} else {
					printf( __( '%d of %d downloads remaining', 'LION' ), 0, $download['download_limit'] );
				}
				echo '</span>';
				
				echo '<span class="expires">';
				if ( !$download['expires'] ) {
					_e( 'Never Expires', 'LION' );	
				} else {
					printf( __( 'Expires %s', 'LION' ), date_i18n( 'M d, Y', time() )  );
				}
				echo '</span>';
            }
            ?>
            </div>
        </div>
        <?php } ?>
        
    </div>
    
<?php endforeach; ?>
     
</div>
