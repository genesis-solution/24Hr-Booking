<?php

namespace MPHB\Shortcodes;

class SearchResultsShortcode extends AbstractShortcode {

	protected $name = 'mphb_search_results';

	const NONCE_NAME   = 'mphb-search-available-room-nonce';
	const NONCE_ACTION = 'mphb-search-available-room';

	/**
	 *
	 * @var int
	 */
	private $adults;

	/**
	 *
	 * @var int
	 */
	private $children;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkInDate;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkOutDate;

	/**
	 *
	 * @var array [%Attribute name% => %Term ID%]
	 */
	private $attributes = array();

	/**
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 *
	 * @var bool
	 */
	private $isCorrectInputData = false;

	/**
	 *
	 * @var bool
	 */
	private $isCorrectPage = false;

	/**
	 *
	 * @var array
	 */
	private $availableRoomsCount;

	/**
	 *
	 * @var bool
	 */
	private $isShowGallery;

	/**
	 *
	 * @var bool
	 */
	private $isShowFeaturedImage;

	/**
	 *
	 * @var bool
	 */
	private $isShowTitle;

	/**
	 *
	 * @var bool
	 */
	private $isShowExcerpt;

	/**
	 *
	 * @var bool
	 */
	private $isShowDetails;

	/**
	 *
	 * @var bool
	 */
	private $isShowPrice;

	/**
	 *
	 * @var bool
	 */
	private $isShowViewButton;

	/**
	 *
	 * @var array
	 */
	private $order = array();

	/**
	 *
	 * @var bool
	 */
	private $isSortByPrice = false;

	/**
	 *
	 * @var int
	 */
	private $stickedRoomType;

	public function addActions() {
		parent::addActions();
		add_action( 'wp', array( $this, 'setup' ) );

		add_filter( 'the_posts', array( $this, 'stickRequestedRoomType' ), 10, 2 );

		add_action( 'mphb_sc_search_results_before_loop', array( $this, 'renderRecommendation' ) );
		add_action( 'mphb_sc_search_results_before_loop', array( $this, 'renderReservationCart' ) );

		add_action( 'mphb_sc_search_results_render_gallery', array( '\MPHB\Views\LoopRoomTypeView', 'renderGallery' ) );
		add_action( 'mphb_sc_search_results_render_image', array( '\MPHB\Views\LoopRoomTypeView', 'renderFeaturedImage' ) );
		add_action( 'mphb_sc_search_results_render_title', array( '\MPHB\Views\LoopRoomTypeView', 'renderTitle' ) );
		add_action( 'mphb_sc_search_results_render_excerpt', array( '\MPHB\Views\LoopRoomTypeView', 'renderExcerpt' ) );
		add_action( 'mphb_sc_search_results_render_details', array( '\MPHB\Views\LoopRoomTypeView', 'renderAttributes' ) );
		add_action( 'mphb_sc_search_results_render_price', array( '\MPHB\Views\LoopRoomTypeView', 'renderPriceForDates' ), 10, 2 );
		add_action( 'mphb_sc_search_results_render_view_button', array( '\MPHB\Views\LoopRoomTypeView', 'renderViewDetailsButton' ) );
		add_action( 'mphb_sc_search_results_render_book_button', array( $this, 'renderBookButton' ) );

		add_action( 'mphb_sc_search_results_error', array( '\MPHB\Views\GlobalView', 'prependBr' ) );
	}

