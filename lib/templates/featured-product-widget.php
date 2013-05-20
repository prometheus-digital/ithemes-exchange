<?php
/**
 * The default template for displaying a single iThemes Exchange product
 *
 * @since 0.4.0
 */
?>
<?php if ( it_exchange( 'product', 'found' ) ) : ?>
	<?php if ( it_exchange( 'product', 'has-featured-image' ) ) : ?>
		<?php it_exchange( 'product', 'featured-image' ); ?></p>
	<?php endif; ?>

	<?php if ( it_exchange( 'product', 'has-title' ) ) : ?>
		<h4><?php it_exchange( 'product', 'title', 'format=data' ); ?></h4>
	<?php endif; ?>

	<?php if ( it_exchange( 'product', 'has-description' ) ) : ?>
		<p><?php it_exchange( 'product', 'description' ); ?></p>
	<?php endif; ?>

	<?php if ( it_exchange( 'product', 'has-permalink' ) ) : ?>
		<p><a href="<?php it_exchange( 'product', 'permalink', 'format=url' ); ?>"><?php _e( 'View Product', 'LION' ); ?></a></p>
	<?php endif; ?>
<?php else : // Shouldn't happen ?>
	<p><?php _e( 'Product not found.', 'LION' ); ?></p>
<?php endif; ?>
