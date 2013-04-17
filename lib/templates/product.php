<?php
/**
 * The default template for displaying a single iThemes Exchange product
 *
 * @since 0.4.0
 */
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">

			<?php it_exchange( 'product.featured-image' ); ?>

			<?php it_exchange( 'product', 'title' ); ?>

			<?php it_exchange( 'product', 'base-price' ); ?>

			<?php it_exchange( 'product', 'description' ); ?>

			<?php it_exchange( 'product', 'extended-description' ); ?>


		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?>
		</div><!-- .entry-content -->

		<footer class="entry-meta">
			<?php edit_post_link( __( 'Edit', 'LION' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
