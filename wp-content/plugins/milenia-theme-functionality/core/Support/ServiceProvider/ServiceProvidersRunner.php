<?php
/**
 *
 *
 *
 */
namespace Milenia\Core\Support\ServiceProvider;

use Milenia\Core\App;

class ServiceProviderRunner
{
    /**
     * Runs initializations of the service providers.
     *
     * @access public
     * @static
     * @return void
     */
    public static function init()
    {
        $providers = App::get('config')['providers'];

        if(!empty($providers))
        {
            foreach($providers as $provider)
            {
                (new $provider)->boot();
            }
        }
    }
}

?>
