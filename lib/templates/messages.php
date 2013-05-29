<?php
/**
 * This file contains the markup for error and
 * notice messages.
*/
/*
	NOTE These are added merely for testing purposes and need to be removed when finished.
*/
// it_exchange_add_message( 'error', 'this is an error!' );
// it_exchange_add_message( 'notice', 'This is a notice!' );
?>
<?php if ( it_exchange( 'messages', 'has-errors' ) ) : ?>
	<ul class="messages errors">
		<?php while ( it_exchange( 'messages', 'errors' ) ) : ?>
			<li><?php it_exchange( 'messages', 'error' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>

<?php if ( it_exchange( 'messages', 'has-notices' ) ) : ?>
	<ul class="messages notices">
		<?php while ( it_exchange( 'messages', 'notices' ) ) : ?>
			<li><?php it_exchange( 'messages', 'notice' ); ?></li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>