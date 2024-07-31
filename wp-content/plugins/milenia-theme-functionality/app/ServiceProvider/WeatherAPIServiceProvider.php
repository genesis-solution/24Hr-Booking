<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\App\Extensions\WeatherAPI\WeatherForecaster;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

class WeatherAPIServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        add_action ('redux/options/milenia_settings/saved', array(&$this, 'removeScheduleEvent'));
        add_action('init', array(&$this, 'buildForecaster'));
    }

    public function buildForecaster()
    {
        global $MileniaWeatherForecaster, $Milenia;

        if(!isset($Milenia)) return;

        $API_key = $Milenia->getThemeOption('milenia-apixu-api-key');
        $q = $Milenia->getThemeOption('milenia-apixu-city');

        if(!empty($API_key) && !empty($q) && function_exists('wp_schedule_event'))
        {
            $MileniaWeatherForecaster = new WeatherForecaster($API_key);
            $args = array($q);


            if( !wp_next_scheduled('milenia_hourly_weather_check', $args ) )
            {
                wp_schedule_event( time(), 'hourly', 'milenia_hourly_weather_check', $args );
            }

            add_action('milenia_hourly_weather_check', array(&$this, 'checkWeather'));
        }
    }

    public function checkWeather($q)
    {
        global $MileniaWeatherForecaster;

        $MileniaWeatherForecaster->query($q);
    }

    public function removeScheduleEvent()
    {
        wp_unschedule_hook( 'milenia_hourly_weather_check' );
    }
}
?>
