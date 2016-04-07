(function ($, root, undefined) {	
	$(function () {
		
		'use strict';
		
		// DOM ready, take it away
		
				var pull 	= $('#pull');
				var menu 		= $('nav ul');
				var menuHeight	= menu.height();
				
				$(pull).on('click', function(e) {
					e.preventDefault();
					menu.slideToggle();
				});
				
				$(window).resize(function(){
					var w = $(window).width();
					if(w > 320 && menu.is(':hidden')) {
						menu.removeAttr('style');
					}
				});
				
				$().UItoTop({ easingType: 'easeOutQuart' });

				$("#slider4").responsiveSlides({
			        auto: true,
			        pager: true,
			        nav: true,
			        speed: 1000,
			        namespace: "callbacks",
			        before: function () {
			          $('.events').append("<li>before event fired.</li>");
			        },
			        after: function () {
			          $('.events').append("<li>after event fired.</li>");
			        }
			      });		
				  
/*----amination-----------*/
//

//Add Hover effect to menus
jQuery('ul.top-nav li.dropdown').hover(function() {
  jQuery(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn();
}, function() {
  jQuery(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut();
});

  var $blocks = jQuery('.notViewed.animBlock');
  var $window = jQuery(window);
  $window.on('scroll', function(e){
    $blocks.each(function(i,elem){
      if(jQuery(this).hasClass('viewed')) 
        return;
        
      isScrolledIntoView(jQuery(this));
    });
  });
	
	function isScrolledIntoView(elem) {
  var docViewTop = jQuery(window).scrollTop() ;
  var docViewBottom = docViewTop + jQuery(window).height();
  var elemOffset = 500;
  
  if(elem.data('offset') != undefined) {
    elemOffset = elem.data('offset');
  }
  var elemTop = jQuery(elem).offset().top;
  var elemBottom = elemTop + jQuery(elem).height();
  
  if(elemOffset != 0) { // custom offset is updated based on scrolling direction
    if(docViewTop - elemTop >= 0) {
      // scrolling up from bottom
      elemTop = jQuery(elem).offset().top + elemOffset;
    } else {
      // scrolling down from top
      elemBottom = elemTop + jQuery(elem).height() - elemOffset
    }
  }
  
  if((elemBottom <= docViewBottom) && (elemTop >= docViewTop) || (jQuery(window).width() <= 800 ) ) {
    // once an element is visible exchange the classes
    jQuery(elem).removeClass('notViewed').addClass('viewed');
    
    var animElemsLeft = jQuery('.notViewed.animBlock').length;
    if(animElemsLeft == 0){
      // with no animated elements left debind the scroll event
      //jQuery(window).off('scroll');
    }
  }
}


		
	});
								


								
})(jQuery, this);
