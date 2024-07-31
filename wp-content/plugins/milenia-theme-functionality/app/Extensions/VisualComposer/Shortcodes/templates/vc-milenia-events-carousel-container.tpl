<script>
    (function($){
        'use strict';

        var $container, $carousel, $dynamicBgElements;

        if(!$) return;

        $(function(){
            // Check all necessary modules have been included
            if(!window.Milenia || !window.Milenia.helpers || !window.Milenia.helpers.owlSettings || !$.fn.owlCarousel || !$.fn.jQueryImagesLoaded) return;

            $container = $('#${container_id}');
            $carousel = $('#${container_id}-carousel');

            if(!$container.length || !$carousel.length) return;

            if(window.appear) {
                appear({
                    elements: function(){
                        return document.querySelectorAll('#${container_id}');
                    },
                    appear: function(element) {
                        var $el = $(element);
                        $el.addClass('milenia-visible').addClass($el.data('animation'));
                    },
                    reappear: false,
                    bounds: -200
                });
            }

            $carousel.jQueryImagesLoaded().then(function(){
                $carousel.owlCarousel(window.Milenia.helpers.owlSettings({
                    items: 1,
                    margin: 0,
                    loop: true
                }));
            });

            if(window.Milenia && window.Milenia.helpers &&  window.Milenia.helpers.dynamicBgImage) {
                $dynamicBgElements = $container.find('[data-bg-image-src]');
                if($dynamicBgElements.length) window.Milenia.helpers.dynamicBgImage($dynamicBgElements);
            }
        });
    })(window.jQuery);
</script>
<div id="${container_id}" class="${container_classes}" data-animation="${css_animation}">
    <div id="${container_id}-carousel" class="milenia-simple-slideshow milenia-simple-slideshow--shortcode milenia-simple-slideshow--events milenia-simple-slideshow--autoplay owl-carousel owl-carousel--nav-edges owl-carousel--nav-inside owl-carousel--nav-hover-white">
        ${items}
    </div>
</div>
