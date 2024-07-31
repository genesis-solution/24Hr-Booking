<script>
    (function($){
        'use strict';

        if(!$) return;

        $(function(){
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
        });
    })(window.jQuery);
</script>
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${widget_title}
    <!--================ Counters ================-->
    <div class="milenia-counters ${element_classes}">
        <div class="milenia-grid ${grid_element_classes}">
            ${items}
        </div>
    </div>
    <!--================ End of Counters ================-->
</div>
