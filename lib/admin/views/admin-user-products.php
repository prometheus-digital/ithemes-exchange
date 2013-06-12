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
	
	$transaction_downloads = it_exchange_get_transaction_download_hash_index( $transaction_id );
	
	if ( !empty( $transaction_downloads[$product_id] ) ) {
		
		$downloads = $transaction_downloads[$product_id];
		$product_link .= __( 'Details', 'LION' );
	
	} else {
		
		$downloads = array();
		
	}

	$product_list[] = array(
		'product_link'     => $product_link,
		'transaction_link' => $transaction_link,
		'downloads'        => $downloads,
	);
}
	
?>

<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">
    
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
        
            <div class="item-column item-column-50">
                <span class="item"><?php echo $data['product_link']; ?></span>
            </div>
            <div class="item-column item-column-50">
                <span class="item"><?php echo $data['transaction_link']; ?></span>
            </div>
            
            <?php if ( !empty( $data['downloads'] ) ) { ?>
            <div class="item-column item-column-100">
            <h3><?php _e( 'Downloads', 'LION' ); ?></h3>
                <div>
                <?php
                foreach ( $data['downloads'] as $download_id => $download_hashes ) {
                                    
                    $download_info = it_exchange_get_download_info( $download_id );
                    
                    if ( !empty( $download_info['source'] ) ) {
                            
                        echo '<h4>' . get_the_title( $download_id ) . '</h4>';
                                            
                        $end = end( ( explode( '/', $download_info['source'] ) ) );
                            
                        echo '<h5>' . $end . '</h5>';
                        
                        foreach( $download_hashes as $download_hash ) {
                                                    
                            $download_data = it_exchange_get_download_data( $download_id, $download_hash );
                            
                            if ( !empty( $download_data ) ) {
                            
                                echo '<ul>';
                                echo '<li>' . $download_data['hash'] . '</li>';
                                
                                if ( !empty( $download_data['download_limit'] ) )
                                    echo '<li>' . sprintf( __( '%s of %s downloads remaining', 'LION' ), $download_data['downloads'], $download_data['download_limit'] ) . '</li>';
                                else
                                    echo '<li>' . __( 'Unlimited Downloads', 'LION' ) . '</li>';
                                    
                                if ( !empty( $download_data['expires'] ) )
                                    echo '<li>' . sprintf( __( 'Expires %s', 'LION' ), date_i18n( get_option( 'date_format' ), $download_data['expires'] ) ) . '</li>';
                                else
                                    echo '<li>' . __( 'Never Expires', 'LION' ) . '</li>';
                                
                                echo '</ul>';
                                
                            }
                                                    
                        }
                        
                    }
                    
                }
                ?>
                </div>
            </div>
            <?php } ?>
            
        </div>
        
    <?php endforeach; ?>
	
</div>