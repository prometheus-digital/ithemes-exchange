<?php
/**
 * Contains the contents of the upgrades page.
 *
 * @since   2.0.0
 * @license GPLv2
 * @var IT_Exchange_Admin $this
 */

$upgrader = it_exchange_make_upgrader();
?>
<div class="wrap tools-wrap tools-upgrade-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
	<h1><?php _e( 'Tools', 'it-l10n-ithemes-exchange' ); ?></h1>

	<?php $this->print_tools_page_tabs(); ?>

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

                <?php if ( $completed ): ?>
                    <?php if ( $file = it_exchange_get_upgrade_log_file( $upgrade->get_slug() ) ) : ?>
                        <p class="upgrade-file">
			                <?php $esc = esc_url( $file ); printf( __( 'Download Log File: %s', 'it-l10n-ithemes-exchange' ), "<a href='$esc'>{$file}</a>" ); ?>
                        </p>
                    <?php endif; ?>
				<?php else: ?>

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

					<p class="upgrade-file hidden">
						<?php _e( 'Download Log File:', 'it-l10n-ithemes-exchange' ); ?>
					</p>

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

