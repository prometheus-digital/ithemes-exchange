<?php
/**
 * The default template for displaying a single iThemes Exchange user profile
 *
 * @since 0.4.0
 */
?>

<div class="customer-info">
    
    <?php it_exchange( 'customer', 'formopen' ); ?>
    
	<?php it_exchange_get_template_part( 'messages' ); ?>
    
    <div class="user-name">
    <?php it_exchange( 'customer', 'username' ); ?>
    </div>
    <div class="account-menu-nav">
    <?php it_exchange( 'customer', 'accountmenu' ); ?>
    </div>
    <div class="customer-avatar">
    <?php it_exchange( 'customer', 'avatar' ); ?>
    </div>
    <div class="first-name">
    <?php it_exchange( 'customer', 'firstname' ); ?>
    </div>
    <div class="last-name">
    <?php it_exchange( 'customer', 'lastname' ); ?>
    </div>
    <div class="user-name">
    <?php it_exchange( 'customer', 'username' ); ?>
    </div>
    <div class="email-name">
    <?php it_exchange( 'customer', 'email' ); ?>
    </div>
    <div class="website">
    <?php it_exchange( 'customer', 'website' ); ?>
    </div>
    <div class="password1">
    <?php it_exchange( 'customer', 'password1' ); ?>
    </div>
    <div class="password2">
    <?php it_exchange( 'customer', 'password2' ); ?>
    </div>

    <?php it_exchange( 'customer', 'save' ); ?>
    
    <?php it_exchange( 'customer', 'formclose' ); ?>

</div><!-- .customer-info -->