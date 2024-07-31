<!--================ Room types ================-->
<script>
    (function($){
        'use strict';
        if(!$) return;

        $(function(){

            if(window.DOMDfd) {

                window.DOMDfd.done(function(){
                    var $currentContainer;

                    if(!$.fn.MonkeysanTabs) return;

                    $currentContainer = $('#${unique_id}');

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

                    if(window.Milenia && window.Milenia.LinkUnderliner) {
                        window.Milenia.LinkUnderliner.init($currentContainer.find('a'));
                    }
                });


            }
        });
    })(window.jQuery);
</script>
${widget_title}
<!--================ Tabs ================-->
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    <!--================ Tabs Navigation ================-->
    <ul role="tablist" ${aria_label} class="text-center milenia-filter-wrap milenia-tabs-nav milenia-filter milenia-list--unstyled">
        ${tabs_nav_items}
    </ul>
    <!--================ End of Tabs Navigation ================-->

    <!--================ Tabs Container ================-->
    <div class="milenia-tabs-container">
        ${tabs}
    </div>
    <!--================ End of Tabs Container ================-->
</div>
<!--================ End of Tabs ================-->
