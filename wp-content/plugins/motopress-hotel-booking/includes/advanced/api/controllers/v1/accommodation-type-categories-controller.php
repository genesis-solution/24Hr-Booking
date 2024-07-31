<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestTermsController;

class AccommodationTypeCategoriesController extends AbstractRestTermsController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mphb/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'accommodation_types/categories';

	/**
	 * Term name.
	 *
	 * @var string
	 */
	protected $taxonomy = 'mphb_room_type_category';

}
