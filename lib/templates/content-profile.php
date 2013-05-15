<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
 */
?>

<div class="customer-info">
    
    <?php it_exchange( 'account', 'formopen' ); ?>
    
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
    <?php it_exchange( 'account', 'username' ); ?>
    </div>
    <div class="account-menu-nav">
    <?php it_exchange( 'account', 'accountmenu' ); ?>
    </div>
    <div class="customer-avatar">
    <?php it_exchange( 'account', 'avatar' ); ?>
    </div>
    <div class="first-name">
    <?php it_exchange( 'account', 'firstname' ); ?>
    </div>
    <div class="last-name">
    <?php it_exchange( 'account', 'lastname' ); ?>
    </div>
    <div class="user-name">
    <?php it_exchange( 'account', 'username' ); ?>
    </div>
    <div class="email-name">
    <?php it_exchange( 'account', 'email' ); ?>
    </div>
    <div class="website">
    <?php it_exchange( 'account', 'website' ); ?>
    </div>
    <div class="password1">
    <?php it_exchange( 'account', 'password1' ); ?>
    </div>
    <div class="password2">
    <?php it_exchange( 'account', 'password2' ); ?>
    </div>

    <?php it_exchange( 'account', 'save' ); ?>
    
    <?php it_exchange( 'account', 'formclose' ); ?>

</div><!-- .customer-info -->