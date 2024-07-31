<!-- - - - - - - - - - - - - - Alert Box - - - - - - - - - - - - - - - - -->
<div id="${unique_id}" role="alert" class="milenia-alert-box ${container_classes}" data-animation="${css_animation}">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close">${alert_close_btn_text}</button>
        ${alert_box_text}
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Alert Box - - - - - - - - - - - - - - - - -->
<script>
    ;(function($) {
        'use srtict';

        if(!$) return;

        $(function() {
            if(!window.MileniaAlertBox) return;

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

            window.MileniaAlertBox.init($container, {
                duration: ${duration},
                cssPrefix: 'milenia-',
                easing: '${easing}',
                type: 'success'
            });
        });
    })(window.jQuery);
</script>
