				<footer class="footer">
				
						<?php if(is_user_logged_in()){ ?>
						<div class="container bottom_nav">
							<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-bottom')) ?>
							<div class="clearfix"> </div>
						</div>
						<?php } ?>
						
						<div class="container bottom_nav_flat">
							<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-bottom-flat')) ?>
							<div class="clearfix"> </div>
						</div>
						
						<div class="container">
						<div class="row">
							<div class="col-md-5">
							<div class="logo2">
								   <img src="<?php echo get_template_directory_uri(); ?>/images/logo2.png" alt="WooCommerce Store Manager" title="WooCommerce Store Manager">
							</div>
							<div class="social">
								<ul>
									<li></li>
									<li><a href="https://plus.google.com/111134920224178302833/about" rel="publisher" title="Google+"><img width="30" height="30" src="<?php echo get_template_directory_uri(); ?>/images/soc/google.png" title="Google+" alt="Google+" /></a></li>
									<li><a href="https://www.facebook.com/WoocommerceStoreManager" title="Facebook"><img width="30" height="30"  src="<?php echo get_template_directory_uri(); ?>/images/soc/facebook.png" title="Facebook" alt="Facebook" /></a></li>
									<li><a href="https://twitter.com/WoocommerceSM" title="Twitter"><img width="30" height="30"  src="<?php echo get_template_directory_uri(); ?>/images/soc/twitter.png" title="Twitter" alt="Twitter" /></a></li>
									<li><a href="#" title="YouTube"><img width="30" height="30"  src="<?php echo get_template_directory_uri(); ?>/images/soc/youtube.png" title="Youtube" alt="Youtube" /></a></li>
									<li><a href="http://www.pinterest.com/emagicone/" title="Pinterest"><img width="30" height="30"  src="<?php echo get_template_directory_uri(); ?>/images/soc/pinterest.png" title="Pinterest" alt="Pinterest" /></a></li>
									<li><a href="http://feeds.feedburner.com/WoocommerceStoreManagerBlog" title="Blogger RSS"><img width="30" height="30"  src="<?php echo get_template_directory_uri(); ?>/images/soc/rss.png" title="Blogger RSS" alt="Blogger  RSS" /></a></li>
								</ul>
							</div>
							</div>
							<div class="col-md-7 copy-right" itemprop="brand" itemscope itemtype="http://schema.org/Brand">
							
	
								<p><?php echo date('Y'); ?> Copyright <a href="https://plus.google.com/111134920224178302833" rel="publisher"> <?php bloginfo('name'); ?>. </a>  <?php _e('Powered by', 'html5blank'); ?><span itemprop="name" > eMagicOne</span><img src="<?php echo get_template_directory_uri(); ?>/images/logo30x30.png" width="30" class="ema_logo" height="30" itemprop="logo" title="eMagicOne" alt="eMagicOne" /> 
								</p>
							</div>								
							<div class="clearfix"> </div>
						</div>	
						</div>	
				</footer>	
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
		 <!---- start-smoth-scrolling---->
		 <script type="text/javascript">
			jQuery(document).ready(function($) {
				$(".scroll").click(function(event){		
					event.preventDefault();
					$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
				});
			});
		</script>
		<!----start-top-nav-script---->

		<!----//End-top-nav-script---->
					<a href="#" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>
					<script type="text/javascript"> 
						(function(d, src, c) { var t=d.scripts[d.scripts.length - 1],s=d.createElement('script');s.id='la_x2s6df8d';s.async=true;s.src=src;s.onload=s.onreadystatechange=function(){var rs=this.readyState;if(rs&&(rs!='complete')&&(rs!='loaded')){return;}c(this);};t.parentElement.insertBefore(s,t.nextSibling);})(document, 
						'https://support.emagicone.com/scripts/track.js', 
						function(e){ LiveAgent.createButton('3c07e8cd', e); }); 
					</script>
					<?php wp_footer(); ?>


</body>
</html>