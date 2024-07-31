<!--================ Newsletter ================-->
<script>
    (function($){
        'use strict';

        if(!$) return;

        $(function(){
            var $container = $('#${unique_id}');

            if(!$container.length) return;

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

            if($.MileniaThemeFunctionality && $.MileniaThemeFunctionality.modules && $.MileniaThemeFunctionality.modules.newsletterForm) {
                $.MileniaThemeFunctionality.modules.newsletterForm($container.find('form.widget_wysija'));
            }
        });
    })(window.jQuery);
</script>
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    <div class="milenia-newsletter-inner">
        <div class="milenia-newsletter-title">${widget_title}</div>
        <div class="milenia-newsletter-content">${content}</div>
    </div>
</div>
<!--================ End of Newsletter ================-->
