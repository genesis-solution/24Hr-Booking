<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtension;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

use Milenia\App\Extensions\VisualComposer\Shortcodes\AccordionShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\AlbumShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\AlertBoxShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\AwardsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\ButtonShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\BannersShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\BlockquoteShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\BlogPostsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\BookingFormShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\CountersShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\CountdownShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\DropcapShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\EventsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\FlexibleGridShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\FlexibleGridColumnShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\GalleryShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\GoogleMapShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\IconBoxesShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\InfoBoxesShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\InstagramShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\KeyValueListShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\MenuShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\NewsletterShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\ProgressBarsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\PortfolioShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\RoomsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\SectionHeadingShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\SignatureShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\SimpleCarouselShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\SocialIconsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\OffersShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\TableShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\TabsShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\TeamMembersShortcode;
use Milenia\App\Extensions\VisualComposer\Shortcodes\TestimonialsShortcode;

use Milenia\App\Extensions\VisualComposer\Params\GetPostsParam;
use Milenia\App\Extensions\VisualComposer\Params\GetTermsParam;

class VisualComposerServiceProvider implements ServiceProviderInterface
{
    protected $assets_path;

    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        if(!function_exists('vc_map')) return;

        $this->assets_path = MILENIA_FUNCTIONALITY_URL . App::get('config')['visual-composer']['assets_path'];

        App::bind('visual-composer', new VisualComposerExtension());
        App::get('visual-composer')->addShortcode(new AccordionShortcode())
                                   ->addShortcode(new AlbumShortcode())
                                   ->addShortcode(new AlertBoxShortcode())
                                   ->addShortcode(new AwardsShortcode())
                                   ->addShortcode(new ButtonShortcode())
                                   ->addShortcode(new BannersShortcode())
                                   ->addShortcode(new BlockquoteShortcode())
                                   ->addShortcode(new BlogPostsShortcode())
                                   ->addShortcode(new BookingFormShortcode())
                                   ->addShortcode(new CountersShortcode())
                                   ->addShortcode(new CountdownShortcode())
                                   ->addShortcode(new DropcapShortcode())
                                   ->addShortcode(new EventsShortcode())
                                   ->addShortcode(new FlexibleGridShortcode())
                                   ->addShortcode(new FlexibleGridColumnShortcode())
                                   ->addShortcode(new GalleryShortcode())
                                   ->addShortcode(new GoogleMapShortcode())
                                   ->addShortcode(new IconBoxesShortcode())
                                   ->addShortcode(new InfoBoxesShortcode())
                                   ->addShortcode(new InstagramShortcode())
                                   ->addShortcode(new KeyValueListShortcode())
                                   ->addShortcode(new MenuShortcode())
                                   ->addShortcode(new NewsletterShortcode())
                                   ->addShortcode(new ProgressBarsShortcode())
                                   ->addShortcode(new PortfolioShortcode())
                                   ->addShortcode(new RoomsShortcode())
                                   ->addShortcode(new SectionHeadingShortcode())
                                   ->addShortcode(new SignatureShortcode())
                                   ->addShortcode(new SimpleCarouselShortcode())
                                   ->addShortcode(new SocialIconsShortcode())
                                   ->addShortcode(new OffersShortcode())
                                   ->addShortcode(new TableShortcode())
                                   ->addShortcode(new TabsShortcode())
                                   ->addShortcode(new TeamMembersShortcode())
                                   ->addShortcode(new TestimonialsShortcode());

        App::get('visual-composer')->addParam(new GetPostsParam('get_posts'))
                                   ->addParam(new GetTermsParam('get_terms'));

       if(!is_admin())
       {
           add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
       }

