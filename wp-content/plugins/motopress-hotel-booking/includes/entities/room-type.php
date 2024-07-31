<?php

namespace MPHB\Entities;

use \MPHB\TaxesAndFees\TaxesAndFees;

class RoomType {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 *
	 * @var int
	 */
	private $originalId;

	/**
	 *
	 * @var string
	 */
	private $title;

	/**
	 *
	 * @var string
	 */
	private $description;

	/**
	 *
	 * @var string
	 */
	private $excerpt;

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
	 * @var int|string
	 *
	 * @since 3.7.2
	 */
	private $totalCapacity;

	/**
	 *
	 * @var string
	 */
	private $bedType;

	/**
	 *
	 * @var float
	 */
	private $size;

	/**
	 *
	 * @var string
	 */
	private $view;

	/**
	 *
	 * @var int[]
	 */
	private $servicesIds;

	/**
	 *
	 * @var \WP_Term[]
	 */
	private $categories;

	/**
	 *
	 * @var \WP_Term[]
	 */
	private $tags;

	/**
	 *
	 * @var \WP_Term[]
	 */
	private $facilities;

	/**
	 * @var array [%Attribute name% => [%Term ID% => %Term title%]]
	 *
	 * @see \MPHB\Repositories\RoomTypeRepository::mapPostToEntity()
	 */
	private $attributes;

	/**
	 *
	 * @var int
	 */
	private $imageId;

	/**
	 *
	 * @var int[]
	 */
	private $galleryIds;

	/**
	 *
	 * @var string
	 */
	private $status;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		$this->id            = $atts['id'];
		$this->originalId    = $atts['original_id'];
		$this->title         = $atts['title'];
		$this->description   = $atts['description'];
		$this->excerpt       = $atts['excerpt'];
		$this->adults        = $atts['adults'];
		$this->children      = $atts['children'];
		$this->totalCapacity = $atts['total_capacity'];
		$this->bedType       = $atts['bed_type'];
		$this->size          = $atts['size'];
		$this->view          = $atts['view'];
		$this->servicesIds   = $atts['services_ids'];
		$this->categories    = $atts['categories'];
		$this->tags          = $atts['tags'];
		$this->facilities    = $atts['facilities'];
		$this->attributes    = $atts['attributes'];
		$this->imageId       = $atts['image_id'];
		$this->galleryIds    = $atts['gallery_ids'];
		$this->status        = $atts['status'];
	}

	/**
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getOriginalId() {
		return $this->originalId;
	}

	/**
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return string
	 */
	public function getExcerpt() {
		return $this->excerpt;
	}

	/**
	 * Check is room type has gallery
	 *
	 * @return bool
	 */
	public function hasGallery() {
		return ! empty( $this->galleryIds );
	}

	/**
	 * Retrieve ids of gallery's attachments
	 *
	 * @return array
	 */
	public function getGalleryIds() {
		return $this->galleryIds;
	}

	/**
	 * Check is room type has featured image
	 *
	 * @return bool
	 */
	public function hasFeaturedImage() {
		return (bool) $this->imageId;
	}

	/**
	 * Retrieve room type featured image id.
	 *
	 * @return string | int Room type featured image ID or empty string.
	 */
	public function getFeaturedImageId() {
		return $this->imageId;
	}

	/**
	 * Retrieve room type categories terms objects
	 *
	 * @return \WP_Term[]
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * Retrieve room type tags terms objects
	 *
	 * @return \WP_Term[]
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 *
	 * @return \WP_Term[]
	 */
	public function getFacilities() {
		return $this->facilities;
	}

	/**
	 *
	 * @return array [%Attribute name% => [%Term ID% => %Term title%]]
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 *
	 * @return string
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 *
	 * @param bool $withUnits Optional. Whether to append units to size. Default FALSE.
	 * @return string
	 */
	public function getSize( $withUnits = false ) {
		return (string) ( $withUnits ? $this->size . MPHB()->settings()->units()->getSquareUnit() : $this->size );
	}

	/**
	 *
	 * @return string
	 */
	public function getBedType() {
		return $this->bedType;
	}

	/**
	 *
	 * @return int
	 */
	public function getAdultsCapacity() {
		return $this->adults;
	}

	/**
	 *
	 * @return int
	 */
	public function getChildrenCapacity() {
		return $this->children;
	}

	/**
	 * @return int|string
	 *
	 * @since 3.7.2
	 */
	public function getTotalCapacity() {
		return $this->totalCapacity;
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.2
	 */
	public function hasLimitedTotalCapacity() {
		return ! empty( $this->totalCapacity );
	}

	/**
	 * @return int
	 *
	 * @since 3.7.2
	 */
	public function calcTotalCapacity() {
		if ( $this->hasLimitedTotalCapacity() ) {
			return $this->totalCapacity;
		} else {
			return $this->adults + $this->children;
		}
	}

	public function getLink() {
		return get_permalink( $this->id );
	}

	/**
	 *
	 * @return bool
	 */
	public function hasServices() {
		return ! empty( $this->servicesIds );
	}

	/**
	 * Retrieve services available for this room type
	 *
	 * @return int[]
	 */
	public function getServices() {
		return $this->servicesIds;
	}

	/**
	 *
	 * @return array
	 */
	public function getServicesPriceList() {
		$prices = array();
		foreach ( $this->servicesIds as $serviceId ) {
			$service = MPHB()->getServiceRepository()->findById( $serviceId );
			if ( $service ) {
				$prices[ $service->getId() ] = $service->getPrice();
			}
		}
		return $prices;
	}

	/**
	 * Retrieve minimal average price from today to +X (from settings) days.
	 *
	 * @return float
	 *
	 * @deprecated 3.8.3
	 * @see mphb_get_room_type_base_price()
	 */
	public function getDefaultPrice() {
		return mphb_get_room_type_base_price( $this );
	}

	/**
	 * Retrieve minimal price for dates
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @return float
	 *
	 * @deprecated 3.8.3
	 * @see mphb_get_room_type_period_price()
	 */
	public function getDefaultPriceForDates( \DateTime $checkInDate, \DateTime $checkOutDate ) {
		return mphb_get_room_type_period_price( $checkInDate, $checkOutDate, $this );
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @since 3.9.8
	 *
	 * @return \MPHB\TaxesAndFees\TaxesAndFees object.
	 */
	public function getTaxesAndFees() {
		$taxesAndFees = new TaxesAndFees();

		$roomType = MPHB()->getRoomTypeRepository()->findById( $this->originalId );
		$taxesAndFees->setRoomType( $roomType );

		return $taxesAndFees;
	}

	/**
	 *
	 * @since 3.9.8
	 *
	 * @return bool
	 */
	public function hasTaxesAndFees() {
		return $this->getTaxesAndFees()->hasTaxesAndFees();
	}

}
