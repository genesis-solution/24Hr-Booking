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
<!-- - - - - - - - - - - - - - Dropcap - - - - - - - - - - - - - - - - -->
<div id="${unique_id}" class="milenia-dropcap ${container_classes}" data-animation="${css_animation}">
    ${content}
</div>
<!-- - - - - - - - - - - - - - End of Dropcap - - - - - - - - - - - - - - - - -->
