<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${widget_title}

    <!-- - - - - - - - - - - - - - Tabs - - - - - - - - - - - - - - - - -->
    <div id="${unique_id}-tabs" class="milenia-tabs ${tabs_classes}">
        <!-- - - - - - - - - - - - - - Tabs Navigation - - - - - - - - - - - - - - - - -->
        <div role="tablist" class="milenia-tabs-nav">
            ${nav_items}
        </div>
        <!-- - - - - - - - - - - - - - End of Tabs Navigation - - - - - - - - - - - - - - - - -->

        <!-- - - - - - - - - - - - - - Tabs Content - - - - - - - - - - - - - - - - -->
        <div class="milenia-tabs-container">
            ${items}
        </div>
        <!-- - - - - - - - - - - - - - End of Tabs Content - - - - - - - - - - - - - - - - -->
    </div>
    <script>
        (function($){
            'use strict';
            if(!$) return;

            $(function(){

                var $currentContainer;

                if(!$.fn.MonkeysanTabs) return;

                $currentContainer = $('#${unique_id}-tabs');

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

                $currentContainer.MonkeysanTabs({
                    speed: ${panels_animation_duration},
                    easing: '${panels_animation_easing}',
                    cssPrefix: 'milenia-',
                    afterOpen: function() {
                        if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.updateGlobalNiceScroll) {
                            window.Milenia.helpers.updateGlobalNiceScroll();
                        }
                    },
                    afterClose: function() {
                        if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.updateGlobalNiceScroll) {
                            window.Milenia.helpers.updateGlobalNiceScroll();
                        }
                    }
                });
            });
        })(window.jQuery);

    </script>
    <!-- - - - - - - - - - - - - - End of Tabs - - - - - - - - - - - - - - - - -->
</div>
