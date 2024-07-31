<?php

namespace MPHB\BookingRules;

use DateTime;

interface RuleVerifyInterface {

	/**
	 * @param DateTime $checkInDate
	 * @param DateTime $checkOutDate
	 * @param int      $roomTypeId Optional.
	 * @return bool
	 *
	 * @since 3.9 renamed from RuleVerifiable.
	 */
	public function verify( DateTime $checkInDate, DateTime $checkOutDate, $roomTypeId = 0);
}
