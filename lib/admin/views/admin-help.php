<?php
/**
 * This file contains the contents of the Help/Support page
 * @since 0.4.14
 * @package IT_Exchange
*/
?>
<div class="wrap help-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' );  ?>
	<h2><?php _e( 'Help and Resources', 'LION' ); ?></h2>

	<p class="top-description"><?php printf( __( 'We\'ve built %s to simplify ecommerce for WordPress. However, ecommerce is not always easy, so we\'ve taken the time to create some resources to help you get started.', 'LION' ), '<a title="iThemes Exchange" href="http://ithemes.com/exchange/" target="_blank">iThemes Exchange</a>' ); ?></p>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Quick Links', 'LION' ); ?></h3>
		<div class="help-action exchange-wizard help-tip" title="<?php _e( 'This is a link back to the Quick Setup page that opens after Exchange is installed. This page walks through the necessary information and settings needed to set up your store.', 'LION' ); ?>">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Go back to the Exchange Quick Setup Wizard.', 'LION' ); ?></p>
			<p><a href="<?php echo get_admin_url( NULL, 'admin.php?page=it-exchange-setup' ); ?>" target="_self"><?php _e( 'Open the Wizard', 'LION' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Resources', 'LION' ); ?></h3>
		<div class="help-action exchange-tutorials" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Short video tutorials to help you become an Exchange expert.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/tutorials/ithemes-exchange" target="_blank"><?php _e( 'Checkout some tutorials', 'LION' ); ?></a></p>
		</div>
		<div class="help-action exchange-codex" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Read through the Exchange documentation.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/codex/page/Exchange" target="_blank"><?php _e( 'Dig Deep into Exchange', 'LION' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Support', 'LION' ); ?></h3>
		<div class="help-action exchange-support" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Get free help on some basics of Exchange.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/forum/forum/207-exchange-ecommerce-plugin/" target="_blank"><?php _e( 'Get Basic Help', 'LION' ); ?></a></p>
		</div>
		<div class="help-action exchange-paid-support" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Get premium and priority support for Exchange.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/exchange/support/" target="_blank"><?php _e( 'Get Premium Support', 'LION' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Report &amp Request', 'LION' ); ?></h3>
		<div class="help-action exchange-report" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Help us fix an issue that you have found in Exchange.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/exchange/bugs/" target="_blank"><?php _e( 'Report a Bug or Problem', 'LION' ); ?></a></p>
		</div>
		<div class="help-action exchange-request" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Help us improve Exchange for everyone.', 'LION' ); ?></p>
			<p><a href="http://ithemes.com/exchange/feature-request/" target="_blank"><?php _e( 'Request a Feature', 'LION' ); ?></a></p>
		</div>
	</div>
</div>
