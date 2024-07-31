<script>
    (function($){
        'use strict';

        var $container, $grid, $filter, $carousels, $dynamicBgElements;

        if(!$) return;

        $(function(){
            // Check all necessary modules have been included

            $container = $('#${container_id}');
            if(!$container.length) return;
            $grid = $container.find('.milenia-grid--isotope');
            if(!$grid.length) return;
            $filter = $('#${filter_id}');
            $carousels = $container.find('.milenia-simple-slideshow--shortcode');

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

            if(window.Milenia && window.MileniaIsotopeWrapper && $.fn.isotope && $.fn.jQueryImagesLoaded) {
                $container.jQueryImagesLoaded().then(function() {
                    MileniaIsotopeWrapper.init($grid, {
                        itemSelector: '.milenia-grid-item',
                        transitionDuration: Milenia.ANIMATIONDURATION
                    });
                });
            }

            if(window.Milenia && window.Milenia.LinkUnderliner) {
                window.Milenia.LinkUnderliner.init($container.find('a'));
                if($filter.length) {
                    window.Milenia.LinkUnderliner.init($filter.find('a'));
                }
            }

            if(window.Milenia && window.Milenia.helpers &&  window.Milenia.helpers.dynamicBgImage) {
                $dynamicBgElements = $container.find('[data-bg-image-src]');
                if($dynamicBgElements.length) window.Milenia.helpers.dynamicBgImage($dynamicBgElements);
            }

            if($carousels.length && window.Milenia && window.Milenia.helpers && window.Milenia.helpers.owlSettings && $.fn.owlCarousel) {
                $carousels.each(function(i,e) {
                    $(e).owlCarousel(window.Milenia.helpers.owlSettings({
                        margin: 1,
                        loop: true,
                        autoplay: $(e).hasClass('milenia-simple-slideshow--autoplay')
                    }));
                });
            }
            else if($carousels.length && $.fn.owlCarousel) {
                $carousels.owlCarousel();
            }
        });
    })(window.jQuery);
</script>

${filter}
<!-- - - - - - - - - - - - - - Blog Posts - - - - - - - - - - - - - - - - -->
<div id="${container_id}" class="milenia-entities ${container_classes}" data-animation="${css_animation}">
    <div class="milenia-grid milenia-grid--isotope milenia-grid--shortcode ${grid_classes}"
         data-isotope-filter="#${filter_id}"
         data-items-per-page="${data_total_items}"
         data-isotope-layout="${isotope_layout}">
        <div class="milenia-grid-sizer"></div>
        ${items}
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Blog Posts - - - - - - - - - - - - - - - - -->
