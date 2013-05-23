<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
 */

// it_exchange( 'customer', 'username' );
// it_exchange( 'customer', 'accountmenu' );
// it_exchange( 'customer', 'avatar' );
// it_exchange( 'customer', 'username' );
?>

<div class="customer-info">
	<?php it_exchange( 'customer', 'formopen' ); ?>
		<?php it_exchange_get_template_part( 'messages' ); ?>
		
		<div class="customer-name">
			<div class="first-name">
				<?php it_exchange( 'customer', 'firstname' ); ?>
			</div>
			<div class="last-name">
				<?php it_exchange( 'customer', 'lastname' ); ?>
			</div>
		</div>
		
		<div class="customer-email">
			<?php it_exchange( 'customer', 'email' ); ?>
		</div>
		
		<div class="customer-website">
			<?php it_exchange( 'customer', 'website' ); ?>
		</div>
		
		<div class="customer-password">
			<div class="password-1">
				<?php it_exchange( 'customer', 'password1' ); ?>
			</div>
			<div class="password-2">
				<?php it_exchange( 'customer', 'password2' ); ?>
			</div>
		</div>
		
		<div class="customer-save">
			<?php it_exchange( 'customer', 'save' ); ?>
		</div>
		
	<?php it_exchange( 'customer', 'formclose' ); ?>
</div>