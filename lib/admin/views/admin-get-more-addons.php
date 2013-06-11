<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php screen_icon( 'page' );  ?>
	
	<h2>Add-ons</h2>
	<p class="top-description"><?php _e( 'Add-Ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Exchange can do.', 'LION' ); ?></p>
	
	<?php
		$this->print_add_ons_page_tabs(); 
		do_action( 'it_exchange_add_ons_page_top' );
		
		$addons = it_exchange_get_more_addons();
		$addons = it_exchange_featured_addons_on_top( $addons );
		
		$class = '';
	?>
	<div class="add-ons-wrapper">
		<?php if ( ! empty( $addons ) ) : ?>
			
			<?php $default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/default-add-on-icon.png' ); ?>
			
			<?php 
				$count = 0;
				foreach( (array) $addons as $addon ) : ?>
				<?php if ( ! it_exchange_is_addon_installed( $addon['slug'] ) ) : ?>
					<?php
						
						if ( $addon['featured'] )
							$class .= ' featured';
						
						if ( $addon['new'] )
							$class .= ' new';
						
						if ( $addon['sale'] )
							$class .= ' sale';
					?>
					<?php $icon = empty( $addon['options']['icon'] ) ? $default_icon : $addon['options']['icon']; ?>
					<div class="add-on-block <?php echo $class; ?>">
						<div class="add-on-icon">
							<div class="image-wrapper">
								<img src="<?php echo $icon; ?>" alt="" />
							</div>
						</div>
						<div class="add-on-info">
							<h4><?php echo $addon['name']; ?></h4>
							<span class="add-on-author">by <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
							<p class="add-on-description"><?php echo $addon['description']; ?></p>
						</div>
						<div class="add-on-actions">
							<?php if ( it_exchange_is_addon_installed( $addon['slug'] ) ) : ?>
								<div class="add-on-installed">Installed</div>
							<?php else : ?>
								<div class="add-on-price">
									<span class="regular-price"><?php echo $addon['price']; ?></span>
									<?php if ( $addon['sale'] ) : ?>
										<span class="sale-price"><?php echo $addon['sale']; ?></span>
									<?php endif; ?>
								</div>
								<div class="add-on-buy-now"><a href="<?php echo $addon['addon_url']; ?>"><?php _e( 'Buy Now', 'LION' )  ?></a></div>
							<?php  endif; ?>
						</div>
					</div>
                <?php $count++; ?>
				<?php endif; ?>
			<?php endforeach; ?>
            <?php 
				if ( 0 === $count )
					 _e( 'You have all iThemes Exchange currently has to offer. Got an idea for an add-on that would make your life easier? <a href="http://ithemes.com/contact/">E-mail us</a>.', 'LION' );
			?>
		<?php else : ?>
			<p><?php __( 'No Add-ons in the store.', 'LION' ); ?></p>
		<?php endif; ?>
	</div> 
</div>