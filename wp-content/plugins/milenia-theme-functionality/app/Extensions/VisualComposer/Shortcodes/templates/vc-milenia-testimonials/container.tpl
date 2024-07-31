<!--================ Testimonials ================-->
<script>
    (function($){
        'use strict';

        var $container, $grid, $bgImages, $noGridCarousel;

        if(!$) return;

        $(function() {
            // Check all necessary modules have been included
            if(!window.Milenia || !$.fn.jQueryImagesLoaded) return;

            $container = $('#${unique_id}');
            $bgImages = $container.find('[data-bg-image-src]');
            $grid = $container.find('.milenia-grid.owl-carousel');
            $noGridCarousel = $container.find('.milenia-testimonials-inner.owl-carousel');

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

            if ( $grid.length && Milenia.helpers && Milenia.helpers.gridOwl ) {

	            var autoplay_carousel = $container.data('autoplay'),
	                autoplay_timeout = $container.data('autoplaytimeout') ? $container.data('autoplaytimeout') : 3000;

                $container.jQueryImagesLoaded().then(function(){
                    Milenia.helpers.gridOwl.extendConfigFor('#${unique_id}', {
                        nav: false,
                        dots: true,
                        startPosition: 1,
	                    autoplay: autoplay_carousel,
	                    autoplayTimeout: autoplay_timeout,
                        loop: true
                    });

                    Milenia.helpers.gridOwl.add($grid);
                });
            } else if( $noGridCarousel.length && Milenia.helpers && Milenia.helpers.owlSettings && $.fn.owlCarousel ) {
                $noGridCarousel.owlCarousel(Milenia.helpers.owlSettings({
                    margin: 0,
                    autoplay: true,
                    loop: true,
                    nav: false,
                    dots: true
                }));
            }

            if($bgImages.length && Milenia.helpers && Milenia.helpers.dynamicBgImage) {
                Milenia.helpers.dynamicBgImage($bgImages);
            }

            if(Milenia.helpers.rating) {
                Milenia.helpers.rating($('#${unique_id} .milenia-rating:not(.milenia-rating--independent)'), {
                    topLevelElements: null,
                    bottomLevelElements: '<i class="icon icon-star"></i>'
                });
            }

            if(window.Milenia.LinkUnderliner) {
                window.Milenia.LinkUnderliner.init($container.find('a'));
            }
        });
    })(window.jQuery);
</script>
${widget_title}
<div id="${unique_id}" class="${container_classes}" data-autoplay="${autoplay}" data-autoplaytimeout="${autoplaytimeout}" data-animation="${css_animation}" data-css-row="${data_row_css}">
    <div class="${grid_classes}">
        ${items}
    </div>
</div>
<!--================ End of Testimonials ================-->
