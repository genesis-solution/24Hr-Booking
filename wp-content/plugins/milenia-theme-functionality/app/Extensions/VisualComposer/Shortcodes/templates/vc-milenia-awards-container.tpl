<!-- - - - - - - - - - - - - - Awards - - - - - - - - - - - - - - - - -->
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
${widget_title}
<div id="${unique_id}" class="milenia-awards ${container_classes}" data-animation="${css_animation}">
    <div class="milenia-grid ${grid_classes}">
        ${items}
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Awards - - - - - - - - - - - - - - - - -->