	public function render( $atts, $content, $shortcodeName ) {

		$defaultAtts = array(
			'gallery'         => 'true',
			'featured_image'  => 'true',
			'title'           => 'true',
			'excerpt'         => 'true',
			'details'         => 'true',
			'price'           => 'true',
			'view_button'     => 'true',
			// Use nulls to determine which one of the "default_sorting" and
			// "orderby" the customer using
			'default_sorting' => null, // "order" was by default
			'orderby'         => null, // "menu_order" by default
			'order'           => 'ASC',
			'meta_key'        => '',
			'meta_type'       => '',
			'class'           => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		// Fix "orderby" value for method AbstractShortcode::buildOrderQuery()
		$defaultAtts['orderby'] = 'menu_order';

		// Force use of the new field "orderby" instead of "default_sorting"
		if ( is_null( $atts['orderby'] ) ) {
			$atts['orderby'] = $defaultAtts['orderby'];
			// Support deprecated attribute "default_sorting" (v2.7.4-); values:
			// "order" (by default) and "price"
			if ( ! is_null( $atts['default_sorting'] ) ) {
				$atts['orderby'] = ( $atts['default_sorting'] == 'price' ) ? 'post__in' : 'menu_order';
			}
		} elseif ( $atts['orderby'] == 'price' ) {
			$atts['orderby'] = 'post__in';
		} elseif ( $atts['orderby'] == 'order' ) {
			$atts['orderby'] = 'menu_order';
		}

		$this->isShowGallery       = \MPHB\Utils\ValidateUtils::validateBool( $atts['gallery'] );
		$this->isShowFeaturedImage = \MPHB\Utils\ValidateUtils::validateBool( $atts['featured_image'] );
		$this->isShowTitle         = \MPHB\Utils\ValidateUtils::validateBool( $atts['title'] );
		$this->isShowExcerpt       = \MPHB\Utils\ValidateUtils::validateBool( $atts['excerpt'] );
		$this->isShowDetails       = \MPHB\Utils\ValidateUtils::validateBool( $atts['details'] );
		$this->isShowPrice         = \MPHB\Utils\ValidateUtils::validateBool( $atts['price'] );
		$this->isShowViewButton    = \MPHB\Utils\ValidateUtils::validateBool( $atts['view_button'] );
		$this->order               = $this->buildOrderQuery( $atts, $defaultAtts );

		$this->isSortByPrice = ( $this->order['orderby'] == 'post__in' ); // The logic of the old default_sorting="price"

		ob_start();

		if ( $this->isCorrectPage && $this->isCorrectInputData ) {

			$this->setupMatchedRoomTypes();

			MPHB()->getPublicScriptManager()->enqueue();

			if ( ! empty( $this->availableRoomsCount ) ) {
				$this->mainLoop();
			} else {
				$this->renderResultsInfo( 0 );
			}
		} else {
			$this->showErrorsMessage();
		}

		$content = ob_get_clean();

		$wrapperClass  = apply_filters( 'mphb_sc_search_results_wrapper_class', 'mphb_sc_search_results-wrapper' );
		$wrapperClass .= empty( $wrapperClass ) ? $atts['class'] : ' ' . $atts['class'];
		return '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
	}

	private function mainLoop() {

		$roomTypesQuery = $this->getRoomTypesQuery();

		$this->renderResultsInfo( $roomTypesQuery->post_count );

		if ( $roomTypesQuery->have_posts() ) {

			do_action( 'mphb_sc_search_results_before_loop', $roomTypesQuery );

			while ( $roomTypesQuery->have_posts() ) :
				$roomTypesQuery->the_post();

				do_action( 'mphb_sc_search_results_before_room' );

				$this->renderRoomType();

				do_action( 'mphb_sc_search_results_after_room' );

			endwhile;

			do_action( 'mphb_sc_search_results_after_loop', $roomTypesQuery );

			wp_reset_postdata();
		}
	}

	/**
	 *
	 * @return \WP_Query
	 */
	private function getRoomTypesQuery() {
		$queryAtts = array_merge(
			$this->order, // <- "orderby", "order", "meta_key", "meta_type"
			array(
				'post_type'           => MPHB()->postTypes()->roomType()->getPostType(),
				'post_status'         => 'publish',
				'post__in'            => array_keys( $this->availableRoomsCount ),
				'posts_per_page'      => -1,
				'ignore_sticky_posts' => true,
			)
		);

		if ( ! empty( $this->stickedRoomType ) ) {
			$queryAtts['mphb_stick_post'] = $this->stickedRoomType;
		}

		// The value of the "order" parameter does not change the resulting sort
		// order if "orderby" is "post__in", so do it manually
		if ( $this->order['orderby'] == 'post__in' && $this->order['order'] == 'DESC' ) {
			$queryAtts['post__in'] = array_reverse( $queryAtts['post__in'] );
		}

		return new \WP_Query( $queryAtts );
	}

	/**
	 * @return array of arrays [string $id, string $count]
	 *
	 * @global \wpdb $wpdb
	 *
	 * @since 3.7.0 added new filter - "mphb_search_available_rooms".
	 */
	private function getAvailableRoomTypes() {
		global $wpdb;

		$roomsAtts = apply_filters(
			'mphb_search_available_rooms',
			array(
				'availability'      => 'locked',
				'from_date'         => $this->checkInDate,
				'to_date'           => $this->checkOutDate,
				'skip_buffer_rules' => false,
			)
		);

		$lockedRooms    = MPHB()->getRoomPersistence()->searchRooms( $roomsAtts );
		$lockedRoomsStr = join( ',', $lockedRooms );

		$query = 'SELECT DISTINCT room_types.ID AS id, COUNT(DISTINCT rooms.ID) AS count'
			. " FROM {$wpdb->posts} AS rooms";

		$join = " INNER JOIN {$wpdb->postmeta} AS room_type_ids"
			. " ON rooms.ID = room_type_ids.post_id AND room_type_ids.meta_key = 'mphb_room_type_id'"
			. " INNER JOIN {$wpdb->posts} AS room_types"
			. ' ON room_type_ids.meta_value = room_types.ID';

		$where = ' WHERE 1=1'
			. " AND rooms.post_type = '" . MPHB()->postTypes()->room()->getPostType() . "'"
			. " AND rooms.post_status = 'publish'"
			. ( ! empty( $lockedRoomsStr ) ? " AND rooms.ID NOT IN ({$lockedRoomsStr})" : '' )

			. ' AND room_type_ids.meta_value IS NOT NULL'
			. " AND room_type_ids.meta_value != ''"

			. " AND room_types.post_type = '" . MPHB()->postTypes()->roomType()->getPostType() . "'"
			. " AND room_types.post_status = 'publish'";

		$order = ' GROUP BY room_type_ids.meta_value'
			. ' ORDER BY room_type_ids.meta_value DESC';

		if ( ! empty( $this->attributes ) ) {
			// Add attributes to the query. At the moment the relation between
			// attributes is OR. Later we need to check, that every room type
			// have each required term (change relation to AND)

			$inTerms    = MPHB()->translation()->translateAttributes( $this->attributes, MPHB()->translation()->getDefaultLanguage() );
			$inTerms    = array_unique( $inTerms );
			$inTermsStr = join( ',', $inTerms );

			// "object_id" can differ from "term_taxonomy_id"; see issue [MB-935]
			$join .= " INNER JOIN {$wpdb->term_relationships} AS room_relationships"
				. ' ON room_types.ID = room_relationships.object_id'
				. " INNER JOIN {$wpdb->term_taxonomy} AS room_attributes"
				. ' ON room_relationships.term_taxonomy_id = room_attributes.term_taxonomy_id';

			$where .= " AND room_attributes.term_id IN ({$inTermsStr})"; // Here term ID can be any from the required list
		}

		$roomTypeDetails = $wpdb->get_results( $query . $join . $where . $order, ARRAY_A );

		return $roomTypeDetails;
	}

	private function renderRoomType() {

		$templateAtts = array(
			'checkInDate'      => $this->checkInDate,
			'checkOutDate'     => $this->checkOutDate,
			'adults'           => $this->adults,
			'children'         => $this->children,
			'isShowGallery'    => $this->isShowGallery,
			'isShowImage'      => $this->isShowFeaturedImage,
			'isShowTitle'      => $this->isShowTitle,
			'isShowExcerpt'    => $this->isShowExcerpt,
			'isShowDetails'    => $this->isShowDetails,
			'isShowPrice'      => $this->isShowPrice,
			'isShowViewButton' => $this->isShowViewButton,
			// disabling book button by shortcode attribute is deprecated
			'isShowBookButton' => true,
		);
		mphb_get_template_part( 'shortcodes/search-results/room-content', $templateAtts );
	}

	/**
	 *
	 * @return false|\WP_Query
	 */
	private function setupMatchedRoomTypes() {

		$checkInDate  = $this->checkInDate;
		$checkOutDate = $this->checkOutDate;

		// Don't use $this->attributes in callback. Fixes "Fatal error: Using
		// $this when not in object context" on PHP 5.3
		$attributes = $this->attributes;

		/**
		 * @since 2.4.0
		 */
		do_action( 'mphb_sc_search_results_before_search' );

		$roomTypeDetailsList = $this->getAvailableRoomTypes();

		$roomTypeDetailsList = array_filter(
			$roomTypeDetailsList,
			function( $roomTypeDetails ) use ( $checkInDate, $checkOutDate, $attributes ) {

				$roomTypeId = $roomTypeDetails['id'];

				$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

				if ( ! $roomType ) {
					return false;
				}

				// Room type must have all the attributes we searched
				if ( count( $attributes ) > 1 ) {
					// ... But we need to do the additional check only when searched
					// attributes count > 1; when count <= 1, then we already find
					// the right result

					$roomAttributes = $roomType->getAttributes();

					foreach ( $attributes as $attributeName => $termId ) {
						if ( ! isset( $roomAttributes[ $attributeName ] )
						|| ! array_key_exists( $termId, $roomAttributes[ $attributeName ] )
						) {
							// The room type does not have a required attribute/term ID
							return false;
						}
					}
				}

				// Search for the rate
				$rateAtts = array(
					'check_in_date'  => $checkInDate,
					'check_out_date' => $checkOutDate,
				);

				if ( ! MPHB()->getRateRepository()->isExistsForRoomType( $roomTypeId, $rateAtts ) ) {
					return false;
				}

				if ( ! MPHB()->getRulesChecker()->verify( $checkInDate, $checkOutDate, $roomTypeId ) ) {
					return false;
				}

				return true;
			}
		);

		if ( $this->isSortByPrice ) { // Equivalent to the old default_sorting="price"
			$roomTypesPriceList = array_map(
				function( $roomTypeDetails ) use ( $checkInDate, $checkOutDate ) {
					$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeDetails['id'] );
					return mphb_get_room_type_period_price( $checkInDate, $checkOutDate, $roomType );
				},
				$roomTypeDetailsList
			);

			// Replace numeric indexes with room type IDs
			$roomTypesPriceList = array_combine( wp_list_pluck( $roomTypeDetailsList, 'id' ), $roomTypesPriceList );

			usort(
				$roomTypeDetailsList,
				function( $roomType1, $roomType2 ) use ( $roomTypesPriceList ) {
					return $roomTypesPriceList[ $roomType1['id'] ] > $roomTypesPriceList[ $roomType2['id'] ] ? 1 : -1;
				}
			);
		}

		// Verify available rooms count
		array_walk(
			$roomTypeDetailsList,
			function( &$roomTypeDetails ) use ( $checkInDate, $checkOutDate ) {
				$roomTypeId                = $roomTypeDetails['id'];
				$unavailableRoomsCount     = MPHB()->getRulesChecker()->customRules()->getUnavailableRoomsCount( $checkInDate, $checkOutDate, $roomTypeId );
				$roomTypeDetails['count'] -= $unavailableRoomsCount;
			}
		);

		$roomTypeDetailsList = array_filter(
			$roomTypeDetailsList,
			function( $roomTypeDetails ) {
				return $roomTypeDetails['count'] > 0;
			}
		);

		// array_combine() issues E_WARNING on PHP 5.4- if one of the array is empty
		$ids    = wp_list_pluck( $roomTypeDetailsList, 'id' );
		$counts = wp_list_pluck( $roomTypeDetailsList, 'count' );
		if ( ! empty( $ids ) && ! empty( $counts ) ) {
			$this->availableRoomsCount = array_combine( $ids, $counts );
		}
	}

