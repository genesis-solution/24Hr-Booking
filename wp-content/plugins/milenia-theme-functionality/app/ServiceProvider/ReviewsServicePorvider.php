<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;
use Milenia\Core\Support\Reviewer\Reviewer;

class ReviewsServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        global $RoomsReviewer;
        global $Milenia;

        if(!isset($Milenia)) return;

        $rooms_criterias = $Milenia->getThemeOption('milenia-rooms-review-criterias');

        if(!empty($rooms_criterias))
        {
            $criterias = explode(',', $rooms_criterias);
            $prepared_criterias = array();

            foreach($criterias as $criteria)
            {
                $prepared_criterias[strtolower(preg_replace('/\s+/', '_', trim($criteria)))] = $criteria;
            }

            $RoomsReviewer = new Reviewer(array('mphb_room_type'), $prepared_criterias);
        }
    }
}
?>
