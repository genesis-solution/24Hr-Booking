<!-- - - - - - - - - - - - - - Team Members - - - - - - - - - - - - - - - - -->
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${widget_title}
    <div class="milenia-grid ${team_members_element_classes}">
        ${items}
    </div>
</div>
<script>
    (function($){
        'use strict';
        if(!$) return;

        $(function(){
            var $currentContainer = $('#${unique_id}');
            if(!$currentContainer.length) return;

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

            if(!window.Milenia || !window.Milenia.LinkUnderliner || !window.Milenia.LinkUnderliner.init) return;

            window.Milenia.LinkUnderliner.init($currentContainer.find('a'));
        });
    })(window.jQuery);
</script>
<!-- - - - - - - - - - - - - - End of Team Members - - - - - - - - - - - - - - - - -->