	private function setupSearchData() {

		$this->adults             = null;
		$this->children           = null;
		$this->checkInDate        = null;
		$this->checkOutDate       = null;
		$this->isCorrectInputData = false;

		$input = $_GET;

		if ( isset( $input['mphb_adults'], $input['mphb_children'], $input['mphb_check_in_date'], $input['mphb_check_out_date'] ) ) {

			$this->parseInputData( $input );

			if ( $this->isCorrectInputData ) {
				MPHB()->searchParametersStorage()->save(
					array(
						'mphb_check_in_date'  => $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_check_out_date' => $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_adults'         => $this->adults,
						'mphb_children'       => $this->children,
					)
				);
			}
		} elseif ( MPHB()->searchParametersStorage()->hasStored() ) {
			$this->parseInputData( MPHB()->searchParametersStorage()->get() );
		}

		if ( ! empty( $input['mphb_room_type_id'] ) ) {
			$roomTypeId            = \MPHB\Utils\ValidateUtils::validateInt( $input['mphb_room_type_id'], 1 );
			$this->stickedRoomType = $roomTypeId ? $roomTypeId : null;
		}
	}

	/**
	 *
	 * @return bool
	 */
	private function parseInputData( $input ) {
		$isCorrectAdults       = $this->parseAdults( $input['mphb_adults'] );
		$isCorrectChildren     = $this->parseChildren( $input['mphb_children'] );
		$isCorrectCheckInDate  = $this->parseCheckInDate( $input['mphb_check_in_date'] );
		$isCorrectCheckOutDate = $this->parseCheckOutDate( $input['mphb_check_out_date'] );

		if ( isset( $input['mphb_attributes'] ) ) {
			$this->parseAttributes( $input['mphb_attributes'] );
		}

		$this->isCorrectInputData = ( $isCorrectAdults && $isCorrectChildren && $isCorrectCheckInDate && $isCorrectCheckOutDate );

		return $this->isCorrectInputData;
	}

