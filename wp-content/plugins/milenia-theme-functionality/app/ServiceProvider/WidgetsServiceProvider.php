<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

class WidgetsServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        add_action('widgets_init', array($this, 'onWidgetsInitialized'), 1);
    }

    /**
     * Callback that will be called on 'widgets_init' action.
     *
     * @access public
     * @return void
     */
    public function onWidgetsInitialized()
    {
        register_widget('RecentPostsWidget');
        register_widget('TwitterFeedWidget');
        register_widget('InstagramWidget');
        register_widget('SocialIconsWidget');
        register_widget('ImagesWidget');
        register_widget('UpcomingEventsListWidget');
        register_widget('BannerWidget');
        register_widget('AboutWidget');
        register_widget('WeatherWidget');
        register_widget('GalleryWidget');
    }
}
?>
