<!--================ Countdown ================-->
<script>
    (function($){
        if(!$) return;

        $(function(){
            var $container = $('#${unique_id}'),
                endDate;

            if(!$container.length || !$.fn.countdown) return;

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

            endDate = $container.data();

            $container.countdown({
                until : new Date(
                    endDate.year,
                    endDate.month || 0,
                    endDate.day || 1,
                    endDate.hours || 0,
                    endDate.minutes || 0,
                    endDate.seconds || 0
                ),
                padZeroes: true,
                format : 'dHMS',
                labels : window.MileniaCountdownLocalization && window.MileniaCountdownLocalization.labels || ['Years', 'Months', 'Weeks', 'Days', 'Hours', 'Minutes', 'Seconds']
            });
        });
    })(window.jQuery);
</script>
<div id="${unique_id}" class="milenia-countdown ${container_classes}" data-year="${year}" data-month="${month}" data-day="${day}" data-hours="${hours}" data-minutes="${minutes}" data-seconds="${seconds}" data-animation="${css_animation}"></div>
<!--================ End of Countdown ================-->
