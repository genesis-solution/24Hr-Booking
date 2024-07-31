<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class CouponRepository extends AbstractPostRepository {

	const TYPE_PERCENT            = '';
	const TYPE_PER_ACCOMM         = 'per_accomm';
	const TYPE_PER_ACCOMM_PER_DAY = 'per_accomm_per_day';

	/**
	 * @param Entities\AbstractCoupon $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'          => $entity->getId(),
			'post_metas'  => array(),
			'post_title'  => $entity->getCode(),
			'post_status' => $entity->getStatus(),
			'post_type'   => MPHB()->postTypes()->coupon()->getPostType(),
		);

		$postAtts['post_metas'] = array(
			'_mphb_description'              => $entity->getDescription(),
			'_mphb_amount'                   => $entity->getAmount(),
			'_mphb_expiration_date'          => $entity->getExpirationDate() ? $entity->getExpirationDate()->format( 'Y-m-d' ) : '',
			'_mphb_include_room_types'       => $entity->getRoomTypes(),
			'_mphb_check_in_date_after'      => $entity->getCheckInDateAfter() ? $entity->getCheckInDateAfter()->format( 'Y-m-d' ) : '',
			'_mphb_check_out_date_before'    => $entity->getCheckOutDateBefore() ? $entity->getCheckOutDateBefore()->format( 'Y-m-d' ) : '',
			'_mphb_min_days_before_check_in' => $entity->getMinDaysBeforeCheckIn(),
			'_mphb_max_days_before_check_in' => $entity->getMaxDaysBeforeCheckIn(),
			'_mphb_min_nights'               => $entity->getMinNights(),
			'_mphb_max_nights'               => $entity->getMaxNights(),
			'_mphb_usage_limit'              => $entity->getUsageLimit(),
			'_mphb_usage_count'              => $entity->getUsageCount(),
			'_mphb_type'                     => $this->detectType( $entity ),
		);

		return new Entities\WPPostData( $postAtts );
	}

	/**
	 * @param \WP_Post|int $post
	 * @return \MPHB\Entities\AbstractCoupon
	 */
	public function mapPostToEntity( $post ) {

		if ( is_a( $post, '\WP_Post' ) ) {
			$id = $post->ID;
		} else {
			$id   = absint( $post );
			$post = get_post( $id );
		}

		$description = get_post_meta( $id, '_mphb_description', true );

		$type   = get_post_meta( $id, '_mphb_type', true );
		$amount = max( (float) get_post_meta( $id, '_mphb_amount', true ), 0.0 );

		$roomTypes = get_post_meta( $id, '_mphb_include_room_types', true );
		if ( $roomTypes == '' ) {
			$roomTypes = array();
		}

		$minDaysBeforeCheckIn = (int) get_post_meta( $id, '_mphb_min_days_before_check_in', true );
		$maxDaysBeforeCheckIn = (int) get_post_meta( $id, '_mphb_max_days_before_check_in', true );

		$minNights  = (int) get_post_meta( $id, '_mphb_min_nights', true );
		$maxNights  = (int) get_post_meta( $id, '_mphb_max_nights', true );
		$usageLimit = (int) get_post_meta( $id, '_mphb_usage_limit', true );
		$usageCount = (int) get_post_meta( $id, '_mphb_usage_count', true );

		$atts = array(
			'id'                       => $id,
			'status'                   => $post->post_status,
			'code'                     => $post->post_title,
			'description'              => $description,
			'amount'                   => $amount,
			'room_types'               => $roomTypes,
			'min_days_before_check_in' => $minDaysBeforeCheckIn,
			'max_days_before_check_in' => $maxDaysBeforeCheckIn,
			'min_nights'               => $minNights,
			'max_nights'               => $maxNights,
			'usage_limit'              => $usageLimit,
			'usage_count'              => $usageCount,
		);

		$expirationDate = \DateTime::createFromFormat( 'Y-m-d', get_post_meta( $id, '_mphb_expiration_date', true ) );
		if ( $expirationDate ) {
			$atts['expiration_date'] = $expirationDate;
		}

		$checkInDateAfter = \DateTime::createFromFormat( 'Y-m-d', get_post_meta( $id, '_mphb_check_in_date_after', true ) );
		if ( $checkInDateAfter ) {
			$atts['check_in_date_after'] = $checkInDateAfter;
		}

		$checkOutDateBefore = \DateTime::createFromFormat( 'Y-m-d', get_post_meta( $id, '_mphb_check_out_date_before', true ) );
		if ( $checkOutDateBefore ) {
			$atts['check_out_date_before'] = $checkOutDateBefore;
		}

		return $this->initTypedCoupon( $atts, $type );
	}

	/**
	 * @param array  $atts
	 * @param string $type
	 *
	 * @return Entities\AbstractCoupon
	 */
	private function initTypedCoupon( $atts, $type ) {
		$coupon = null;
		switch ( $type ) {
			case self::TYPE_PER_ACCOMM:
				$coupon = new Entities\FixedAccommodationCoupon( $atts );
				break;
			case self::TYPE_PER_ACCOMM_PER_DAY:
				$coupon = new Entities\FixedAccommodationPerDayCoupon( $atts );
				break;
			case self::TYPE_PERCENT:
			default:
				$coupon = new Entities\PercentCoupon( $atts );
				break;
		}

		return $coupon;
	}

	/**
	 * @param string $code
	 * @return \MPHB\Entities\AbstractCoupon|null
	 */
	public function findByCode( $code ) {

		$atts = array(
			'title'          => $code,
			'posts_per_page' => 1,
			'status'         => 'publish',
		);

		$coupons = $this->findAll( $atts );

		return ! empty( $coupons ) ? reset( $coupons ) : null;
	}

	/**
	 * @param int  $id
	 * @param bool $force
	 * @return Entities\AbstractCoupon
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	/**
	 * @param \MPHB\Entities\AbstractCoupon $entity
	 * @return string
	 */
	private function detectType( $entity ) {
		$type = '';

		$entityClass = get_class( $entity );
		$entityClass = $entityClass ? ltrim( $entityClass, '\\' ) : '';

		switch ( $entityClass ) {
			case 'MPHB\Entities\FixedAccommodationCoupon':
				$type = self::TYPE_PER_ACCOMM;
				break;
			case 'MPHB\Entities\FixedAccommodationPerDayCoupon':
				$type = self::TYPE_PER_ACCOMM_PER_DAY;
				break;
			case 'MPHB\Entities\PercentCoupon':
			default:
				$type = self::TYPE_PERCENT;
				break;
		}

		return $type;
	}
}
