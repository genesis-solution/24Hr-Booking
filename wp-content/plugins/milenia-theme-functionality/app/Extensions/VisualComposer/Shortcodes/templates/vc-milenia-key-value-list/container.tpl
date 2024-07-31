<!--================ Key-value list ================-->
<script>
    (function($){
        'use strict';

        var $container, $grid, $filter, $carousels, $dynamicBgElements;

        if(!$) return;

        $(function(){
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

            if(window.Milenia && window.Milenia.LinkUnderliner) {
                window.Milenia.LinkUnderliner.init($container.find('a'));
            }
        });
    })(window.jQuery);
</script>

${widget_title}
<ul id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${items}
</ul>
<!--================ End of Key-value list ================-->
