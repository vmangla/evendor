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
				auto: 3,
				wrap: 'circular'
			});
			
			jQuery('#mycarouse2').jcarousel({
				scroll: 1,
				auto: 3.2,
				wrap: 'circular'
			});
			
			jQuery('#mycarouse3').jcarousel({
				scroll: 1,
				auto: 3.4,
				wrap: 'circular'
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


		