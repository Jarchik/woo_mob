<?php get_header(); ?>

	<article class="row" role="main">
		<!-- section -->
		<section class="container">

			<h1><?php _e( 'Archives', 'html5blank' ); ?></h1>

			<?php get_template_part('loop'); ?>

			<?php get_template_part('pagination'); ?>

		</section>
		<!-- /section -->
	</article>

<?php get_footer(); ?>
