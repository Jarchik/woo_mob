<?php get_header(); ?>

		
		
		
		<div id="<?php the_field('page_id'); ?>"  class="<?php the_field('page_id'); ?>">
				<div class="container">
					<div class="about-head" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h3><?php _e( 'Page not found', 'html5blank' );  ?></h3>
						<span> <a href="<?php echo home_url(); ?>"><?php _e( 'Return home?', 'html5blank' ); ?></a>  </span>
				
								
					</div>
				</div>
		</div>
		
		<!-- /section -->


<?php get_footer(); ?>
