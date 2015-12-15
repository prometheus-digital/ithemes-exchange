<?php
/**
 * This file contains the contents of the Tools page
 * @since   1.33
 * @package IT_Exchange
 */
?>
<div class="wrap tools-wrap tools-upgrade-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
	<h1><?php _e( 'Tools', 'it-l10n-ithemes-exchange' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="" class="nav-tab nav-tab-active">Upgrades</a>
		<a href="" class="nav-tab">System Status</a>
	</h2>

	<div class="upgrades-container">
		<div class="upgrade-row">
			<h3>Coupons</h3>
			<p class="description">This is a description of the coupon upgrade.</p>
			<button class="button button-secondary">Upgrade</button>

			<div class="upgrade-progress">
				<a href="javascript:">View Details</a>
				<progress value="10" max="100"></progress>
			</div>

			<div class="upgrade-feedback">
				<label for="upgrade-feedback-{upgrade}" class="screen-reader-text">Upgrade Feedback</label>
				<textarea readonly id="upgrade-feedback-{upgrade}">This is some feedback</textarea>
			</div>
		</div>
		<div class="upgrade-row">
			<h3>Orders</h3>
			<p class="description">This is a description of the orders upgrade.</p>
			<button class="button button-secondary">Upgrade</button>

			<div class="upgrade-progress">
				<a href="javascript:">View Details</a>
				<progress value="10" max="100"></progress>
			</div>

			<div class="upgrade-feedback">
				<label for="upgrade-feedback-{upgrade}" class="screen-reader-text">Upgrade Feedback</label>
				<textarea readonly id="upgrade-feedback-{upgrade}">This is some feedback</textarea>
			</div>
		</div>
	</div>

</div>
