<script>
    (function($){
        'use strict';

        var $container, $filter, $grid;

        if(!$) return;

        $(function(){
            // Check all necessary modules have been included
            if(!window.Milenia || !window.MileniaIsotopeWrapper || !$.fn.isotope || !$.fn.jQueryImagesLoaded) return;

            $container = $('#${container_id}');
            $grid = $container.find('.milenia-grid--isotope');
            $filter = $('#${filter_id}');

            if(!$container.length || !$grid.length) return;

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

            $container.jQueryImagesLoaded().then(function(){
                MileniaIsotopeWrapper.init($grid, {
                    itemSelector: '.milenia-grid-item',
                    transitionDuration: Milenia.ANIMATIONDURATION
                });
            });

            if(window.Milenia.LinkUnderliner) {
                window.Milenia.LinkUnderliner.init($container.find('a'));
                if($filter.length) {
                    window.Milenia.LinkUnderliner.init($filter.find('a'));
                }
            }
        });
    })(window.jQuery);
</script>
${filter}
<!-- - - - - - - - - - - - - - Isotope Container - - - - - - - - - - - - - - - - -->
<div id="${container_id}" class="milenia-entities ${container_classes} "
    data-animation="${css_animation}">
    <div class="milenia-grid milenia-grid--isotope milenia-grid--shortcode ${grid_classes}"
         data-isotope-layout="${isotope_layout}"
         data-isotope-filter="#${filter_id}"
         data-items-per-page="${data_total_items}">
        <div class="milenia-grid-sizer"></div>
        ${items}
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Isotope Container - - - - - - - - - - - - - - - - -->
