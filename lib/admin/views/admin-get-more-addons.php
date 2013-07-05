<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php screen_icon( 'it-exchange-add-ons' );  ?>
	
	<h2>Add-ons</h2>
	<p class="top-description"><?php _e( 'Add-Ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Exchange can do.', 'LION' ); ?></p>
	
	<?php
		$this->print_add_ons_page_tabs(); 
		do_action( 'it_exchange_add_ons_page_top' );
		
		$addons = it_exchange_get_more_addons();
		$addons = it_exchange_featured_addons_on_top( $addons );
		
		$default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/exchange50px.png' );
		
		$class = '';
	?>
	
	<div class="add-ons-wrapper">
		<?php if ( ! empty( $addons ) ) : ?>
			<?php 
				$count = 0;
				foreach( (array) $addons as $addon ) : ?>
				<?php if ( ! it_exchange_is_addon_registered( $addon['slug'] ) ) : ?>
					<?php
						if ( $addon['featured'] )
							$class .= ' featured';
						
						if ( $addon['new'] )
							$class .= ' new';
						
						if ( $addon['sale'] )
							$class .= ' sale';
					?>
					<?php $icon = empty( $addon['icon'] ) ? $default_icon : $addon['icon']; ?>
					<div class="add-on-block <?php echo $class; ?>">
						<div class="add-on-icon">
							<div class="image-wrapper">
								<img src="<?php echo $icon; ?>" alt="" />
							</div>
						</div>
						<div class="add-on-info">
							<h4><?php echo $addon['name']; ?></h4>
							<span class="add-on-author"><?php _e( 'by', 'LION' ); ?> <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
							<p class="add-on-description"><?php echo $addon['description']; ?></p>
						</div>
						<div class="add-on-actions">
							<?php if ( it_exchange_is_addon_registered( $addon['slug'] ) ) : ?>
								<div class="add-on-installed"><?php _e( 'Installed', 'LION' ); ?></div>
							<?php else : ?>
								<div class="add-on-price">
									<span class="regular-price"><?php echo $addon['price']; ?></span>
									<?php if ( $addon['sale'] ) : ?>
										<span class="sale-price"><?php echo $addon['sale']; ?></span>
									<?php endif; ?>
								</div>
								<div class="add-on-buy-now">
									<a href="<?php echo $addon['addon_url']; ?>"><?php _e( 'Buy Now', 'LION' )  ?></a>
								</div>
							<?php  endif; ?>
						</div>
					</div>
				<?php $count++; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			
			<?php if ( 0 === $count ) : ?>
				<div class="addons-achievement">
					<div class="achievement-notice">
						<span><?php _e( 'ACHIEVEMENT UNLOCKED', 'LION' ) ?></span>
						<span><?php _e( 'Acquired all Exchange Add-Ons', 'LION' ) ?></span>
					</div>
					<h2><?php echo sprintf( __( 'You have all %s currently has to offer!', 'LION' ), 'iThemes Exchange' ); ?></h2>
					<p><?php _e( 'Got and idea for an add-on that would make life easier?', 'LION' ); ?></p>
					<a class="it-exchange-button" target="_blank" href="http://ithemes.com/contact"><?php _e( 'Send us a message', 'LION' ); ?></a>
					
					<div class="email-signup">
                    	<?php 
						if ( ! empty( $_REQUEST['optin-email'] ) ) {
						
							IT_Exchange_Admin::mail_chimp_signup( $_REQUEST['optin-email'] );
						
						}
						?>
						<form action="" method="post" accept-charset="utf-8">
                            <p><label for="optin-email"><?php _e( 'Sign up to be notified via email when new Add-Ons and updates are released.', 'LION' ); ?></label></p>
							<input type="text" name="optin-email" value="<?php echo get_bloginfo( 'admin_email' ); ?>">
							<input class="it-exchange-button" type="submit" value="Subscribe">
						</form>
					</div>
				</div>
			<?php endif; ?>
			
		<?php else : ?>
			<div class="no-addons-found">
				<p><?php echo sprintf( __( 'Looks like there\'s a problem loading available add-ons. Go to %s to check out other available add-ons.', 'LION' ), '<a href="http://ithemes.com/exchange">iThemes Exchange</a>' ); ?></p>
			</div>
		<?php endif; ?>
	</div> 
</div>
