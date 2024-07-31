<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

class MetaBoxServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        if(class_exists('RWMB_Field'))
        {
            require(MILENIA_FUNCTIONALITY_ROOT . 'app/Extensions/MetaBox/Fields/RWMB_Breadcrumb_Field.php');
            require(MILENIA_FUNCTIONALITY_ROOT . 'app/Extensions/MetaBox/Fields/RWMB_Widgetsettings_Field.php');
        }
    }
}

?>
