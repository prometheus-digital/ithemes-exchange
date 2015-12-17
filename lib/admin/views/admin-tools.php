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
		<a href="<?php echo admin_url( 'admin.php?page=it-exchange-tools' ); ?>" class="nav-tab nav-tab-active">
			<?php _e( 'Upgrades', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</h2>

	<div class="upgrades-container">

		<?php foreach ( $upgrader->get_upgrades( true ) as $upgrade ): ?>

			<?php
			$completed = $upgrader->is_upgrade_completed( $upgrade ) ? ' completed' : '';
			$partial   = ! $completed && $upgrader->is_upgrade_in_progress( $upgrade ) ? ' in-progress' : '';
			$button    = $partial ? 'button-primary' : 'button-secondary';
			?>

			<div class="upgrade-row<?php echo $completed . $partial; ?>" data-upgrade="<?php echo $upgrade->get_slug(); ?>">

				<header>
					<h3><?php echo $upgrade->get_name(); ?></h3>
					<h4><?php echo $upgrade->get_group() . ' &ndash; v' . $upgrade->get_version(); ?></h4>
				</header>

				<p class="description"><?php echo $upgrade->get_description(); ?></p>

				<?php if ( ! $completed ) : ?>

					<button class="button <?php echo $button; ?>">

						<?php if ( $partial ) : ?>
							<?php _e( 'Resume', 'it-l10n-ithemes-exchange' ); ?>
						<?php else: ?>
							<?php _e( 'Upgrade', 'it-l10n-ithemes-exchange' ); ?>
						<?php endif; ?>
					</button>

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

				<?php endif; ?>
			</div>

		<?php endforeach; ?>
	</div>

</div>
