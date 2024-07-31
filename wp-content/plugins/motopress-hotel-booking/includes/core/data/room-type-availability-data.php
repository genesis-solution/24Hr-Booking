<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data transfer object for room type availability.
 */
class RoomTypeAvailabilityData {

	/**
	 * @var \MPHB\Core\RoomTypeAvailabilityStatus constant
	 */
	private $roomTypeStatus;
	private $availableRoomsCount;
	private $isCheckInDate;
	private $isCheckOutDate;
	private $isStayInNotAllowed;
	private $isCheckInNotAllowed;
	private $isCheckOutNotAllowed;
	private $isEarlierThanMinAdvanceDate;
	private $isLaterThanMaxAdvanceDate;
	private $minStayNights;
	private $maxStayNights = null;

	public function __construct(

		$roomTypeStatus,
		$availableRoomsCount = 0,
		$isCheckInDate = false,
		$isCheckOutDate = false,
		$isStayInNotAllowed = false,
		$isCheckInNotAllowed = false,
		$isCheckOutNotAllowed = false,
		$isEarlierThanMinAdvanceDate = false,
		$isLaterThanMaxAdvanceDate = false,
		$minStayNights = 1,
		$maxStayNights = null
	) {
		$this->roomTypeStatus              = $roomTypeStatus;
		$this->availableRoomsCount         = $availableRoomsCount;
		$this->isCheckInDate               = $isCheckInDate;
		$this->isCheckOutDate              = $isCheckOutDate;
		$this->isStayInNotAllowed          = $isStayInNotAllowed;
		$this->isCheckInNotAllowed         = $isCheckInNotAllowed;
		$this->isCheckOutNotAllowed        = $isCheckOutNotAllowed;
		$this->isEarlierThanMinAdvanceDate = $isEarlierThanMinAdvanceDate;
		$this->isLaterThanMaxAdvanceDate   = $isLaterThanMaxAdvanceDate;
		$this->minStayNights               = $minStayNights;

		if ( is_int( $maxStayNights ) && 0 < $maxStayNights ) {
			$this->maxStayNights = $maxStayNights;
		}
	}

	public function getRoomTypeStatus() {
		return $this->roomTypeStatus;
	}

	public function getAvailableRoomsCount() {
		return $this->availableRoomsCount;
	}

	public function isCheckInDate() {
		return $this->isCheckInDate;
	}

	public function isCheckOutDate() {
		return $this->isCheckOutDate;
	}

	public function isStayInNotAllowed() {
		return $this->isStayInNotAllowed;
	}

	public function isCheckInNotAllowed() {
		return $this->isCheckInNotAllowed;
	}

	public function isCheckOutNotAllowed() {
		return $this->isCheckOutNotAllowed;
	}

	public function isEarlierThanMinAdvanceDate() {
		return $this->isEarlierThanMinAdvanceDate;
	}

	public function isLaterThanMaxAdvanceDate() {
		return $this->isLaterThanMaxAdvanceDate;
	}

	public function getMinStayNights() {
		return $this->minStayNights;
	}

	public function getMaxStayNights() {
		return $this->maxStayNights;
	}

	public function toArray() {

		$result = array(
			'roomTypeStatus' => $this->roomTypeStatus,
			'availableRoomsCount' => $this->availableRoomsCount,
			'isCheckInDate' => $this->isCheckInDate,
			'isCheckOutDate' => $this->isCheckOutDate,
			'isStayInNotAllowed' => $this->isStayInNotAllowed,
			'isCheckInNotAllowed' => $this->isCheckInNotAllowed,
			'isCheckOutNotAllowed' => $this->isCheckOutNotAllowed,
			'isEarlierThanMinAdvanceDate' => $this->isEarlierThanMinAdvanceDate,
			'isLaterThanMaxAdvanceDate' => $this->isLaterThanMaxAdvanceDate,
			'minStayNights' => $this->minStayNights
		);

		if ( null !== $this->maxStayNights ) {

			$result['maxStayNights'] = $this->maxStayNights;
		}

		return $result;
	}
}
