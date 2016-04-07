<?php /* Template Name: Card */  ?>

<?php get_header(); ?>
		  <article id="card" class="card">
	 	   		<div class="container">
	 	   		<div class="card-head">
	 	 			<h3 id="card"><?php the_title(); ?> </h3>
 					<?php if(get_field('subpage')) { ?> <p> <?php the_field('subpage'); ?></p> <?php } else { ?> <br /> <?php } ?>
	 	 		</div>
	 	 		<div class="feed-form">
				 	 			<div class="col-md-12 form-form">
									<?php the_content(); ?>
								<?php edit_post_link(); ?>
									<div class="clearfix"> </div>
								</div>
				</div>
				</div>
		</article>

<?php 
wp_reset_postdata();
?>

<?php get_footer(); ?>
