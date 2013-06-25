<?php
/**
 * The login tempalte part for the Super Widget
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div class="login it-exchange-sw-processing-login">
	<?php it_exchange( 'login', 'formopen' ); ?>
	
	<?php it_exchange_get_template_part( 'messages' ); ?>
	
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
		<?php it_exchange( 'login', 'recover' ); ?>
	</div>

    <div class="register_url">
        <?php it_exchange( 'login', 'register' ); ?>
    </div>

    <div class="cancel_url">
        <?php it_exchange( 'login', 'cancel' ); ?>
    </div>
    <?php it_exchange( 'login', 'form-close' ); ?>
</div>
