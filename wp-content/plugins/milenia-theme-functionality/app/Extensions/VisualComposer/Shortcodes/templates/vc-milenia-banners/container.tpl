<!--================ Banners ================-->
<script>
    (function($){
        'use strict';

        var $container, $grid, $colorizer;

        if(!$) return;

        $(window).load(function() {

	        if(!window.Milenia || !window.MileniaIsotopeWrapper || !$.fn.isotope || !$.fn.jQueryImagesLoaded) return;

	        $container = $('#${unique_id}');
	        $grid = $container.find('.milenia-grid--isotope');

	        if(!$container.length || !$grid.length) return;

	        if(window.appear) {
		        appear({
			        elements: function(){
				        return document.querySelectorAll('#${unique_id}');
			        },
			        appear: function(element) {
				        var $el = $(element);
				        $el.addClass('milenia-visible').addClass($el.data('animation'));
			        },
			        reappear: false,
			        bounds: -200
		        });
	        }

	        $container.jQueryImagesLoaded().then(function(){
		        setTimeout(function() {
			        MileniaIsotopeWrapper.init($grid, {
				        itemSelector: '.milenia-grid-item',
				        transitionDuration: Milenia.ANIMATIONDURATION
			        });
		        }, 0)
	        });

	        if(window.Milenia.LinkUnderliner) {
		        window.Milenia.LinkUnderliner.init($container.find('a'));
	        }

	        $colorizer = $container.find('[class*="milenia-colorizer-"]');

	        if($colorizer.length) {
		        if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.Colorizer) window.Milenia.helpers.Colorizer.init($colorizer);
	        }

        });

//        $(function(){
            // Check all necessary modules have been included

//        });

    })(window.jQuery);
</script>
${widget_title}
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    <div data-isotope-layout="masonry" class="${grid_classes}"><div class="milenia-grid-sizer"></div>${items}</div>
</div>
<!--================ End of Banners ================-->
