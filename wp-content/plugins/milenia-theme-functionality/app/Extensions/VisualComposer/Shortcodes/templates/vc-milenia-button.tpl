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
<div id="${unique_id}" class="milenia-button-container ${container_classes}" data-animation="${css_animation}">
    <a href="${button_url}"
       role="button"
       class="milenia-btn ${button_classes}"
       title="${button_title}"
       target="${button_target}"
       rel="${button_rel}"
       style="${button_style}">${button_text}</a>
</div>
