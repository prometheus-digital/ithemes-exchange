<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
 */
?>

<div class="login">
    
    <?php it_exchange( 'login', 'formopen' ); ?>
    
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

    <?php it_exchange( 'login', 'loginbutton' ); ?>
    
    <div class="recover_url">
    <?php it_exchange( 'login', 'recoverurl' ); ?>
    </div>
    
    <?php it_exchange( 'login', 'formclose' ); ?>

</div><!-- .customer-info -->