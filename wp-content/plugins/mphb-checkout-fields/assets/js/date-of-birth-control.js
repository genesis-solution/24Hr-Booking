jQuery(function () {
    'use strict';

    // Date of birth control
    jQuery('.mphb-date_of_birth-control, .mphb-ctrl-date-of-birth').each(function (i, element) {
        var $control = jQuery(element);
        var $day     = $control.find('.mphb-day-control-component');
        var $month   = $control.find('.mphb-month-control-component');
        var $year    = $control.find('.mphb-year-control-component');

        $month.on('change', onMonthOrYearUpdate);
        $year.on('change', onMonthOrYearUpdate);

        function onMonthOrYearUpdate()
        {
            var month = $month.val();
            var year = $year.val() || mphb_cf_current_year();

            var daysInMonth = month !== '' ? mphb_cf_days_in_month(parseInt(month), parseInt(year)) : 31;

            mphb_cf_reset_options_number($day, daysInMonth);
        }
    });

    function mphb_cf_current_year()
    {
        var currentDate = new Date();
        return currentDate.getFullYear();
    }

    function mphb_cf_days_in_month(month, year)
    {
        var date = new Date(year, month, 0);
        return date.getDate();
    }

    function mphb_cf_reset_options_number($select, limit)
    {
        var optionsCount = $select.children('[value!=""]').length;

        if (optionsCount < limit) {
            // Add more options
            for (var i = optionsCount + 1; i <= limit; i++) {
                var $option = jQuery('<option value="' + i + '">' + i + '</option>');
                $select.append($option);
            }

        } else if (optionsCount > limit) {
            // Remove extra options
            $select.children().each(function (i, option) {
                if (option.value !== '' && parseInt(option.value) > limit) {
                    option.remove();
                }
            });
        }
    }
});
