<?php /* Template Name: product */  ?>
<div class="container">
<ul class="products">
	<?php
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 12
			);
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) : $loop->the_post();
				woocommerce_get_template_part( 'content', 'product' );
			endwhile;
		} else {
			echo __( 'No products found' );
		}
		wp_reset_postdata();
	?>
</ul><!--/.products-->
</div>