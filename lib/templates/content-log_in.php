<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
 */
?>

<div class="login">
    
    <?php it_exchange( 'login', 'form-open' ); ?>
    
	<?php it_exchange_get_template_part( 'messages' ); ?>
    
    <div class="user-name">
		<?php it_exchange( 'login', 'username' ); ?>
    </div>
    <div class="password">
		<?php it_exchange( 'login', 'password' ); ?>
    </div>
    <div class="rememberme">
		<?php it_exchange( 'login', 'rememberme' ); ?>
    </div>

    <?php it_exchange( 'login', 'login-button' ); ?>
    
    <div class="recover_url">
		<?php it_exchange( 'login', 'recover' ); ?>
    </div>
    
    <div class="register_url">
		<?php it_exchange( 'login', 'register' ); ?>
    </div>
    
    <?php it_exchange( 'login', 'form-close' ); ?>

</div><!-- .customer-info -->