	public function setup() {
		if ( mphb_is_search_results_page() ) {
			$this->isCorrectPage = true;
			$this->setupSearchData();
			/**
			 * @since 2.4.0
			 */
			do_action(
				'mphb_load_search_results_page',
				array(
					'check_in_date'  => $this->checkInDate,
					'check_out_date' => $this->checkOutDate,
					'adults'         => $this->adults,
					'children'       => $this->children,
					'is_correct'     => $this->isCorrectInputData,
				)
			);
		}
	}

	/**
	 *
	 * @param string|int $adults
	 * @return bool
	 */
	private function parseAdults( $adults ) {
		$adults = intval( $adults );
		if ( $adults >= MPHB()->settings()->main()->getMinAdults() && $adults <= MPHB()->settings()->main()->getSearchMaxAdults() ) {
			$this->adults = $adults;
			return true;
		} else {
			$this->errors[] = __( 'Adults number is not valid.', 'motopress-hotel-booking' );
			return false;
		}
	}

	/**
	 *
	 * @param int|string $children
	 * @return boolean
	 */
	private function parseChildren( $children ) {
		$children = intval( $children );
		if ( $children >= MPHB()->settings()->main()->getMinChildren() && $children <= MPHB()->settings()->main()->getSearchMaxChildren() ) {
			$this->children = $children;
			return true;
		} else {
			$this->errors[] = __( 'Children number is not valid.', 'motopress-hotel-booking' );
			return false;
		}
	}

