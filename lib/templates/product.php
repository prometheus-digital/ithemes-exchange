<?php
/**
 * The default template for displaying a single iThemes Exchange product
 *
 * @since 0.4.0
 */
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">

			<?php if ( it_exchange( 'product', 'has-featured-image' ) ) : ?>
				<p><strong>Featured Image</strong><br /><?php it_exchange( 'product.featured-image' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-title' ) ) : ?>
				<p><strong>Title</strong><br /><?php it_exchange( 'product', 'title' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-base-price' ) ) : ?>
				<p><strong>Base Price</strong><br /><?php it_exchange( 'product', 'base-price' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-description' ) ) : ?>
				<p><strong>Description</strong><br /><?php it_exchange( 'product', 'description' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-extended-description' ) ) : ?>
				<p><strong>Extended Description</strong><br /><?php it_exchange( 'product', 'extended-description' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-downloads' ) ) : ?>
				<p><strong>Downloads</strong><br />
				<?php while( it_exchange( 'product', 'downloads' ) ): ?>
					<em>Download</em>: <?php it_exchange( 'download', 'title' ); ?> | 
					<?php it_exchange( 'download', 'limit' ); ?> | <?php it_exchange( 'download', 'expiration' ); ?>
					<br />
				<?php endwhile; ?>
				</p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-inventory' ) ) : ?>
				<p><strong>Inventory</strong><br /><?php it_exchange( 'product', 'inventory' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-quantity' ) ) : ?>
				<p><strong>Max Quantity Per Purcahse</strong><br /><?php it_exchange( 'product', 'quantity' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-availability', 'type=start' ) ) : ?>
				<p><strong>Product Start Availability</strong><br /><?php it_exchange( 'product', 'availability', 'type=start' ); ?></p>
			<?php endif; ?>

			<?php if ( it_exchange( 'product', 'has-availability', 'type=end' ) ) : ?>
				<p><strong>Product End Availability</strong><br /><?php it_exchange( 'product', 'availability', 'type=end' ); ?></p>
			<?php endif; ?>

		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?>
		</div><!-- .entry-content -->

		<footer class="entry-meta">
			<?php edit_post_link( __( 'Edit', 'LION' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
