<?php
/**
* The main application config.
*
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core;
global $Milenia;

return array(
    'providers' => array(
        '\Milenia\App\ServiceProvider\MPHBServiceProvider',
        '\Milenia\App\ServiceProvider\VisualComposerServiceProvider',
        '\Milenia\App\ServiceProvider\CustomPostTypeServiceProvider',
        '\Milenia\App\ServiceProvider\MetaBoxServiceProvider',
        '\Milenia\App\ServiceProvider\WidgetsServiceProvider',
        '\Milenia\App\ServiceProvider\WeatherAPIServiceProvider',
        '\Milenia\App\ServiceProvider\ReviewsServiceProvider'
    ),

    'visual-composer' => array(
        'templates_path' => 'app/Extensions/VisualComposer/Shortcodes/templates',
        'vc_templates_path' => 'app/Extensions/VisualComposer/VCTemplates',
        'assets_path' => 'app/Extensions/VisualComposer/assets'
    )
);
?>