	/**
	 *
	 * @param string $date Date in front date format
	 * @return boolean
	 */
	private function parseCheckInDate( $date ) {
		$checkInDateObj = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );
		$todayDate      = \DateTime::createFromFormat( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );

		if ( ! $checkInDateObj ) {
			$this->errors[] = __( 'Check-in date is not valid.', 'motopress-hotel-booking' );
			return false;
		} elseif ( \MPHB\Utils\DateUtils::calcNights( $todayDate, $checkInDateObj ) < 0 ) {
			$this->errors[] = __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkInDate = $checkInDateObj;
		return true;
	}

	/**
	 *
	 * @param string $date Date in front date format
	 * @return boolean
	 */
	private function parseCheckOutDate( $date ) {

		$checkOutDateObj = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );

		if ( ! $checkOutDateObj ) {
			$this->errors[] = __( 'Check-out date is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		if ( isset( $this->checkInDate ) &&
			! MPHB()->getRulesChecker()->verify( $this->checkInDate, $checkOutDateObj )
		) {
			$this->errors[] = __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkOutDate = $checkOutDateObj;
		return true;
	}

	private function parseAttributes( $attributes ) {
		foreach ( $attributes as $attributeName => $id ) {
			if ( empty( $id ) ) {
				continue;
			}

			$attributeName = mphb_sanitize_attribute_name( $attributeName );
			$id            = absint( $id );

			$this->attributes[ $attributeName ] = $id;
		}
	}

	public function showErrorsMessage() {
		$templateAtts = array(
			'errors' => $this->errors,
		);
		mphb_get_template_part( 'shortcodes/search-results/errors', $templateAtts );
	}

	/**
	 *
	 * @param int $roomTypeCount
	 */
	private function renderResultsInfo( $roomTypeCount ) {
		$templateAtts = array(
			'roomTypesCount' => $roomTypeCount,
			'adults'         => $this->adults,
			'children'       => $this->children,
			'checkInDate'    => \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkInDate ),
			'checkOutDate'   => \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkOutDate ),
		);
		mphb_get_template_part( 'shortcodes/search-results/results-info', $templateAtts );
	}

	public function renderReservationCart() {

		do_action( 'mphb_sc_search_results_reservation_cart_before' );

		$title = apply_filters( 'mphb_sc_search_results_reservation_cart_title', '' );

		if ( $title ) {
			?>
			<h2 class="mphb-reservation-cart-title">
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				echo $title;
			?>
				</h2>
		<?php } ?>
		<form action="<?php echo esc_url( MPHB()->settings()->pages()->getCheckoutPageUrl() ); ?>"
			  method="POST"
			  id="mphb-reservation-cart"
			  class="mphb-reservation-cart mphb-empty-cart">
			<input type="hidden" name="mphb_check_in_date"
				   value="<?php echo esc_attr( $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"/>
			<input type="hidden" name="mphb_check_out_date"
				   value="<?php echo esc_attr( $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"/>
				   <?php wp_nonce_field( \MPHB\Shortcodes\CheckoutShortcode::NONCE_ACTION_CHECKOUT, \MPHB\Shortcodes\CheckoutShortcode::NONCE_NAME, true ); ?>
			<div class="mphb-reservation-details">
				<p class="mphb-empty-cart-message"><?php esc_html_e( 'Select from available accommodations.', 'motopress-hotel-booking' ); ?></p>
				<p class="mphb-cart-message"></p>
				<p class="mphb-cart-total-price">
					<span class="mphb-cart-total-price-title">
						<?php esc_html_e( 'Total:', 'motopress-hotel-booking' ); ?>
					</span>
					<span class="mphb-cart-total-price-value"></span>
				</p>
			</div>
			<button class="button mphb-button mphb-confirm-reservation"><?php esc_html_e( 'Confirm Reservation', 'motopress-hotel-booking' ); ?></button>
			<div class="mphb-clear"></div>
		</form>
		<?php
		do_action( 'mphb_sc_search_results_reservation_cart_after' );
	}

	/**
	 *
	 * @param \WP_Query $roomTypesQuery
	 */
	public function renderRecommendation( $roomTypesQuery ) {

		if ( ! MPHB()->settings()->main()->isEnabledRecommendation() ) {
			return;
		}

		$adults         = $this->adults;
		$children       = $this->children;
		$availableRooms = array_map( 'intval', $this->availableRoomsCount );
		$recommendation = $this->generateRecommmendation( $adults, $children, $availableRooms );

		if ( empty( $recommendation ) ) {
			return;
		}

		do_action( 'mphb_sc_search_results_recommendation_before' );

		$childrenAllowed = MPHB()->settings()->main()->isChildrenAllowed();
		$guestsAllowed   = MPHB()->settings()->main()->isAdultsAllowed();

		if ( $childrenAllowed ) {
			$title = sprintf( _n( 'Recommended for %d adult', 'Recommended for %d adults', $this->adults, 'motopress-hotel-booking' ), $this->adults );
			if ( ! empty( $this->children ) ) {
				$title .= sprintf( _n( ' and %d child', ' and %d children', $this->children, 'motopress-hotel-booking' ), $this->children );
			}
		} else {
			$title = sprintf( _n( 'Recommended for %d guest', 'Recommended for %d guests', $this->adults, 'motopress-hotel-booking' ), $this->adults );
		}

		$title = apply_filters( 'mphb_sc_search_results_recommendation_title', $title );

		?>
		<h2 class="mphb-recommendation-title">
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $title;
		?>
			</h2>
		<form action="<?php echo esc_url( MPHB()->settings()->pages()->getCheckoutPageUrl() ); ?>"
			  method="POST"
			  id="mphb-recommendation"
			  class="mphb-recommendation">
				  <?php wp_nonce_field( \MPHB\Shortcodes\CheckoutShortcode::NONCE_ACTION_CHECKOUT, \MPHB\Shortcodes\CheckoutShortcode::RECOMMENDATION_NONCE_NAME, true ); ?>
			<input type="hidden" name="mphb_check_in_date"
				   value="<?php echo esc_attr( $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"/>
			<input type="hidden" name="mphb_check_out_date"
				   value="<?php echo esc_attr( $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"/>
			<ul class="mphb-recommendation-details-list">
				<?php
				$total = 0;
				foreach ( $recommendation as $roomTypeId => $roomsCount ) {
					$roomType     = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
					$roomType     = apply_filters( '_mphb_translate_room_type', $roomType, null );
					$roomAdults   = $roomType->getAdultsCapacity();
					$roomChildren = $roomType->getChildrenCapacity();
					$roomCapacity = $roomType->getTotalCapacity();
					$nights       = \MPHB\Utils\DateUtils::calcNights( $this->checkInDate, $this->checkOutDate );
					$roomPrice    = mphb_get_room_type_period_price( $this->checkInDate, $this->checkOutDate, $roomType );
					$price        = $roomPrice * $roomsCount;

					$taxesAndFees = $roomType->getTaxesAndFees();
					$taxesAndFees->setRoomPrice( $price );
					$taxesAndFees->setupParams(
						array(
							'period_nights'         => $nights,
							'accommodations_amount' => $roomsCount,
						)
					);

					if ( count( $recommendation ) > 1 ) {
						$taxesAndFees->setDefined( false );
					}

					/**
					 * @since 3.9.8
					 *
					 * @param float $price
					 * @param \MPHB\TaxesAndFees\TaxesAndFees
					 */
					$price = apply_filters( 'mphb_recommended_room_types_items_for_dates', $price, $taxesAndFees );

					$total += $price;
					?>
					<li>
						<input name="mphb_rooms_details[<?php echo esc_attr( $roomType->getOriginalId() ); ?>]" type="hidden" value="<?php echo esc_attr( $roomsCount ); ?>">
						<div class="mphb-recommendation-item">
							<span class="mphb-recommedation-item-subtotal">
								<?php
								/** This filter is documented in template-functions.php **/
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo apply_filters(
									'mphb_tmpl_the_room_type_price_for_dates',
									mphb_format_price( $price ),
									$taxesAndFees,
									array(),
									$price
								);
								?>
							</span>
							<span class="mphb-recommendation-item-count"><?php echo esc_html( $roomsCount ) . ' &times; '; ?></span>
							<a href="<?php echo esc_url( $roomType->getLink() ); ?>" class="mphb-recommendation-item-link" target="_blank">
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $roomType->getTitle();
								?>
							</a>
							<?php if ( $guestsAllowed ) { ?>
								<small class="mphb-recommendation-item-guests">
									<span class="mphb-recommendation-item-guests-label">
										<?php esc_html_e( 'Max occupancy:', 'motopress-hotel-booking' ); ?>
									</span>

									<?php if ( $roomType->hasLimitedTotalCapacity() ) { ?>
										<span class="mphb-recommendation-item-total-capacity mphb-total-capacity-<?php echo esc_attr( $roomCapacity ); ?>">
											<?php echo esc_html( sprintf( _n( '%d guest', '%d guests', $roomCapacity, 'motopress-hotel-booking' ), $roomCapacity ) ); ?>
										</span>
									<?php } elseif ( ! $childrenAllowed ) { ?>
										<span class="mphb-recommendation-item-adults mphb-adults-<?php echo esc_attr( $roomAdults ); ?>">
											<?php echo esc_html( sprintf( _n( '%d guest', '%d guests', $roomAdults, 'motopress-hotel-booking' ), $roomAdults ) ); ?>
										</span>
									<?php } else { ?>
										<span class="mphb-recommendation-item-adults mphb-adults-<?php echo esc_attr( $roomAdults ); ?>">
											<?php echo esc_html( sprintf( _n( '%d adult', '%d adults', $roomAdults, 'motopress-hotel-booking' ), $roomAdults ) ); ?>
										</span>

										<?php if ( $roomChildren > 0 ) { ?>
											<span class="mphb-recommendation-item-adults-children-separator"><?php echo ', '; ?></span>
											<span class="mphb-recommendation-item-children mphb-children-<?php echo esc_attr( $roomChildren ); ?>">
												<?php echo esc_html( sprintf( _n( '%d child', '%d children', $roomChildren, 'motopress-hotel-booking' ), $roomChildren ) ); ?>
											</span>
										<?php } ?>
									<?php } ?>
								</small>
							<?php } ?>
						</div>
					</li>
				<?php } ?>
			</ul>
			<p	 class="mphb-recommendation-total">
				<span class="mphb-recommendation-total-title"><?php esc_html_e( 'Total:', 'motopress-hotel-booking' ); ?></span>
				<span class="mphb-recommendation-total-value">
					<?php
					/**
					 *
					 * @since 3.9.8
					 *
					 * @param string Price html
					 * @param \MPHB\TaxesAndFees\TaxesAndFees
					 * @param array Price attributes
					 * @param float $total Total Price
					 **/
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo apply_filters(
						'mphb_tmpl_the_total_recommended_price_for_dates',
						mphb_format_price( $total ),
						$taxesAndFees,
						array(),
						$total
					);
					?>
				</span>
			</p>
			<button class="button mphb-button mphb-recommendation-reserve-button">
				<?php esc_html_e( 'Reserve', 'motopress-hotel-booking' ); ?>
			</button>
			<div class="mphb-clear"></div>
		</form>
		<?php
		do_action( 'mphb_sc_search_results_recommendation_after' );
	}

	/**
	 * Generate basic naive recommendation
	 *
	 * @param int   $adults
	 * @param int   $children
	 * @param array $availableRooms Room type ids as keys and rooms count as values.
	 * @param bool  $strict Optional. Forbid incomplete allocation. Default FALSE.
	 * @return array
	 */
	private function generateRecommmendation( $adults, $children, $availableRooms, $strict = false ) {
		$adults   = max( 0, $adults );
		$children = max( 0, $children );

		if ( $adults == 0 && $children == 0 ) {
			return array();
		}

		$recommendation = new \MPHB\Recommendation( $availableRooms );
		return $recommendation->generate( $adults, $children, $strict );
	}

	public function renderBookButton() {
		$roomType      = MPHB()->getCurrentRoomType();
		$maxRoomsCount = isset( $this->availableRoomsCount[ $roomType->getOriginalId() ] ) ? $this->availableRoomsCount[ $roomType->getOriginalId() ] : 0;
		$roomPrice     = apply_filters( 'mphb_sc_search_results_data_room_price', mphb_get_room_type_period_price( $this->checkInDate, $this->checkOutDate, $roomType ) );
		?>
		<div class="mphb-reserve-room-section"
			 data-room-type-id="<?php echo esc_attr( $roomType->getOriginalId() ); ?>"
			 data-room-type-title="<?php echo esc_attr( $roomType->getTitle() ); ?>"
			 data-room-price="<?php echo esc_attr( $roomPrice ); ?>">
			
			<?php if ( 1 < $maxRoomsCount ) : ?>
				<p class="mphb-rooms-quantity-wrapper mphb-rooms-quantity-multiple">
					<select class="mphb-rooms-quantity" id="mphb-rooms-quantity-<?php echo esc_attr( $roomType->getOriginalId() ); ?>">
						<?php for ( $count = 1; $count <= $maxRoomsCount; $count++ ) { ?>
							<option value="<?php echo esc_attr( $count ); ?>">
													  <?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
														echo $count;
														?>
								</option>
						<?php } ?>
					</select>
					<span class="mphb-available-rooms-count">
					<?php
						echo esc_html( sprintf( _n( 'of %d accommodation available.', 'of %d accommodations available.', $maxRoomsCount, 'motopress-hotel-booking' ), $maxRoomsCount ) );
					?>
						</span>
				</p>
			<?php endif; ?>

			<div class="mphb-rooms-reservation-message-wrapper">
				<a href="#" class="mphb-remove-from-reservation"><?php esc_html_e( 'Remove', 'motopress-hotel-booking' ); ?></a>
				<p class="mphb-rooms-reservation-message"></p>
			</div>
			<button class="button mphb-button mphb-book-button"><?php esc_html_e( 'Book', 'motopress-hotel-booking' ); ?></button>
			<button class="button mphb-button mphb-confirm-reservation"><?php esc_html_e( 'Confirm Reservation', 'motopress-hotel-booking' ); ?></button>
		</div>
		<?php
	}

	/**
	 *
	 * @param \WP_Post[] $posts
	 * @param \WP_Query  $wp_query
	 */
	public function stickRequestedRoomType( $posts, $wp_query ) {
		if ( ! $wp_query->get( 'mphb_stick_post' ) ) {
			return $posts;
		}
		$position = array_search( $this->stickedRoomType, wp_list_pluck( $posts, 'ID' ) );
		if ( false !== $position ) {
			$stickedPost = $posts[ $position ];
			unset( $posts[ $position ] );
			array_unshift( $posts, $stickedPost );
		}

		return $posts;
	}
}
