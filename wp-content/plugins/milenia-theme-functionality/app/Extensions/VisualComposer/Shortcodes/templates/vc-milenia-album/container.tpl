<!--================ Album ================-->
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
<figure id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${cover}
    <div class="milenia-action-buttons">
        ${action_images}
        ${action_video}
    </div>
</figure>
<!--================ End of Album ================-->
