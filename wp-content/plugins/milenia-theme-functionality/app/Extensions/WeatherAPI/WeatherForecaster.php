<?php
namespace Milenia\App\Extensions\WeatherAPI;

use Milenia\Core\App;

class WeatherForecaster
{
    protected $API_key;

    protected $server_url = 'http://api.weatherstack.com/current?access_key=%s&query=%s';

    /**
     * Constructor.
     *
     * @param string $API_key
     */
    public function __construct($API_key)
    {
        $this->API_key = $API_key;
    }

    /**
     * Makes a query to the weathe api server.
     *
     * @param string $q
     * @access public
     * @return
     */
    public function query($q)
    {



        if(!empty($this->API_key) && function_exists('curl_init'))
        {
            if($curl = curl_init())
            {
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_URL, sprintf($this->server_url, $this->API_key, urlencode($q)));
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);


                update_option('milenia_weather_data', curl_exec($curl));
                curl_close($curl);
            }
        }

        return $this;
    }

    /**
     * Returns weather data.
     *
     * @access public
     * @return array|null
     */
    public function getData()
    {
        return json_decode(get_option('milenia_weather_data'));
    }

    public function getIconClass()
    {
        $data = $this->getData();

        if(is_object($data) && isset($data->current) && isset($data->current->weather_code))
        {
            switch($data->current->weather_code)
            {
                case 113 :
                    return 'icon-sun';
                break;
                case 116 :
                    return 'icon-cloud-sun';
                break;
                case 119 :
                case 122 :
                    return 'icon-cloud';
                break;
                case 176 :
                case 263 :
                case 266 :
                case 293 :
                case 296 :
                case 299 :
                case 302 :
                case 305 :
                case 308 :
                case 353 :
                case 356 :
                case 359 :
                    return 'icon-cloud-rain';
                break;
                case 179 :
                case 182 :
                case 185 :
                case 227 :
                case 230 :
                case 281 :
                case 284 :
                case 311 :
                case 314 :
                case 317 :
                case 320 :
                case 323 :
                case 326 :
                case 329 :
                case 332 :
                case 335 :
                case 338 :
                case 350 :
                case 362 :
                case 365 :
                case 368 :
                case 371 :
                case 374 :
                case 377 :
                    return 'icon-cloud-snow';
                break;
                case 200 :
                case 386 :
                case 389 :
                case 392 :
                case 395 :
                    return 'icon-cloud-lightning';
                break;
                case 143 :
                case 248 :
                case 260 :
                    return 'icon-cloud-fog';
                break;
            }
        }
    }

    public function getCelsiusValue()
    {
        $data = $this->getData();


        if(is_object($data) && isset($data->current) && isset($data->current->temperature))
        {
            return $data->current->temperature;
        }
    }

    public function getFahrenheitValue()
    {
        $data = $this->getData();
        
        if(is_object($data) && isset($data->current) && isset($data->current->temperature)) {
            return number_format(($this->getCelsiusValue() * (9/5)) + 32, 1, '.', '');
        }
    }

    public function getName()
    {
        $data = $this->getData();

        if(is_object($data) && isset($data->location) && isset($data->location->name))
        {
            return $data->location->name;
        }
    }

    public function getCountry()
    {
        $data = $this->getData();

        if(is_object($data) && isset($data->location) && isset($data->location->country))
        {
            return $data->location->country;
        }
    }
}
?>
