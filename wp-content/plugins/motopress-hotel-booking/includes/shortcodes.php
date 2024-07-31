<?php

namespace MPHB;

class Shortcodes {

	/**
	 *
	 * @var \MPHB\Shortcodes\SearchShortcode
	 */
	private $search;
	/**
	 *
	 * @var \MPHB\Shortcodes\SearchResultsShortcode
	 */
	private $searchResults;
	/**
	 *
	 * @var \MPHB\Shortcodes\CheckoutShortcode
	 */
	private $checkout;
	/**
	 *
	 * @var \MPHB\Shortcodes\RoomsShortcode
	 */
	private $rooms;
	/**
	 *
	 * @var \MPHB\Shortcodes\RoomShortcode
	 */
	private $room;
	/**
	 *
	 * @var \MPHB\Shortcodes\ServicesShortcode
	 */
	private $services;
	/**
	 *
	 * @var \MPHB\Shortcodes\BookingFormShortcode
	 */
	private $bookingForm;
	/**
	 *
	 * @var \MPHB\Shortcodes\RoomRatesShortcode
	 */
	private $roomRates;
	/**
	 *
	 * @var \MPHB\Shortcodes\BookingConfirmationShortcode
	 */
	private $bookingConfirmation;
	/**
	 *
	 * @var \MPHB\Shortcodes\AvailabilityCalendarShortcode
	 */
	private $availabilityCalendar;

	/**
	 *
	 * @var \MPHB\Shortcodes\BookingCancellationShortcode
	 */
	private $bookingCancellation;

	/**
	 *
	 * @var \MPHB\Shortcodes\CustomerAccountShortcode
	 */
	private $account;

	private $shortcodes = array();

	public function __construct() {
		$this->search               = new Shortcodes\SearchShortcode();
		$this->searchResults        = new Shortcodes\SearchResultsShortcode();
		$this->checkout             = new Shortcodes\CheckoutShortcode();
		$this->rooms                = new Shortcodes\RoomsShortcode();
		$this->room                 = new Shortcodes\RoomShortcode();
		$this->services             = new Shortcodes\ServicesShortcode();
		$this->bookingForm          = new Shortcodes\BookingFormShortcode();
		$this->roomRates            = new Shortcodes\RoomRatesShortcode();
		$this->bookingConfirmation  = new Shortcodes\BookingConfirmationShortcode();
		$this->availabilityCalendar = new Shortcodes\AvailabilityCalendarShortcode();
		$this->bookingCancellation  = new Shortcodes\BookingCancellationShortcode();
		$this->account              = new Shortcodes\AccountShortcode();

		foreach ( $this as $shortcode ) {
			if ( ! is_a( $shortcode, 'MPHB\Shortcodes\AbstractShortcode' ) ) {
				continue;
			}

			$this->shortcodes[ $shortcode->getName() ] = $shortcode;
		}
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\SearchShortcode
	 */
	public function getSearch() {
		return $this->search;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\CheckoutShortcode
	 */
	public function getCheckout() {
		return $this->checkout;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\ServicesShortcode
	 */
	public function getServices() {
		return $this->services;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\BookingFormShortcode
	 */
	public function getBookingForm() {
		return $this->bookingForm;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\RoomRatesShortcode
	 */
	public function getRoomRates() {
		return $this->roomRates;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\SearchResultsShortcode
	 */
	public function getSearchResults() {
		return $this->searchResults;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\RoomsShortcode
	 */
	public function getRooms() {
		return $this->rooms;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\RoomShortcode
	 */
	public function getRoom() {
		return $this->room;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\BookingConfirmationShortcode
	 */
	public function getBookingConfirmation() {
		return $this->bookingConfirmation;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes\AvailabilityCalendarShortcode
	 */
	public function getAvailabilityCalendar() {
		return $this->availabilityCalendar;
	}

	public function getBookingCancellation() {
		return $this->bookingCancellation;
	}

	public function getAccount() {
		return $this->account;
	}

	public function getShortcodeByName( $name ) {
		if ( isset( $this->shortcodes[ $name ] ) ) {
			return $this->shortcodes[ $name ];
		} else {
			return null;
		}
	}

}
