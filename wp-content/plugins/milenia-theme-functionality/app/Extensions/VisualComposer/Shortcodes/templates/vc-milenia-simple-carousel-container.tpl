<!--================ Carousel ================-->
${widget_title}
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${items}
</div>
<script>
	(function($){
		'use strict';

		var $container;

		if(!$) return;

		$(window).load(function() {
			// Check all necessary modules have been included
			if(!window.Milenia || !window.Milenia.helpers || !window.Milenia.helpers.owlSettings || !$.fn.owlCarousel || !$.fn.jQueryImagesLoaded) return;

			$container = $('#${unique_id}');

			if(!$container.length) return;

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
					$container.owlCarousel(window.Milenia.helpers.owlSettings(${carousel_options}));
				}, 0);
			});
		});

	})(window.jQuery);
</script>
<!--================ End of Carousel ================-->
