<?php
/**
 * The login tempalte part for the Super Widget
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div class="login">
	<?php it_exchange( 'login', 'formopen' ); ?>
	
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
		<?php it_exchange( 'login', 'username' ); ?>
	</div>
	<div class="password">
		<?php it_exchange( 'login', 'password' ); ?>
	</div>
	<div class="rememberme">
		<?php it_exchange( 'login', 'remember-me' ); ?>
	</div>

	<?php it_exchange( 'login', 'login-button' ); ?>
	
	<div class="recover_url">
		<?php it_exchange( 'login', 'recover-url' ); ?>
	</div>
	
		<div class="register_url">
			<a href="<?php echo it_exchange_get_page_url( 'registration' ); ?>"><?php _e( 'Register', 'LION' ); ?></a>
		</div>
    <?php it_exchange( 'login', 'form-close' ); ?>
</div>
