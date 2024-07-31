<!--================ Info Boxes ================-->
<script>
    (function($){
        'use strict';

        if(!$) return;

        $(function(){
            var $container = $('#${unique_id}'),
                $dynamicBgElements,
                $carousels;

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

            if(window.Milenia && window.Milenia.LinkUnderliner) {
                window.Milenia.LinkUnderliner.init($container.find('a'));
            }

            if(window.Milenia && window.Milenia.helpers &&  window.Milenia.helpers.dynamicBgImage) {
                $dynamicBgElements = $container.find('[data-bg-image-src]');
                if($dynamicBgElements.length) window.Milenia.helpers.dynamicBgImage($dynamicBgElements);
            }

            if($.fn.owlCarousel) {
                $carousels = $container.find('.milenia-simple-slideshow--shortcode');

                if($carousels.length) {
                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.owlSettings) {

                        $carousels.jQueryImagesLoaded().then(function(){
                                $carousels.each(function(i,e) {
                                    $(e).owlCarousel(window.Milenia.helpers.owlSettings({
                                        margin: 1,
                                        loop: true,
                                        autoplay: $(e).hasClass('milenia-simple-slideshow--autoplay')
                                    }));
                                });
                        });

                    }
                    else {
                        $carousels.owlCarousel({
                            items: 1
                        });
                    }
                }
            }
        });
    })(window.jQuery);
</script>
${widget_title}
<div id="${unique_id}" class="milenia-entities ${container_classes}" data-animation="${css_animation}">
    <div class="milenia-grid ${grid_classes}">
        ${items}
    </div>
</div>
<!--================ End of Info Boxes ================-->
