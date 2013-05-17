<?php
/**
 * The default template for displaying a single iThemes Exchange registration page
 *
 * @since 0.4.0
 */
?>

<div class="registration-info">

	<?php if ( is_user_logged_in() ) : ?>
		<p><?php printf( __( 'You already have an active account and are logged in. Visit your %sProfile%s', 'LION' ), '<a href="' . it_exchange_get_page_url( 'profile' ) . '">', '</a>' ); ?></p>
	<?php else : ?>
		<?php if ( it_exchange( 'registration', 'is-enabled' ) ) { ?>
		
		<?php it_exchange( 'registration', 'formopen' ); ?>
		
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
		<?php it_exchange( 'registration', 'firstname' ); ?>
		</div>
		<div class="last-name">
		<?php it_exchange( 'registration', 'lastname' ); ?>
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
		
		<?php it_exchange( 'registration', 'formclose' ); ?>
		
		
		<?php } else { ?>
		
			<?php it_exchange( 'registration', 'disabled-message' ); ?>
		
		<?php } ?>
	<?php endif; ?>
</div><!-- .customer-info -->
