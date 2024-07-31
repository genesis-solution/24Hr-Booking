<!--================ Icon Boxes ================-->
<script>
    (function($){
        'use strict';

        if(!$) return;

        $(function(){
            var $container = $('#${unique_id}');

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
<div id="${unique_id}" class="milenia-icon-boxes ${container_classes}" data-animation="${css_animation}">
    <div class="milenia-grid ${grid_classes}">
        ${items}
    </div>
</div>
<!--================ Icon Boxes ================-->
