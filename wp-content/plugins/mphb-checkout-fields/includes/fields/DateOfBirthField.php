<?php

namespace MPHB\CheckoutFields\Fields;

use MPHB\Admin\Fields\InputField;
use MPHB\Utils\ValidateUtils;

/**
 * @since 1.0
 */
class DateOfBirthField extends InputField
{
    const TYPE = 'date-of-birth';

    /** @var int */
    public $minAge = 0;

    /** @var int */
    public $maxAge = 100;

    protected $default = '';
    protected $value = '';

    public function __construct($name, $args, $value = '')
    {
        parent::__construct($name, $args, $value);

        $this->minAge = isset($args['min_age']) ? $args['min_age'] : apply_filters('mphb_cf_date_of_birth_min_age', $this->minAge);
        $this->maxAge = isset($args['max_age']) ? $args['max_age'] : apply_filters('mphb_cf_date_of_birth_max_age', $this->maxAge);
    }

    /**
     * @return string
     */
    protected function renderInput()
    {
        return $this->renderFields();
    }

    /**
     * @return string
     */
    public function renderFields()
    {
        $output = $this->renderDays();
        $output .= '&nbsp;';
        $output .= $this->renderMonths();
        $output .= '&nbsp;';
        $output .= $this->renderYears();

        return $output;
    }

    /**
     * @return string
     */
    protected function renderDays()
    {
        $atts = $this->inputAtts();

        $atts['name'] .= '[day]';
        $atts['id']   .= '-day';
        $atts['class'] = 'mphb-control-component mphb-day-control-component';

        $values      = $this->getValues();
        $daysInMonth = mphb_days_in_month($values['month'], $values['year']);
        $daysRange   = range(1, $daysInMonth);

        $days = ['' => esc_html__('Day', 'mphb-checkout-fields')];
        $days += array_combine($daysRange, $daysRange);

        $selectedDay = !empty($this->value) ? $values['day'] : '';

        return mphb_tmpl_render_select($days, $selectedDay, $atts);
    }

    /**
     * @return string
     */
    protected function renderMonths()
    {
        $atts = $this->inputAtts();

        $atts['name'] .= '[month]';
        $atts['id']   .= '-month';
        $atts['class'] = 'mphb-control-component mphb-month-control-component';

        $months = ['' => esc_html__('Month', 'mphb-checkout-fields')];

        $gregorianCalendar = cal_info(CAL_GREGORIAN);
        $months += array_map('translate', $gregorianCalendar['months']);

        $selectedMonth = !empty($this->value) ? $this->getMonth() : '';

        return mphb_tmpl_render_select($months, $selectedMonth, $atts);
    }

    /**
     * @return string
     */
    protected function renderYears()
    {
        $atts = $this->inputAtts();

        $atts['name'] .= '[year]';
        $atts['id']   .= '-year';
        $atts['class'] = 'mphb-control-component mphb-year-control-component';

        $currentYear = mphb_current_year();
        $yearsRange = range($currentYear - $this->minAge, $currentYear - $this->maxAge);

        $years = ['' => esc_html__('Year', 'mphb-checkout-fields')];
        $years += array_combine($yearsRange, $yearsRange);

        $selectedYear = !empty($this->value) ? $this->getYear() : '';

        return mphb_tmpl_render_select($years, $selectedYear, $atts);
    }

    protected function inputAtts()
    {
        $atts = [
            'name' => $this->name,
            'id'   => $this->name
        ];

        if ($this->required) {
            $atts['required'] = 'required';
        }

        return $atts;
    }

    /**
     * @param string|array $value
     * @return string
     */
    public function sanitize($value)
    {   
        // Remove empty values before the next check
        if (is_array($value)) {
            $value = array_filter($value);
        }

        // Return "" for empty values
        if (empty($value)) {
            return '';
        }

        // Split the value into [day, month, year] values
        if (is_string($value)) {
            preg_match('/(?<year>\d+)?\-?(?<month>\d+)?\-?(?<day>\d+)?/', $value, $date);
        } else {
            $date = $value;
        }

        // Validate
        $currentYear = mphb_current_year();

        $day   = 1;
        $month = 1;
        $year  = $currentYear - $this->minAge;

        foreach (['year', 'month', 'day'] as $fieldName) {
            $fieldValue = isset($date[$fieldName]) ? $date[$fieldName] : '';

            if ($fieldValue !== '') {
                // filter_var() in ValidateUtils::parseInt() fails on strings
                // with leading zeros when trying to validate an integer number
                $fieldValue = ltrim($fieldValue, '0');

                switch ($fieldName) {
                    case 'year':
                        $year = ValidateUtils::parseInt($fieldValue);
                        $year = mphb_limit($year, $currentYear - $this->maxAge, $currentYear - $this->minAge);
                        break;

                    case 'month':
                        $month = ValidateUtils::parseInt($fieldValue);
                        $month = mphb_limit($month, 1, 12);
                        break;

                    case 'day':
                        $day = ValidateUtils::parseInt($fieldValue);
                        $daysInMonth = mphb_days_in_month($month, $year);
                        $day = mphb_limit($day, 1, $daysInMonth);
                        break;
                }
            } // if $fieldValue !== ''
        } // For each field

        $day   = str_pad($day,   2, '0', STR_PAD_LEFT);
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        return "{$year}-{$month}-{$day}";
    }

    /**
     * @return int[] [year, month, day]
     */
    public function getValues()
    {
        $values = !empty($this->value) ? explode('-', $this->value) : [];

        return [
            'year'  => isset($values[0]) ? (int)$values[0] : mphb_current_year() - $this->minAge,
            'month' => isset($values[1]) ? (int)$values[1] : 1,
            'day'   => isset($values[2]) ? (int)$values[2] : 1
        ];
    }

    /**
     * @return int
     */
    public function getDay()
    {
        $values = $this->getValues();

        return $values['day'];
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        $values = $this->getValues();

        return $values['month'];
    }

    /**
     * @return int
     */
    public function getYear()
    {
        $values = $this->getValues();

        return $values['year'];
    }
}
