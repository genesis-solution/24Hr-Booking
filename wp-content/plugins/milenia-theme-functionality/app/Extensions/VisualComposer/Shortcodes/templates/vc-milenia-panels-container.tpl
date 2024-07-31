<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${widget_title}
    <!-- - - - - - - - - - - - - - Accordion - - - - - - - - - - - - - - - - -->
    <dl class="milenia-panels ${element_classes}">
        ${items}
    </dl>
    <script>
        (function($){
            'use strict';
            if(!$) return;

            $(function(){

                var $currentContainer;

                if(!$.fn.MonkeysanAccordion) return;

                $currentContainer = $('#${unique_id} .milenia-panels');

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

				$currentContainer.MonkeysanAccordion({
                    toggle: !!${is_toggle},
					easing: '${panels_animation_easing}',
					speed: ${panels_animation_duration},
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

                if(window.Milenia && window.Milenia.LinkUnderliner) {
                    window.Milenia.LinkUnderliner.init($currentContainer.find('a'));
                }
            });
        })(window.jQuery);
    </script>
    <!-- - - - - - - - - - - - - - End of Accordion - - - - - - - - - - - - - - - - -->
</div>
