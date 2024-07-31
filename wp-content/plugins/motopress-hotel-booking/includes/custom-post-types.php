<?php

namespace MPHB;

use \MPHB\PostType;

class CustomPostTypes {

	/**
	 *
	 * @var PostTypes\AttributesCPT
	 */
	private $attributes;

	/**
	 *
	 * @var PostTypes\RoomTypeCPT
	 */
	private $roomType;

	/**
	 *
	 * @var PostTypes\RoomCPT
	 */
	private $room;

	/**
	 *
	 * @var PostTypes\ServiceCPT
	 */
	private $service;

	/**
	 *
	 * @var PostTypes\BookingCPT
	 */
	private $booking;

	/**
	 *
	 * @var PostTypes\SeasonCPT
	 */
	private $season;

	/**
	 *
	 * @var PostTypes\RateCPT
	 */
	private $rate;

	/**
	 *
	 * @var PostTypes\PaymentCPT
	 */
	private $payment;

	/**
	 *
	 * @var PostTypes\ReservedRoomCPT
	 */
	private $reservedRoom;

	/**
	 *
	 * @var PostTypes\CouponCPT
	 */
	private $coupon;

	public function __construct() {
		$this->booking      = new PostTypes\BookingCPT();
		$this->roomType     = new PostTypes\RoomTypeCPT();
		$this->attributes   = new PostTypes\AttributesCPT();
		$this->season       = new PostTypes\SeasonCPT();
		$this->rate         = new PostTypes\RateCPT();
		$this->service      = new PostTypes\ServiceCPT();
		$this->room         = new PostTypes\RoomCPT();
		$this->payment      = new PostTypes\PaymentCPT();
		$this->reservedRoom = new PostTypes\ReservedRoomCPT();
		$this->coupon       = new PostTypes\CouponCPT();
	}

	/**
	 *
	 * @return PostTypes\RoomTypeCPT
	 */
	public function roomType() {
		return $this->roomType;
	}

	/**
	 *
	 * @return PostTypes\AttributesCPT
	 */
	public function attributes() {
		return $this->attributes;
	}

	/**
	 *
	 * @return PostTypes\RoomCPT
	 */
	public function room() {
		return $this->room;
	}

	/**
	 *
	 * @return PostTypes\ServiceCPT
	 */
	public function service() {
		return $this->service;
	}

	/**
	 *
	 * @return PostTypes\BookingCPT
	 */
	public function booking() {
		return $this->booking;
	}

	/**
	 *
	 * @return PostTypes\SeasonCPT
	 */
	public function season() {
		return $this->season;
	}

	/**
	 *
	 * @return PostTypes\RateCPT
	 */
	public function rate() {
		return $this->rate;
	}

	/**
	 *
	 * @return PostTypes\PaymentCPT
	 */
	public function payment() {
		return $this->payment;
	}

	/**
	 *
	 * @return PostTypes\ReservedRoomCPT
	 */
	public function reservedRoom() {
		return $this->reservedRoom;
	}

	/**
	 *
	 * @return PostTypes\CouponCPT
	 */
	public function coupon() {
		return $this->coupon;
	}

	public function getPostTypes() {
		return array(
			$this->booking,
			$this->roomType,
			$this->attributes,
			$this->season,
			$this->rate,
			$this->service,
			$this->room,
			$this->payment,
			$this->reservedRoom,
			$this->coupon,
		);
	}

	public function flushRewriteRules() {
		$this->roomType->register();
		$this->service->register();
		$this->booking->register();
		flush_rewrite_rules();
	}
}