        $this->updateVCShortcodes();
    }

    /**
     * Adds necessary assets.
     *
     * @access public
     * @return ServiceProviderInterface
     */
    public function enqueueAssets()
    {
        return $this->enqueueStyles()->enqueueScripts();
    }

    /**
     * Adds necessary stylesheets.
     *
     * @access protected
     * @return ServiceProviderInterface
     */
    protected function enqueueStyles()
    {
        wp_register_style( 'media-element', $this->assets_path . '/vendors/mediaelement/mediaelementplayer.min.css', array(), '4.2.7');
        wp_register_style( 'owl-carousel', $this->assets_path . '/vendors/owl-carousel/assets/owl.carousel.min.css', array(), '2.2.3');
        wp_register_style( 'fancybox', $this->assets_path . '/vendors/fancybox/jquery.fancybox.min.css', array(), '3.3.5');

        wp_enqueue_style('animate-css', $this->assets_path . '/css/animate.min.css', array(), '1.0.0');
        wp_enqueue_style('milenia-js-composer-front', $this->assets_path . '/css/milenia-js-composer-front.css', null, WPB_VC_VERSION, 'all' );

        if(is_rtl())
        {
            wp_enqueue_style('milenia-js-composer-front-rtl', $this->assets_path . '/css/milenia-js-composer-front-rtl.css', array('milenia-js-composer-front'), WPB_VC_VERSION, 'all' );
        }

        return $this;
    }

    /**
     * Adds necessary scripts.
     *
     * @access protected
     * @return ServiceProviderInterface
     */
    protected function enqueueScripts()
    {
        global $milenia_settings;

        wp_register_script('milenia-accordion', $this->assets_path . '/vendors/monkeysan.accordion.js', array('jquery'), '1.0.0', true);
        wp_register_script('milenia-tabs', $this->assets_path . '/vendors/monkeysan.tabs.min.js', array('jquery'), '1.0.1', true);
        wp_register_script('milenia-counters', $this->assets_path . '/vendors/wat.counters.js', array('jquery'), '1.0.0', true);
        wp_register_script('milenia-alert-box', $this->assets_path . '/js/milenia.alert-box.min.js', array('jquery'), '1.0.0', true);
        wp_register_script('isotope', $this->assets_path . '/vendors/isotope.pkgd.min.js', array('jquery'), '3.0.5', true);
        wp_register_script('media-element', $this->assets_path . '/vendors/mediaelement/mediaelement-and-player.min.js', array('jquery', 'isotope'), '4.2.7', true);
        wp_register_script('owl-carousel', $this->assets_path . '/vendors/owl-carousel/owl.carousel.min.js', array('jquery'), '2.3.3', true);
        wp_register_script('fancybox', $this->assets_path . '/vendors/fancybox/jquery.fancybox.min.js', array('jquery'), '3.3.5', true);
        wp_register_script('countdown-plugin', $this->assets_path . '/vendors/countdown/jquery.plugin.min.js', array('jquery'), '2.0.2', true);
        wp_register_script('countdown', $this->assets_path . '/vendors/countdown/jquery.countdown.js', array('jquery', 'countdown-plugin'), '2.0.2', true);
        wp_register_script('maplace', $this->assets_path . '/vendors/maplace-0.1.3.min.js', array('jquery', 'milenia-google-map'), '0.1.3', false);
        wp_register_script('milenia-tabbed-grid', $this->assets_path . '/js/milenia.tabbed-grid.min.js', array('jquery'), '1.0.0', true);

        wp_enqueue_script('milenia-easings', $this->assets_path . '/vendors/jquery.easing.1.3.min.js', array('jquery'), '1.3.0', true);
        wp_enqueue_script('milenia-js-composer-front', $this->assets_path . '/js/milenia-js-composer-front.js', array('milenia-core'), '1.0.0', true);
        wp_enqueue_script('appearjs', $this->assets_path . '/vendors/appear.min.js', null, '1.0.3', false);

        if(isset($milenia_settings) && isset($milenia_settings['milenia-google-map-api-key']) && !empty($milenia_settings['milenia-google-map-api-key']))
        {
            wp_enqueue_script('milenia-google-map', sprintf('https://maps.google.com/maps/api/js?key=%s&amp;amp;libraries=geometry&amp;amp;v=3.20', $milenia_settings['milenia-google-map-api-key']), null, '1.0.0', true);
        }

        return $this;
    }

    /**
     * Updates registered shortcodes.
     *
     * @access protected
     * @return MileniaVC
     */
    protected function updateVCShortcodes()
    {
        add_filter('vc_iconpicker-type-milenia_icons', array(&$this, 'getMileniaIcons'));
        add_action('vc_base_register_front_css', array(&$this, 'enqueMileniaIcons'));
        add_action('vc_base_register_admin_css', array(&$this, 'enqueMileniaIcons'));
        add_action('vc_backend_editor_enqueue_js_css', array(&$this, 'enqueMileniaIcons'));
        add_action('vc_frontend_editor_enqueue_js_css', array(&$this, 'enqueMileniaIcons'));

        if(function_exists('vc_map_update'))
        {
            vc_map_update('icon_type', array(
                esc_html__('Milenia Icons', 'milenia-app-textdomain') => 'milenia_icons'
            ));
        }

        if(function_exists('vc_add_param'))
        {
            vc_add_param('vc_row', array(
                'type' => 'dropdown',
                'heading' => esc_html__('Opacity of the background media element', 'milenia-app-textdomain'),
                'param_name' => 'bg_media_opacity',
                'value' => array(
                    '1',
                    '0.05',
                    '0.1',
                    '0.15',
                    '0.2',
                    '0.25',
                    '0.3',
                    '0.35',
                    '0.4',
                    '0.45',
                    '0.5',
                    '0.55',
                    '0.6',
                    '0.65',
                    '0.7',
                    '0.75',
                    '0.8',
                    '0.85',
                    '0.9',
                    '0.95'
                ),
                'description' => esc_html__('Specifies opacity of the background image or background video.', 'milenia-app-textdomain')
            ));
        }

        if(function_exists('vc_add_param'))
        {
            vc_add_param('vc_row', array(
                'type' => 'colorpicker',
                'heading' => esc_html__('Text color', 'milenia-app-textdomain'),
                'param_name' => 'milenia_text_color'
            ));
        }

        if(function_exists('vc_add_param'))
        {
            vc_add_param('vc_row', array(
                'type' => 'colorpicker',
                'heading' => esc_html__('Headings color', 'milenia-app-textdomain'),
                'param_name' => 'milenia_headings_color'
            ));
        }

        if(function_exists('vc_add_param'))
        {
            vc_add_param('vc_row', array(
                'type' => 'colorpicker',
                'heading' => esc_html__('Links color', 'milenia-app-textdomain'),
                'param_name' => 'milenia_links_color'
            ));
        }

        return $this;
    }

    /**
     * Returns an array of the milenia font icons.
     *
     * @param array $icons
     * @access public
     * @return array
     */
    public function getMileniaIcons($icons)
    {
        return array_merge($icons, array(
            array( 'milenia-font-icon-air-conditioner' => esc_html__('Conditioner', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-alarm-clock' => esc_html__('Alarm Clock', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-anchor' => esc_html__('Anchor', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-armchair' => esc_html__('Armchair', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-atm' => esc_html__('Atm', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-ax' => esc_html__('Ax', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-backpack' => esc_html__('Backpack', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bag' => esc_html__('Bag', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-baggage-trolley' => esc_html__('Baggage Trolley', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-balloon' => esc_html__('Balloon', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bathtub' => esc_html__('Bathtub', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bed' => esc_html__('Bed', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bed-plus' => esc_html__('Bed plus', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-beds' => esc_html__('Beds', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-beer' => esc_html__('Beer', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bell' => esc_html__('Bell', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bike' => esc_html__('Bike', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bonfire' => esc_html__('Bonfire', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-booking' => esc_html__('Booking', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-boots' => esc_html__('Boots', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-boots2' => esc_html__('Boots 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bottle-glasses' => esc_html__('Bottle Glasses', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bottles' => esc_html__('Bottles', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-building' => esc_html__('Building', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-building2' => esc_html__('Building 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-bus' => esc_html__('Bus', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-champagne' => esc_html__('Champagne', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-chaise-longue' => esc_html__('Chaise Longue', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-castle' => esc_html__('Castle', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cards' => esc_html__('Cards', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-car-wash' => esc_html__('Car Wash', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-car' => esc_html__('Car', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cap' => esc_html__('Cap', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-canoe' => esc_html__('Canoe', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-camping-knife' => esc_html__('Camping Knife', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-camera' => esc_html__('Camera', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-calendar' => esc_html__('Calendar', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cake' => esc_html__('Cake', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cabin' => esc_html__('Cabin', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-church' => esc_html__('Church', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-destination' => esc_html__('Destination', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-disabled' => esc_html__('Disabled', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-church2' => esc_html__('Church 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-church3' => esc_html__('Church 3', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dish' => esc_html__('Dish', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dish2' => esc_html__('Dish 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-clipboard' => esc_html__('Clipboard', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-clock' => esc_html__('Clock', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dislike' => esc_html__('Dislike', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dislike2' => esc_html__('Dislike 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cocktail' => esc_html__('Cocktail', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cocktail2' => esc_html__('Cocktail 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-do-not-disturb' => esc_html__('Do Not Disturb', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-coffee-to-go' => esc_html__('Coffee to go', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dog' => esc_html__('Dog', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-coliseum' => esc_html__('Coliseum', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-dolphin' => esc_html__('Dolphin', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-double-bed' => esc_html__('Double Bed', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-column' => esc_html__('Column', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-compass' => esc_html__('Compass', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-drink' => esc_html__('Drink', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-eat' => esc_html__('Eat', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-cup' => esc_html__('Cup', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-desktop' => esc_html__('Desktop', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-eye' => esc_html__('Eye', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-globe' => esc_html__('Globe', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-ice-cream' => esc_html__('Ice Cream', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-glasses' => esc_html__('Glasses', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hotel-sign' => esc_html__('Hotel Sign', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hotel' => esc_html__('Hotel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-food-to-go' => esc_html__('Food to go', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-flippers' => esc_html__('Flippers', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hostel' => esc_html__('Hostel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-home-on-the-water' => esc_html__('Home on the water', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-flip-flops' => esc_html__('Flip flops', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-flashlight' => esc_html__('Flashlight', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hat-glasses' => esc_html__('Hat glasses', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hanger' => esc_html__('Hanger', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-flag' => esc_html__('Flag', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-fish' => esc_html__('Fish', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-handwheel' => esc_html__('Handwheel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hand' => esc_html__('Hand', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-fireplace' => esc_html__('Fireplace', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-filling-station' => esc_html__('Filling Station', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-hair-dryer' => esc_html__('Hair dryer', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-gym' => esc_html__('Gym', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-ferris-wheel' => esc_html__('Ferris Wheel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-fast-food' => esc_html__('Fast Food', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-grill' => esc_html__('Grill', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-globe2' => esc_html__('Globe 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-fan' => esc_html__('Fan', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-iceberg' => esc_html__('Iceberg', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-location2' => esc_html__('Location 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-magnifier' => esc_html__('Magnifier', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-info' => esc_html__('Info', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-iron' => esc_html__('Iron', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-megaphone' => esc_html__('Megaphone', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-message' => esc_html__('Message', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-island' => esc_html__('Island', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-key' => esc_html__('Key', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mobile' => esc_html__('Mobile', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mobile2' => esc_html__('Mobile 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-lamp' => esc_html__('Lamp', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-life-vest' => esc_html__('Life Vest', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-money-exchange' => esc_html__('Money Exchange', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-moon' => esc_html__('Moon', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-lifebuoy' => esc_html__('Lifebuoy', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-lift' => esc_html__('Lift', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mosque' => esc_html__('Mosque', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mosque2' => esc_html__('Mosque 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-lift2' => esc_html__('Lift 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-like' => esc_html__('Like', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mountains-cloud' => esc_html__('Mountains Cloud', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-mountains-sun' => esc_html__('Mountains Sun', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-like2' => esc_html__('Like 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-location' => esc_html__('Location', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-music' => esc_html__('Music', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-pictures' => esc_html__('Pictures', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sailing-ship' => esc_html__('Sailing Ship', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-safe' => esc_html__('Safe', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-picnic' => esc_html__('Picnic', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-phone' => esc_html__('Phone', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-road-sign2' => esc_html__('Road Sign 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-road-sign' => esc_html__('Road Sign', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-penguin' => esc_html__('Penguin', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-paw' => esc_html__('Paw', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-road-cone' => esc_html__('Road Cone', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-receptionist' => esc_html__('Receptionist', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-passanger' => esc_html__('Passanger', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-pass' => esc_html__('Pass', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-rain' => esc_html__('Rain', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-pyramids' => esc_html__('Pyramids', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-parking2' => esc_html__('Parking 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-parking' => esc_html__('Parking', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-power-socket' => esc_html__('Power Socket', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-pool' => esc_html__('Pool', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-paper-pencil' => esc_html__('Paper Pencil', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-palms' => esc_html__('Palms', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-pointer' => esc_html__('Pointer', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-plug' => esc_html__('Plug', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-no-smoking' => esc_html__('No Smoking', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-no-photo' => esc_html__('No Photo', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-plane' => esc_html__('Plane', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-satellite-antenna' => esc_html__('Statellite Antenna', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sos' => esc_html__('SOS', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-starfish' => esc_html__('Starfish', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-ship' => esc_html__('Ship', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-shop' => esc_html__('Shop', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-stela' => esc_html__('Stela', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-storm' => esc_html__('Storm', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-shopping-bag' => esc_html__('Shopping Bag', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-shorts' => esc_html__('Shorts', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-suitcase' => esc_html__('Suitcase', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-suitcase-plus' => esc_html__('Suitcase Plus', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-shovel' => esc_html__('Shovel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-shower' => esc_html__('Shower', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sun' => esc_html__('Sun', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sun2' => esc_html__('Sun 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-signal' => esc_html__('Signal', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-ski' => esc_html__('Ski', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sunscreen' => esc_html__('Sunscreen', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-sunset' => esc_html__('Sunset', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-snorkeling' => esc_html__('Snorkeling', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-snowboard' => esc_html__('Snowboard', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-support' => esc_html__('Support', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-surfboard' => esc_html__('Surfboard', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-snowflake' => esc_html__('Snowflake', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-soap' => esc_html__('Soap', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-swimming' => esc_html__('Swimming', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-trailer' => esc_html__('Trailer', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-waiter' => esc_html__('Waiter', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-volcano' => esc_html__('Volcano', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-traffic-light' => esc_html__('Traffic Light', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-toothpaste-brush' => esc_html__('Toothpaste Brush', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-video-camera' => esc_html__('Video Camera', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tv-tower' => esc_html__('TV Tower', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tickets' => esc_html__('Tickets', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-thermos' => esc_html__('Thermos', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tv' => esc_html__('TV', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-turtle' => esc_html__('Turtle', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-thermometer' => esc_html__('Thermometer', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tent' => esc_html__('Tent', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tunnel' => esc_html__('Tunnel', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-trees2' => esc_html__('Trees 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tennis' => esc_html__('Tennis', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-temple' => esc_html__('Temple', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-trees' => esc_html__('Trees', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-traveler2' => esc_html__('Traveler 2', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-taxi' => esc_html__('Taxi', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tag' => esc_html__('Tag', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-traveler' => esc_html__('Traveler', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-wi-fi' => esc_html__('Wi-Fi', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-water-lily' => esc_html__('Water Lily', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-tram' => esc_html__('Tram', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-table-chairs-umbrella' => esc_html__('Table Chairs Umbrella', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-table' => esc_html__('Table', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-train' => esc_html__('Train', 'milenia-app-textdomain') ),
            array( 'milenia-font-icon-washer' => esc_html__('Washer', 'milenia-app-textdomain') )
        ));
    }

    /**
     * Adds milenia icons font.
     *
     * @access public
     * @return void
     */
    public function enqueMileniaIcons($font)
    {
        wp_enqueue_style('milenia-icons', $this->assets_path . '/css/milenia-icon-font.css', null, '1.0.0', 'all' );
    }
}
?>
