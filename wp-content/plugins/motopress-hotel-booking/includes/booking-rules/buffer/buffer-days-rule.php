<?php

namespace MPHB\BookingRules\Buffer;

use MPHB\BookingRules\AbstractRule;

/**
 * @since 3.9
 */
class BufferDaysRule extends AbstractRule {

	/**
	 * @var int
	 *
	 * @since 3.9
	 */
	protected $bufferDays = 0;

	/**
	 * @param array $atts
	 *     @param array $atts['season_ids']
	 *     @param array $atts['room_type_ids']
	 *     @param int   $atts['buffer_day']
	 *
	 * @since 3.9
	 * @since 3.9.9 - don't use buffer days if rules for admin are disabled.
	 */
	public function __construct( $atts ) {
		parent::__construct( $atts );

		if ( ! MPHB()->settings()->main()->isBookingRulesForAdminDisabled() ) {
			$this->bufferDays = intval( $atts['buffer_days'] );
		}
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId Optional.
	 * @return bool
	 *
	 * @since 3.9
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		return true;
	}

	/**
	 * @return int
	 *
	 * @since 3.9
	 */
	public function getBufferDays() {
		return $this->bufferDays;
	}

	/**
	 * @return array
	 *
	 * @since 3.9
	 */
	public function toArray() {
		return parent::toArray() + array(
			'buffer_days' => $this->bufferDays,
		);
	}
}
