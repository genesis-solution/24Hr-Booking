<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

class MPHBServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        // single page
        add_action('milenia_mphb_render_single_room_type_price', array('\MPHB\Views\SingleRoomTypeView', 'renderPrice'), 10 );
        add_action('milenia_mphb_render_single_room_type_title', array('\MPHB\Views\SingleRoomTypeView', 'renderTitle'), 10 );
        add_action('milenia_mphb_render_single_room_type_layout_sidebar_content', array($this, 'renderSidebarLayoutMedia'), 10, 1);
        add_action('milenia_mphb_render_single_room_type_layout_sidebar_content', array($this, 'renderRoomTypeContentTabs'), 20, 1);

        add_action('milenia_mphb_render_single_room_type_description', array('\MPHB\Views\SingleRoomTypeView', 'renderDescription'), 10 );
        add_action('milenia_mphb_render_single_room_type_floor_plan', array($this, 'renderFloorPlan' ), 10, 1);
        add_action('milenia_mphb_render_single_room_type_reservation_form', array($this, 'renderReservationForm' ), 10, 1);
        add_action('milenia_mphb_render_single_room_type_availability', array($this, 'renderAvailability' ), 10, 1);
        add_action('milenia_mphb_render_single_room_type_amenities', array($this, 'renderAmenities' ), 10, 3);
        add_action('milenia_mphb_render_single_room_type_fullwidth_before_content', array($this, 'renderRevSliderGallery'), 10);
        add_action('milenia_mphb_render_single_room_type_fullwidth_before_content', array($this, 'renderBreadcrumbs'), 20);
        add_action('milenia_mphb_render_single_room_type_rates_table', array($this, 'renderRatesTableView'), 10);
        add_action('milenia_mphb_render_single_room_type_reviews', array($this, 'renderReviews'), 10);

        // Grid system integration
        add_action('mphb_sc_rooms_before_loop', array($this, 'gridSystemIntegrationBeginning'));
        add_action('mphb_sc_search_results_before_loop', array($this, 'gridSystemIntegrationBeginning'));
        add_action('mphb_sc_rooms_after_loop', array($this, 'gridSystemIntegrationEnding'));
        // add_action('mphb_sc_search_results_after_loop', array($this, 'gridSystemIntegrationBeginning'));
        add_action('mphb_sc_rooms_before_item', array($this, 'gridSystemIntegrationItemBeginning'));
        add_action('mphb_sc_search_results_before_room', array($this, 'gridSystemIntegrationItemBeginning'));
        add_action('mphb_sc_rooms_after_item', array($this, 'gridSystemIntegrationEnding'));
        add_action('mphb_sc_search_results_after_room', array($this, 'gridSystemIntegrationEnding'));
        add_filter('mphb_sc_search_results_wrapper_class', array($this, 'defaultWrapperClasses'));
        add_filter('mphb_sc_room_wrapper_class', array($this, 'defaultWrapperClasses'));

        // Facilities
        add_action('mphb_room_type_facility_edit_form_fields', array($this, 'facilitiesEditFormFields'), 10);
		add_action('mphb_room_type_facility_add_form_fields', array($this, 'facilitiesEditFormFields'), 10);
		add_action('created_mphb_room_type_facility', array($this, 'facilitiesSaveIcon'), 10, 2);
		add_action('edited_mphb_room_type_facility', array($this, 'facilitiesUpdatedIcon'), 10, 2);
		add_action('admin_footer', array($this, 'enqueueScripts'));

        add_filter('manage_edit-mphb_room_type_facility_columns', array($this, 'facilitiesManageColumnsName'));
        add_action('manage_mphb_room_type_facility_custom_column', array($this, 'facilitiesManageColumnsContent'), 10, 3);

        add_action('milenia_mphb_render_loop_room_type_before_price', array($this, 'priceModification'), 10);
        add_action('milenia_mphb_render_single_room_type_before_price', array($this, 'priceModification'), 10);

        // Pagination
        add_filter('mphb_pagination_args', array($this, 'modifyPagination'));
    }

    public function renderSidebarLayoutMedia($room_type_id)
    {
        if(MPHB()->getCurrentRoomType()->hasGallery())
        {
            \MPHB\Views\SingleRoomTypeView::renderGallery();
        }
        else
        {
            ?>
            <div class="milenia-entity-media"> <?php
                the_post_thumbnail($room_type_id, 'entity-thumb-standard');
            ?></div><?php
        }
    }

    public function modifyPagination($args)
    {
        return array_merge($args, array(
            'prev_text' => esc_html__('Prev', 'milenia-app-textdomain'),
            'next_text' => esc_html__('Next', 'milenia-app-textdomain')
        ));
    }

    public function facilitiesManageColumnsName($columns)
    {
        return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 			'icon'=> esc_html__('Icon', 'milenia-app-textdomain'),
 		    'name' => esc_html__('Name', 'milenia-app-textdomain'),
            'description' => esc_html__('Description', 'milenia-app-textdomain'),
            'slug' => esc_html__('Slug', 'milenia-app-textdomain'),
            'posts' => esc_html__('Posts', 'milenia-app-textdomain')
 		), $columns);
    }

    public function facilitiesManageColumnsContent($term, $column, $term_id)
    {
        $icon = get_term_meta($term_id, 'icon', true);

        if($icon) : ?>
            <span class="<?php echo esc_attr($icon); ?>"></span>
        <?php endif;
    }

    public function facilitiesEditFormFields()
    {
        $icons = apply_filters('milenia-facilities-icons', array(
            array( 'milenia-font-icon-none' => esc_html__('None', 'milenia-app-textdomain') ),
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
            array( 'milenia-font-icon-washer' => esc_html__('Washer', 'milenia-app-textdomain') ),

            array( 'milenia-svg-icon-bath' => esc_html__('Bath', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-bathroom' => esc_html__('Bathroom', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-bathtub' => esc_html__('Bathtub', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-coffee' => esc_html__('Coffee', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-jacuzzi' => esc_html__('Jacuzzi', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-no-pets' => esc_html__('No Pets', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-portafilter' => esc_html__('Portafilter', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-shower' => esc_html__('Shower', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-smart-tv' => esc_html__('Smart TV', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-sofa' => esc_html__('Sofa', 'milenia-app-textdomain') ),
            array( 'milenia-svg-icon-wc' => esc_html__('WC', 'milenia-app-textdomain') ),
        ));

        $icons_in_row = 10;

		if(function_exists('get_current_screen'))
		{
			$current_screen = get_current_screen();
			if($current_screen->base == 'term')
			{
				$term_id = intval($_GET['tag_ID']);
				$term_icon = get_term_meta($term_id, 'icon', true);
			}
		}

        ?>
            <div class="form-field">
                <label for="icon"><?php esc_html_e('Icon', 'milenia-app-textdomain'); ?></label>

                <div class="milenia-admin-iconpicker">
                    <div class="milenia-admin-iconpicker-inner">
                        <?php foreach($icons as $index => $icon) : ?>
                            <div class="milenia-admin-iconpicker-item" style="width: <?php echo (100 / $icons_in_row); ?>%;">
                                <?php foreach($icon as $class => $title) : ?>
                                <button type="button" title="<?php echo esc_attr($title); ?>" data-icon-value="<?php echo esc_attr($class); ?>"
									<?php if(isset($term_icon) && $term_icon == $class) : ?>class="milenia-admin-iconpicker-active"<?php endif; ?>>
                                    <i class="<?php echo esc_attr($class); ?>"></i>
                                </button>
                            <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" name="icon" value="<?php echo esc_attr(isset($term_icon) ? $term_icon : 'milenia-font-icon-none'); ?>">
                </div>
            </div>
        <?php
    }

    public function facilitiesSaveIcon($term_id)
    {
        if( isset( $_POST['icon'] ) && '' !== $_POST['icon'] )
		{
            $icon = $_POST['icon'];
            add_term_meta( $term_id, 'icon', $icon, true );
        }
    }

    public function facilitiesUpdatedIcon($term_id)
    {
        if( isset( $_POST['icon'] ) && '' !== $_POST['icon'] )
		{
            $icon = $_POST['icon'];
            update_term_meta ( $term_id, 'icon', $icon );
        }
		else
		{
            update_term_meta ( $term_id, 'icon', '' );
        }
    }

	public function enqueueScripts()
	{
		?>
			<script>
				jQuery(document).ajaxComplete(function(event, xhr, settings) {
					var queryStringArr = settings.data.split('&');
					if( jQuery.inArray('action=add-tag', queryStringArr) !== -1 ) {
						var xml = xhr.responseXML;
						$response = jQuery(xml).find('term_id').text();
						if($response != "") {
							var $adminIconPicker = jQuery('.milenia-admin-iconpicker'),
								$items,
								$active,
								$input;

							if($adminIconPicker.length) {
								$items = $adminIconPicker.find('.milenia-admin-iconpicker-item');
								if($items.length) {
									$active = $adminIconPicker.find('.milenia-admin-iconpicker-active');

									if($active.length) $active.removeClass('milenia-admin-iconpicker-active');
									if($items.first().children().length) $items.first().children().addClass('milenia-admin-iconpicker-active');
								}

								$input = $adminIconPicker.find('input[type="hidden"]');
								if($input.length) $input.val('milenia-font-icon-none');
							}

							// Clear the thumb image
							jQuery('#category-image-wrapper').html('');
						}
					}
				});
			</script>
		<?php
	}

    public function renderRoomTypeContentTabs($room_type_id)
    {
    	global $Milenia;
        ?>
			<!--================ Tabs ================-->
			<div class="milenia-tabs milenia-tabs--integrated milenia-tabs--style-2 milenia-tabs--tour-sections-lg">
				<!--================ Tabs Navigation ================-->
				<div role="tablist" aria-label="<?php esc_attr_e('Page content navigation', 'milenia-app-textdomain'); ?>" class="milenia-tabs-nav">

					<?php $sections = milenia_mphb_render_sections();
					$milenia_single_rooms_sections = $Milenia->getThemeOption('milenia-single-room-sections')['enabled'];

					$i = 0; ?>

					<?php foreach ( $milenia_single_rooms_sections as $key => $title ): ?>

						<?php
						$section = $sections[$key] ? $sections[$key] : '';
						if (!$section) continue;

						$href = '#tab-'. $key .'';

						if ( 'reservation' == $key ) {
							$href = '#booking-form-'. $room_type_id .'-room';
						}
						?>

						<span <?php if ($i == 0 ): ?>class="milenia-active"<?php endif; ?>>
							<a id="tab-<?php echo esc_attr($key) ?>-link" href="<?php echo esc_url($href) ?>" role="tab" aria-selected="false" aria-controls="tab-<?php echo esc_attr($key) ?>" class="milenia-ln--independent milenia-tab-link"><?php echo sprintf('%s', esc_html__($title, 'milenia')); ?></a>
						</span>

						<?php $i++; ?>

					<?php endforeach; ?>

				</div>
				<!--================ End of Tabs Navigation ================-->

				<!--================ Tabs Container ================-->
				<div class="milenia-tabs-container">

					<?php foreach ( $milenia_single_rooms_sections as $key => $title ): ?>

						<?php
						$section = $sections[$key] ? $sections[$key] : '';
						if (!$section) continue;

						$id = 'tab-'. $key .'';

						if ( 'reservation' == $key ) {
							$id = 'booking-form-'. $room_type_id .'-room';
						}
						?>

						<div id="<?php echo esc_attr($id) ?>" tabindex="0" role="tabpanel" aria-labelledby="tab-<?php echo esc_attr($key) ?>-link" class="milenia-tab">
							<?php if ( 'description' == $key ): ?>

								<?php do_action($section['action'][0]); ?>
								<?php do_action($section['action'][1]); ?>

							<?php else: ?>

								<?php do_action($section['action'], $section['arg2'][0], $section['arg2'][1]); ?>

							<?php endif; ?>
						</div>

					<?php endforeach; ?>

				</div>
				<!--================ End of Tabs Container ================-->
			</div>
			<!--================ End of Tabs ================-->
		<?php
    }

    public function renderReservationForm($complex = false)
    {

        /**
         * @hooked \MPHB\Views\SingleRoomTypeView::_renderReservationFormTitle - 10
         */
        do_action('mphb_render_single_room_type_before_reservation_form');

        mphb_tmpl_the_room_reservation_form();

        do_action('mphb_render_single_room_type_after_reservation_form');
    }

	public function renderAvailability($complex = false)
	{
		/**
		 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCalendarTitle - 10
		 */
		mphb_tmpl_the_room_type_calendar();
	}

    public function renderFloorPlan($complex = false)
    {
        global $Milenia;
        if(!function_exists('MPHB') || !isset($Milenia) || is_null($Milenia)) return;

		$floor_plan = $Milenia->getThemeOption('accomodation-floor-plan', array(), array(
			'object_id' => MPHB()->getCurrentRoomType()->getId()
		));

		$floor_plan_attribute = array();

		if(is_array($floor_plan) && !empty($floor_plan))
        {
			foreach($floor_plan as $image)
            {
				array_push($floor_plan_attribute, array(
					'src' => $image['full_url'],
					'opts' => array(
						'caption' => $image['caption']
					)
				));
			}
			if($complex) : ?>
                <figure class="milenia-figure-linked">
                    <img src="<?php echo esc_url($floor_plan_attribute[0]['src']) ?>" alt="<?php echo esc_url($floor_plan_attribute[0]['opts']['caption']) ?>">
                    <figcaption>
                        <a href="#" data-fancybox-album-src="<?php echo esc_js(wp_json_encode($floor_plan_attribute)); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php echo esc_html__('View Floor Plan', 'milenia-theme-functionality'); ?></a>
                    </figcaption>
                </figure>
            <?php else : ?>
			    <a href="#" data-fancybox-album-src="<?php echo esc_js(wp_json_encode($floor_plan_attribute)); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php echo esc_html__('View Floor Plan', 'milenia-theme-functionality'); ?></a>
            <?php endif; ?>
		<?php }
    }

    public function renderAmenities($columns = 3, $items_in_column = 11)
    {
        if(!function_exists('MPHB')) return;

        $RoomType = MPHB()->getCurrentRoomType();
        $room_type_id = $RoomType->getId();

        $terms = get_the_terms($room_type_id, 'mphb_room_type_facility');
		$terms_template = array();
        $column_class = $columns == 2 ? 'col-lg-6' : 'col-md-4';

		if(is_array($terms)) {
			$terms_template[] = '<div class="row"><div class="'.esc_attr($column_class).'"><ul class="milenia-list--icon milenia-list--icon-big">';

			foreach($terms as $index => $term)
            {
				$term_icon_class = get_term_meta($term->term_id, 'icon', true);
				$terms_template[] = '<li>';

				if($term_icon_class)
				{
					$terms_template[] = sprintf('<span class="%s"></span>', esc_attr($term_icon_class));
				}
				$terms_template[] = esc_html( $term->name ) . '</li>';

                if($index > 0 && (($index+1) % $items_in_column == 0))
                {
                    $terms_template[] = '</ul></div><div class="'.esc_attr($column_class).'"><ul class="milenia-list--icon milenia-list--icon-big">';
                }
			}

			$terms_template[] = '</ul></div></div>';
		}

        echo implode('', $terms_template);
    }

    public function gridSystemIntegrationBeginning()
    {
        echo '<div class="milenia-grid">';
    }

    public function gridSystemIntegrationItemBeginning()
    {
        echo '<div class="milenia-grid-item">';
    }

    public function gridSystemIntegrationEnding()
    {
        echo '</div>';
    }

    public function defaultWrapperClasses($classes)
    {
        $classes .= ' milenia-entities milenia-entities--style-15';
        return $classes;
    }

    public function priceModification()
    {
        esc_html_e('from ', 'milenia-app-textdomain');
    }

    public function renderRevSliderGallery()
    {
        if(!function_exists('MPHB')) return;
        $roomType = MPHB()->getCurrentRoomType();
        ?>

		<div class="milenia-section milenia-section--stretched-content-no-px milenia-section--no-py">
			<div class="milenia-rev-slider-wrapper">
				<?php if($roomType->hasGallery()) : ?>
					<rs-module-wrap class="rev_slider_wrapper fullscreenbanner-container">
						<rs-module id="rev-slider-4" class="milenia-d-none rev-slider fullscreenabanner">
							<rs-slides>
								<?php foreach($roomType->getGalleryIds() as $gallery_image_id) : ?>
									<!--================ Slide ================-->
					                <rs-slide data-transition="fade" data-speed="300" data-delay="9000">
										<img src="<?php echo esc_url(wp_get_attachment_image_url($gallery_image_id, 'full')); ?>" alt="<?php echo esc_attr(wp_get_attachment_caption($gallery_image_id)); ?>" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">
									</rs-slide>
					                <!--================ End of Slide ================-->
								<?php endforeach; ?>
							</rs-slides>
						</rs-module>
					</rs-module-wrap>
                <?php elseif($roomType->hasFeaturedImage()) : ?>
                    <div class="milenia-full-height-image" data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url($roomType->getFeaturedImageId(), 'full')); ?>"></div>
				<?php endif;
    }

    public function renderBreadcrumbs()
    {
        if(!function_exists('MPHB')) return;
        $roomType = MPHB()->getCurrentRoomType();
        ?>
				<!--================ Breadcrumb ================-->
				<div class="milenia-section milenia-section--breadcrumb milenia-colorizer--scheme-dark milenia-colorizer--bg-color-opacity-80 milenia-entity-single milenia-entity--room">
					<div class="container">
						<div class="milenia-page-header">
							<div class="row align-items-center milenia-columns-aligner--edges-lg">
								<div class="col-lg-9">
									<?php
				                    /**
				                     * @hooked \MPHB\Views\SingleRoomTypeView::renderTitle - 10
				                     */
				                    do_action( 'milenia_mphb_render_single_room_type_title' );
				                    ?>
								</div>
								<div class="col-lg-3">
									<div class="milenia-entity-meta">
										<?php
			                            /**
			                             * @hooked \MPHB\Views\SingleRoomTypeView::renderPrice - 10
			                             */
			                            do_action( 'milenia_mphb_render_single_room_type_price' );
			                            ?>
									</div>
								</div>
							</div>
						</div>

						<?php
							if( function_exists( 'bcn_display' ) ) {
								echo '<nav class="milenia-breadcrumb-path">';
									bcn_display();
								echo '</nav>';
							}
						?>

                        <?php if($roomType->hasGallery()) : ?>
    						<div data-rev-api="roomRevApi" class="milenia-rev-arrows-outside milenia-action-buttons">
    							<button type="button" class="milenia-rev-arrows-prev"><i class="icon icon-chevron-left"></i></button>
    							<button type="button" class="milenia-rev-arrows-next"><i class="icon icon-chevron-right"></i></button>
    						</div>
                        <?php endif; ?>
					</div>
				</div>
				<!--================ End of Breadcrumb ================-->
			</div>
		</div>
	<?php
    }

    public function renderReviews()
    {
        global $RoomsReviewer;

        comments_template();

        if ( comments_open() ) : ?>
            <?php if( have_comments() ) : ?>
                <?php if(isset($RoomsReviewer) && !empty($RoomsReviewer->getCriterias())) : ?>
                    <div class="milenia-section milenia-section--py-medium">
                        <!--================ Estimate ================-->
                        <div class="milenia-estimate milenia-estimate--horizontal-xl">
                            <div class="milenia-estimate-mark milenia-aligner milenia-aligner--valign-middle">
                                <div class="milenia-aligner-outer">
                                    <div class="milenia-aligner-inner"><?php echo esc_html($RoomsReviewer->getTotalEstimate(get_the_ID())); ?><em class="milenia-estimate-mark-text"><?php echo esc_html($RoomsReviewer->getTotalEstimateName(get_the_ID())); ?></em></div>
                                </div>
                            </div>

                            <div class="milenia-estimate-bars">
                                <!--================ Progress Bars ================-->
                                <div class="milenia-progress-bars milenia-progress-bars--style-2">
                                    <?php foreach($RoomsReviewer->getCriterias() as $milenia_criteria => $milenia_criteria_name) :
                                        $criteria_estimate = $RoomsReviewer->getTotalEstimate(get_the_ID(), $milenia_criteria);
                                    ?>
                                        <div class="milenia-progress-bars-item">
                                            <strong id="progress-bar-title-<?php echo esc_attr($milenia_criteria); ?>" data-value="<?php echo esc_attr($criteria_estimate); ?>" class="milenia-progress-bar-title"><?php echo esc_html($milenia_criteria_name); ?></strong>
                                            <div role="progressbar" aria-valuenow="<?php echo esc_attr($criteria_estimate); ?>" aria-valuemin="0" aria-valuemax="5" aria-labelledby="progress-bar-title-<?php echo esc_attr($milenia_criteria); ?>" class="milenia-progress-bar">
                                                <div style="width: <?php echo esc_attr($criteria_estimate / 5 * 100); ?>%" class="milenia-progress-bar-indicator"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!--================ End of Progress Bars ================-->
                            </div>
                        </div>
                    <!--================ End of Estimate ================-->
                    </div>
                <?php endif; ?>

                <div class="milenia-section milenia-section--py-medium">
                    <h5><?php printf( _nx( 'Review (1)', 'Reviews (%1$s)', get_comments_number(), 'comments title', 'milenia-app-textdomain' ),
            				number_format_i18n( get_comments_number() ), get_the_title() ); ?></h5>

            		<ol class="comments-list <?php echo esc_attr(sprintf('comments-list--max-depth-%d', get_option('thread_comments_depth'))); ?>">
            		    <?php wp_list_comments(array(
            					'short_ping'  => true,
            					'avatar_size' => 70,
            					'callback' => 'milenia_review_comment'
            			)); ?>
            		</ol>
                </div>
            <?php endif; ?>

            <div class="milenia-section milenia-section--py-medium">
                <?php $commenter = wp_get_current_commenter();
                      $req = get_option( 'require_name_email' );
                      $aria_req_safe = ( $req ? " aria-required='true'" : '' );

                $comment_form_args = array(
                    'fields' => apply_filters('comment_form_default_fields', array(
                            'author' => '<div class="form-group">
                                            <div class="form-col">
                                                <label for="comment-form-author">'.esc_html__('Your Name', 'milenia-app-textdomain').' <span class="milenia-required-sign">*</span></label>
                                                <input id="comment-form-author" name="author" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" required/>
                                            </div>
                                        </div>',
                            'email'  => '<div class="form-group">
                                            <div class="form-col">
                                                <label for="comment-form-email">'.esc_html__('Your Email', 'milenia-app-textdomain').' <span class="milenia-required-sign">*</span></label>
                                                <input id="comment-form-email" name="email" class="form-control" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" required/>
                                            </div>
                                        </div>'
                    )),
                    'comment_field' =>  '<div class="form-group">
                                            <div class="form-col">
                                                <label for="comment-form-comment">'.esc_html__('Comment', 'milenia-app-textdomain').' <span class="milenia-required-sign">*</span></label>
                                                <textarea id="comment-form-comment" name="comment" rows="4" required></textarea>
                                            </div>
                                        </div>',
                    'comment_notes_before' => '<small class="form-caption">'.sprintf(esc_html__('Your email address will not be published. Fields marked with an %s are required.', 'milenia-app-textdomain'), '<span class="milenia-required-sign">*</span>').'</small>',
                    'comment_notes_after'  => '',
                    'class_submit'         => 'milenia-btn',
                    'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">'.esc_html__('Submit Review', 'milenia-app-textdomain').'</button>',
                    'submit_field' 		   => '<div class="form-group"><div class="form-col">%1$s %2$s</div></div>',
                    'title_reply_to'       => esc_html__( 'Leave a reply to %s', 'milenia-app-textdomain' ),
                    'cancel_reply_link'    => esc_html__( 'Cancel', 'milenia-app-textdomain' ),
                    'title_reply'          => esc_html__( 'Add a Review', 'milenia-app-textdomain' ),
                    'title_reply_before'   => '<h5>',
                    'title_reply_after'    => '</h5>',
                    'title_before'   => '<h5>',
                    'title_after'    => '</h5>'
                );

                comment_form($comment_form_args); ?>
            </div>
        <?php endif;
    }

    public static function renderRatesTableView()
    {
        if(!function_exists('MPHB')) return;

        global $Milenia;
        if(isset($Milenia)) {
	        $rates_note = get_post_meta(get_the_ID(), 'accommodation-rate-note', true);
        }

        $rates = MPHB()->getRateRepository()->findAllByRoomType(MPHB()->getCurrentRoomType()->getOriginalId());

        if(is_array($rates) && !empty($rates))
        {
            foreach($rates as $Rate)
            {
                if(!empty($Rate->getTitle())) :?>
                    <h5><?php echo esc_html($Rate->getTitle()); ?></h5>
                <?php endif;

                $seasonPrices = $Rate->getSeasonPrices();
                $seasons = $Rate->getSeasons();

	            $seasonPrices = array_reverse($seasonPrices);

                if(is_array($seasonPrices) && !empty($seasonPrices) && is_array($seasons) && !empty($seasons))
                { ?>
                    <table class="milenia-table--rates milenia-table--responsive-md">
                        <tbody>
                            <?php
                                foreach($seasonPrices as $SeasonPrice)
                                {
                                    ?>
                                        <tr>
                                            <td data-cell-title="<?php esc_attr_e('Season', 'milenia-app-textdomain'); ?>" class="milenia-color--black">
                                                <?php foreach($seasons as $Season) : ?>
                                                    <?php if($Season->getId() == $SeasonPrice->getSeasonId()) : ?>
                                                        <?php echo esc_html($Season->getTitle()) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </td>
                                            <td data-cell-title="<?php esc_attr_e('Period', 'milenia-app-textdomain'); ?>">
                                                <?php foreach($seasons as $Season) : ?>
                                                    <?php if($Season->getId() == $SeasonPrice->getSeasonId()) : ?>
                                                        <?php printf(
                                                            '%s - %s',
                                                            $Season->getStartDate()->format('j M Y'),
                                                            $Season->getEndDate()->format('j M Y')
                                                        ); ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </td>
                                            <td data-cell-title="<?php esc_attr_e('Price', 'milenia-app-textdomain'); ?>" class="milenia-color--black">
                                                <?php foreach($seasons as $Season) : ?>
                                                    <?php if($Season->getId() == $SeasonPrice->getSeasonId()) : ?>
                                                        <?php echo mphb_format_price($SeasonPrice->getPrice(), array(
                                                            'period' => true
                                                        )); ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                <?php }
            }

            if(isset($rates_note) && !empty($rates_note)) :
            ?><small class="milenia-table-label"><?php echo $rates_note; ?></small><?php
            endif;
            mphb_tmpl_the_loop_room_type_book_button();
        }
    }
}

?>
