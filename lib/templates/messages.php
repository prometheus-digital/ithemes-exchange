<?php
/**
 * This file contains the markup for error and
 * notice messages.
 * 
 * @since 0.4.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 * 
 * Example: theme/exchange/messages.php
*/
?>

<?php if ( it_exchange( 'messages', 'has-errors' ) ) : ?>
	<ul class="it-exchange-messages it-exchange-errors">
		<?php while ( it_exchange( 'messages', 'errors' ) ) : ?>
			<li><?php it_exchange( 'messages', 'error' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>

<?php if ( it_exchange( 'messages', 'has-notices' ) ) : ?>
	<ul class="it-exchange-messages it-exchange-notices">
		<?php while ( it_exchange( 'messages', 'notices' ) ) : ?>
			<li><?php it_exchange( 'messages', 'notice' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>
