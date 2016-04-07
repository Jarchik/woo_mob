<?php get_header(); ?>

	<article class="row" role="main">
		<!-- section -->
		<section class="container">

			<span class="hd1"><?php echo sprintf( __( '%s Search Results for ', 'html5blank' ), $wp_query->found_posts ); echo get_search_query(); ?></span>

			<?php get_template_part('loop'); ?>

			<?php get_template_part('pagination'); ?>

		</section>
		<!-- /section -->
	</article>


<?php get_footer(); ?>
