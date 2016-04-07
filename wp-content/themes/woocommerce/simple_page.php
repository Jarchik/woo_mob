<?php /* Template Name:Simple page */  ?>

<?php get_header(); ?>
		  <article id="page" class="page">
	 	   		<div class="container">
	 	   		<div class="page-head">
	 	 			<h2 id="page-title"><?php the_title(); ?> </h3>
 					<?php if(get_field('subpage')) { ?> <p> <?php the_field('subpage'); ?></p> <?php } else { ?> <br /> <?php } ?>
	 	 		</div>
				 	 			<div class="col-md-12">
									<?php the_content(); ?>
								<?php edit_post_link(); ?>
									<div class="clearfix"> </div>
								</div>
				
				</div>
		</article>

<?php 
wp_reset_postdata();
?>

<?php get_footer(); ?>
