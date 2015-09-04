function mycarousel_initCallback(carousel)
{
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
        carousel.startAuto(0);
    });

    carousel.buttonPrev.bind('click', function() {
        carousel.startAuto(0);
    });

    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
        carousel.stopAuto();
    }, function() {
        carousel.startAuto();
    });
};
		jQuery(document).ready(function() {
			jQuery('#mycarousel').jcarousel({
			   scroll: 1,
				auto: 5,
				wrap: 'circular',
				initCallback: mycarousel_initCallback
			});

			
			jQuery('#mycarouse2').jcarousel({
				scroll: 1,
				auto: 5.2,
				wrap: 'circular',
				initCallback: mycarousel_initCallback
			});
			
			jQuery('#mycarouse3').jcarousel({
				scroll: 1,
				auto: 5.4,
				wrap: 'circular',
				initCallback: mycarousel_initCallback
			});
		});
		
		/*$(document).ready(function(){
			$('.slidyContainer').slidy({
			});
		});
		*/
		
		$(document).load(function(){
      SyntaxHighlighter.all();
    });
    
	$(window).load(function(){
      $('.flexslider').flexslider({
        animation: "slide",
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });




		
