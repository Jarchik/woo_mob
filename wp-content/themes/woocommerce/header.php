<!DOCTYPE HTML>
<html <?php language_attributes(); ?> >
	<head>
		<title><?php wp_title(''); ?></title>
		<link rel="dns-prefetch" href="//fonts.googleapis.com">
		<link rel="dns-prefetch" href="//google-analytics.com">
		<link rel="dns-prefetch" href="//www.google-analytics.com">
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php wp_head(); ?>
		 <link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">
	</head>
	<body <?php body_class(); ?> >

<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5RMS2F"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5RMS2F');</script>
<!-- End Google Tag Manager -->
	
		<header <?php if(is_front_page()) { ?> class="main-header" <?php } else { ?> class="short-header" <?php } ?> itemscope itemtype="http://schema.org/Product">
			<div id="home" class="header">
					<div class="top-header">
						<div class="container">
							 <div class="logo col-md-4">
								<a href="<?php echo home_url(); ?>">
								   <img width="278" height="62" src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="<?php wp_title(''); ?>">
								 </a>  
							</div>
							<div class="col-md-8">
							<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-top')) ?>
							</div>
						<div class="clearfix"> </div>
					</div>
				</div>
			</div>
	
			<?php if(is_front_page()) {?>
			    <div  id="top" class="callbacks_container">
				   <ul class="rslides" id="slider4">
				<?php
				if( have_rows('slider_content','option') ):
						while ( have_rows('slider_content','option') ) : the_row();
				?>
					<li>
			        	<div class="tittle-head">
			        		<h1 itemprop="name" ><?php the_sub_field('heading','option'); ?></h1>
			          		<?php the_sub_field('subheadings','option'); ?>
			          		<div class="learn-button">
			          			<a id="button_header_download" class="slide-btn" href="<?php the_sub_field('button_link','option'); ?>"><?php the_sub_field('button','option'); ?></a>

			          		</div>
							<div class="beta_link">
								<a id="button_header_betatest"  class="slider-link scroll" href="#betaform" >Become a Beta Tester</a>
							</div>
							
			          	</div>
			        </li>			
				<?php
						endwhile;
				else :

				endif;

?>
				
			      </ul>
			    </div> 
					<?php }?>
			    <div class="clearfix"> </div>
		<div class="nav-wrapper">
			<div class="container">
				 <nav class="top-nav">
							
									<?php// if(is_front_page()) { ?> 
										<?php html5blank_nav() ?>
									<?php //} else { ?>
										<?php //nohome_nav() ?>
									<?php// } ?> 
									<a href="#" id="pull"><img src="<?php echo get_template_directory_uri(); ?>/images/menu-icon.png" title="menu" /></a>
					<?php global $woocommerce; 
						if( $woocommerce->cart->get_cart_contents_count() > 0 ) {
							include('cart.php');
				} ?>				
				</nav>
			</div>	
		</div>
		</header>