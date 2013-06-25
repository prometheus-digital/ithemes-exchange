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
        
		<?php it_exchange_get_template_part( 'messages' ); ?>
		
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
        
		<div class="cancel_url">
		<?php it_exchange( 'registration', 'cancel' ); ?>
        </div>
		
	<?php it_exchange( 'registration', 'form-close' ); ?>
	
	<?php } else { ?>
	
		<?php it_exchange( 'registration', 'disabled-message' ); ?>
	
	<?php } ?>
</div><!-- .customer-info -->
