    /**
    * @author    JoomShaper http://www.joomshaper.com
    * @copyright Copyright (C) 2010 - 2013 JoomShaper
    * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
    */
	;(function($){ 
	$.fn.blogganiSlider = function(options) {  

	var defaults = {
		interval : 5000,
		autoplay : true,
		prev : '.controller-prev',	
		next : '.controller-next'	
	};  
	
	var options = $.extend(defaults, options);  

		return this.each(function() {  
			var container = $(this);
			var slides = container.find('>.slider-item');
			var totalSlides = slides.length;
			var currentIndex = 0;
			var slideShowInterval;
			$(slides).hide().removeClass('active');
			$(slides).first().addClass('active').fadeIn();
			
			//height
			container.css('height', $(slides).first().height());
			
			var nextIndex = function(){
				currentIndex++;
				if(currentIndex>=totalSlides) currentIndex=0;
			};
			
			var prevIndex = function(){
				currentIndex--;
				if(currentIndex<0) currentIndex=totalSlides-1;
			};
		
			var activateInterval = function(){
				if(options.autoplay){
					slideShowInterval = setInterval(function(){
						nextIndex();
						slide(currentIndex);
					}, options.interval);
				}
			}
			
			activateInterval();
			
			var slide = function($i){
				$(slides).fadeOut().removeClass('active');
				$(slides.get($i)).addClass('active').fadeIn();
			}
			
			var resize = function() {
				$(window).on('resize',function(){
					if($(slides).hasClass('active')){
						container.css('height',$(slides).height());
					}
				});
			}
			
			$(options.next).on('click',function(){
				nextIndex();
				slide(currentIndex);
				clearInterval(slideShowInterval);
				activateInterval();
			});
			
			$(options.prev).on('click',function(){
				prevIndex();
				slide(currentIndex);
				clearInterval(slideShowInterval);
				activateInterval();
			});

			resize();
			
		});  
	};  
	
})(jQuery);