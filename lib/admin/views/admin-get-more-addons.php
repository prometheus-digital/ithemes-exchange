<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php ITUtility::screen_icon( 'it-exchange-add-ons' );  ?>

	<h2>Add-ons</h2>
	<p class="top-description"><?php _e( 'Add-ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. If you have already purchased additional Exchange add-ons, please upload and activate them through the WordPress plugins menu.', 'it-l10n-ithemes-exchange' ); ?></p>

	<?php
		$this->print_add_ons_page_tabs();
		do_action( 'it_exchange_add_ons_page_top' );

		// $addons = it_exchange_get_more_addons();
		// $addons = it_exchange_featured_addons_on_top( $addons );

		$default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/exchange50px.png' );

		$class = '';
	?>
			<div class="no-addons-found">
				<p><?php echo sprintf( __( 'Looks like there\'s a problem loading available add-ons. Go to %s to check out other available add-ons.', 'it-l10n-ithemes-exchange' ), '<a href="http://exchangewp.com/">ExchangeWP</a>' ); ?></p>
			</div>
	</div>
</div>
