<!--================ Menu ================-->
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
<ul id="${unique_id}" class="milenia-menu milenia-list--unstyled" data-animation="${css_animation}">
    ${items}
</ul>
<!--================ End of Menu ================-->
