<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
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
    <?php it_exchange( 'login', 'rememberme' ); ?>
    </div>

    <?php it_exchange( 'login', 'loginbutton' ); ?>
    
    <div class="recover_url">
    <?php it_exchange( 'login', 'recoverurl' ); ?>
    </div>
    
    <?php it_exchange( 'login', 'formclose' ); ?>

</div><!-- .customer-info -->