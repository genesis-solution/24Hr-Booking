<?php

namespace MPHB\Addons\Invoice\Entities;

class BookingEntity
{
    /** @var string */
    public $id = '0';

    /** @var int */
    public $bookingId = 0;

    /** @var \MPHB\Entities\Booking */
    public $booking = null;

    /**
     * @param int $bookingId
     * @param \MPHB\Entities\Booking $booking Optional.
     */
    public function __construct($bookingId, $booking = null)
    {
        $this->id = (string)$bookingId;
        $this->bookingId = $bookingId;
        $this->booking = $booking;
    }

    public function getBooking()
    {
        if (is_null($this->booking)) {
            $this->booking = mphb()->getBookingRepository()->findById($this->bookingId);
        }

        return $this->booking;
    }


    public function getDetailsUrl()
    {
        return (string)mphb()->userActions()->getBookingViewAction()->generateLink(['booking' => $this->getBooking()]);
    }
}
