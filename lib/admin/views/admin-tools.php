<?php
/**
 * This file contains the contents of the Tools page
 * @since   1.33
 * @package IT_Exchange
 */

$upgrader = it_exchange_make_upgrader();
?>
<div class="wrap tools-wrap tools-upgrade-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
	<h1><?php _e( 'Tools', 'it-l10n-ithemes-exchange' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="" class="nav-tab nav-tab-active">Upgrades</a>
		<a href="" class="nav-tab">System Status</a>
	</h2>

	<div class="upgrades-container">

		<?php foreach ( $upgrader->get_available_upgrades() as $upgrade ): ?>

			<div class="upgrade-row" data-upgrade="<?php echo $upgrade->get_slug(); ?>">
				<h3><?php echo $upgrade->get_name(); ?></h3>
				<p class="description"><?php echo $upgrade->get_description(); ?></p>
				<button class="button button-secondary"><?php _e( 'Upgrade', 'it-l10n-ithemes-exchange' ); ?></button>

				<div class="upgrade-progress">
					<a href="javascript:"><?php _e( 'View Details', 'it-l10n-ithemes-exchange' ); ?></a>
					<progress value="0" max="100"></progress>
				</div>

				<div class="upgrade-feedback">
					<label for="upgrade-feedback-<?php echo $upgrade->get_slug(); ?>" class="screen-reader-text">
						<?php _e( 'Upgrade Feedback', 'it-l10n-ithemes-exchange' ); ?>
					</label>
					<textarea readonly id="upgrade-feedback-<?php echo $upgrade->get_slug(); ?>"></textarea>
				</div>
			</div>

		<?php endforeach; ?>

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
