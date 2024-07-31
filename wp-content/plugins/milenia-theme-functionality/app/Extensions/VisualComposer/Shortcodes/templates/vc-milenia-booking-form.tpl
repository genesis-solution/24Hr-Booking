<!--================ Booking Form ================-->
<script>
    (function($){
        'use strict';

        if(!$) return;

        var $container, $selects, $formInner, title;

        $(function(){
            $container = $('#${unique_id}');

            if(!$container.length) return;

            $selects = $container.find('.milenia-custom-select');
            title = '${widget_title}';

            if($selects.length && $.fn.MadCustomSelect) {
                $selects.MadCustomSelect({
                    cssPrefix: 'milenia-'
                });
            }

            if(title && ($container.find('.milenia-booking-form-wrapper--v2').length || $container.find('.milenia-booking-form-wrapper--v4').length))
            {
                $formInner = $container.find('.milenia-booking-form-inner-wrapper .form-group');

                if($formInner.length)
                {
                    $formInner.prepend('<div class="form-col form-col--title"><div class="form-control">'+title+'</div></div>');
                }
            }


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
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    ${form}
</div>
<!--================ End of Booking Form ================-->
