<?php
/**
 * The default template for displaying a single iThemes Exchange registration page
 *
 * @since 0.4.0
 */
?>

<div class="registration-info it-exchange-sw-processing-registration">

	<?php if ( it_exchange( 'registration', 'is-enabled' ) ) { ?>
		
		<?php it_exchange( 'registration', 'form-open' ); ?>
		
		<?php if ( it_exchange( 'messages', 'has-errors' ) ) : ?>
			<ul class='errors'>
			<?php while( it_exchange( 'messages', 'errors' ) ) : ?>
				<li><?php it_exchange( 'messages', 'error' ); ?></li>
			<?php endwhile; ?>
			</ul>
		<?php endif; ?>

		<?php if ( it_exchange( 'messages', 'has-notices' ) ) : ?>
			<ul class='notices'>
			<?php while( it_exchange( 'messages', 'notices' ) ) : ?>
				<li><?php it_exchange( 'messages', 'notice' ); ?></li>
			<?php endwhile; ?>
			</ul>
		<?php endif; ?>
		
		<div class="user-name">
		<?php it_exchange( 'registration', 'username' ); ?>
		</div>
		<div class="first-name">
		<?php it_exchange( 'registration', 'first-name' ); ?>
		</div>
		<div class="last-name">
		<?php it_exchange( 'registration', 'last-name' ); ?>
		</div>
		<div class="email-name">
		<?php it_exchange( 'registration', 'email' ); ?>
		</div>
		<div class="password1">
		<?php it_exchange( 'registration', 'password1' ); ?>
		</div>
		<div class="password2">
		<?php it_exchange( 'registration', 'password2' ); ?>
		</div>

		<?php it_exchange( 'registration', 'save' ); ?>
		<?php it_exchange( 'registration', 'cancel' ); ?>
		
	<?php it_exchange( 'registration', 'form-close' ); ?>
	
	<?php } else { ?>
	
		<?php it_exchange( 'registration', 'disabled-message' ); ?>
	
	<?php } ?>
</div><!-- .customer-info -->
