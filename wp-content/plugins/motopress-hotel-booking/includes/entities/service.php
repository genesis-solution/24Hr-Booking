<?php

namespace MPHB\Entities;

class Service {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $originalId;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var float
	 */
	protected $price;

	/**
	 * @var string
	 */
	protected $periodicity;

	protected $minQuantity;

	/**
	 * @var int|string Max quantity number or empty string (unlimited).
	 */
	protected $maxQuantity;

	protected $isAutoLimit;

	/**
	 * @var string
	 */
	protected $repeat;

	/**
	 * @param array $atts
	 */
	protected function __construct( $atts ) {
		$this->id          = $atts['id'];
		$this->originalId  = $atts['original_id'];
		$this->title       = $atts['title'];
		$this->description = $atts['description'];
		$this->periodicity = $atts['periodicity'];
		$this->minQuantity = $atts['min_quantity'];
		$this->maxQuantity = $atts['max_quantity'];
		$this->isAutoLimit = $atts['is_auto_limit'];
		$this->repeat      = $atts['repeat'];
		$this->price       = $atts['price'];
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
	 * @return float
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 *
	 * @return string
	 */
	public function getPeriodicity() {
		return $this->periodicity;
	}

	public function getMinQuantity() {
		return $this->minQuantity;
	}

	/**
	 * @return int|string
	 */
	public function getMaxQuantity() {
		return $this->maxQuantity;
	}

	/**
	 * @return int
	 */
	public function getMaxQuantityNumber() {
		return $this->maxQuantity !== '' ? $this->maxQuantity : 0;
	}

	public function isAutoLimit() {
		return $this->isAutoLimit;
	}

	public function isUnlimited() {
		return ! $this->isAutoLimit && $this->maxQuantity === '';
	}

	/**
	 *
	 * @return string
	 */
	public function getRepeatability() {
		return $this->repeat;
	}

	/**
	 *
	 * @return bool
	 */
	public function isPayPerNight() {
		return $this->periodicity === 'per_night';
	}

	/**
	 *
	 * @return bool
	 */
	public function isPayPerAdult() {
		return $this->repeat === 'per_adult';
	}

	public function isFlexiblePay() {
		return $this->periodicity == 'flexible';
	}

	/**
	 *
	 * @return bool
	 */
	public function isFree() {
		return $this->price == 0;
	}

	/**
	 *
	 * @param bool $repeatability Whether to show conditions of repeatedness. Default TRUE.*
	 * @param bool $periodicity Whether to show conditions of periodicity. Default TRUE.
	 * @param bool $literalFree Whether to replace 0 price to free label. Default TRUE.
	 *
	 * @return string
	 */
	public function getPriceWithConditions( $repeatability = true, $periodicity = true, $literalFree = true ) {

		$price = $this->getPriceHTML( $literalFree );

		if ( ! $this->isFree() ) {
			if ( $periodicity ) {
				$price .= ' / ';
				if ( $this->isPayPerNight() ) {
					$price .= __( 'Per Day', 'motopress-hotel-booking' );
				} elseif ( $this->isFlexiblePay() ) {
					$price .= __( 'Per Instance', 'motopress-hotel-booking' );
				} else {
					$price .= __( 'Once', 'motopress-hotel-booking' );
				}
			}
			if ( $repeatability ) {
				$price .= ' / ';
				if ( $this->isPayPerAdult() ) {
					$price .= __( 'Per Guest', 'motopress-hotel-booking' );
				} else {
					$price .= __( 'Per Accommodation', 'motopress-hotel-booking' );
				}
			}
		}

		return $price;
	}

	/**
	 *
	 * @param bool $literalFree
	 * @return string
	 */
	public function getPriceHTML( $literalFree = true ) {
		return mphb_format_price( $this->getPrice(), array( 'literal_free' => $literalFree ) );
	}

	/**
	 *
	 * @param array $atts
	 * @return Service
	 */
	public static function create( $atts ) {
		return new self( $atts );
	}

}
