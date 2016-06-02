<?php get_header(); ?>
<?php 
// WP_Query arguments
$args = array (
	'post_type'              => 'page',
	'order'                  => 'ASC',
	'orderby'                => 'menu_order',
	'meta_query'             => array(
		array(
			'key'       => 'hide',
			'value'     => '1',
			'compare'   => '!='
		),
	)
);

// The Query
$query = new WP_Query( $args );
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		?> 
		<?php if(get_field('page_id') == 'shop'  ) { ?>
							
				<!-- plans -->
						<div class="plans">
							<!-- container -->
							
							<div class="container">
							<div class="plans-header">
								<h3 > <?php the_title(); ?></h3>
								<?php if(get_field('subpage')) { ?> <p> <?php the_field('subpage'); ?></p> <?php } else { ?> <?php } ?>
							</div>
			
								<div class="plans-grids row">
								
								<?php
				$post_objects = get_field('pricing_services','option');
				global  $woocommerce;
				if( $post_objects ): ?>
				<?php $rows_count = count(array_keys(get_field('pricing_services','option')));  ?>
					<?php foreach( $post_objects as $post): // variable must be called $post (IMPORTANT) ?>
						<?php setup_postdata($post); ?>
						<div class="plans-grid col-md-<?php echo  12 / $rows_count  ?>">
						
							<div class="plan-text">
										<?php if(get_post_meta( get_the_ID(), '_regular_price', true) and !get_post_meta( get_the_ID(), '_sale_price', true)){ ?>
											<div class="price"><?php echo get_woocommerce_currency_symbol(); ?> <span><?php echo get_post_meta( get_the_ID(), '_regular_price', true); ?></span></div>
											
										<?php } elseif(!get_post_meta( get_the_ID(), '_regular_price', true) and !get_post_meta( get_the_ID(), '_sale_price', true)) { ?>
										<div class="price free"> Free </div> 
										<?php } else {  ?>	
																	<div class="price" ><?php echo get_woocommerce_currency_symbol(); ?> <span class="regular"><?php echo get_post_meta( get_the_ID(), '_regular_price', true); ?></span> <span class="sale"><?php echo get_post_meta( get_the_ID(), '_sale_price', true); ?></span></div>
										<?php } ?>
							</div>
							<div class="plans-grid-bottom">
										
											<div class="plans-grid-head" <?php if(get_field('hdr_color')): ?> style="background:<?php the_field('hdr_color'); ?>" <?php endif; ?> >
										
												<div class="pri_heading"> <?php the_title(); ?> </div>
										
												<?php if(get_field('woo_subheading')): ?>
												<span><?php the_field('woo_subheading'); ?></span>
												<?php endif; ?>
											</div>

										<?php if(get_field('woo_features')): ?> 
													<?php while ( have_rows('woo_features') ) : the_row();  ?>
														<?php if(get_sub_field('woo_fea')): ?>
															<p><?php the_sub_field('woo_fea'); ?></p>
														<?php endif; ?>
													<?php endwhile; ?>
										<?php endif; ?>
										
												<?php  $add_to_cart = do_shortcode('[add_to_cart_url id="'.get_the_ID().'"]'); ?>
<!--												--><?php //if( is_user_logged_in()  ) { ?>
												<div class="plan-button" id="button_add_to_cart_<?php echo get_the_ID() ?>" ><a href="<?php echo $add_to_cart ?>">Buy Now</a></div> 
<!--													--><?php // } else { ?>
<!--												<div class="plan-button"><a href="#">Coming soon</a></div> -->
<!--												--><?php // }  ?>
							</div>
						
						</div>
					<?php endforeach; ?>
				 
					<?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
				<?php endif;?>			
									<div class="clearfix"> </div>
								</div>
							</div>
							<!-- //container -->
						</div>
						
		
		<?php } elseif(get_field('page_id') == 'form' ) { ?>
		  <article id="form" class="feedback">
	 	   		<div class="container">
	 	   		<div class="feed-head">
	 	 			<h3 id="betaform"><?php the_title(); ?> </h3>
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
		<?php } else {
		?> 
		<article id="<?php the_field('page_id'); ?>"  class="<?php the_field('page_id'); ?>">
				<div class="container">
					<div class="<?php the_field('page_id'); ?>-head" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h3><?php the_title(); ?></h3>
						<?php if(get_field('subpage')) { ?> <span> <?php the_field('subpage'); ?></span> <?php } else { ?> <br /> <?php } ?>
					
					<?php the_content(); ?>
								<?php edit_post_link(); ?>
								
					</div>
				</div>
		</article>
		<?php } ?>
		
		
		<?php
	}
} else {
	// no posts found
}

// Restore original Post Data
wp_reset_postdata();
?>
</div>
<?php get_footer(); ?>
