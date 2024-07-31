<?php

namespace MPHB;

class Importer {

	private $processedPosts = array();
	private $importedPosts  = array();
	private $importProcess  = false;

	public function __construct() {
		add_action( 'import_end', array( $this, 'backfillPostMeta' ), 10 );
		add_action( 'import_start', array( $this, 'startImportProcess' ) );
		add_action( 'import_end', array( $this, 'endImportProcess' ), 20 );
		add_filter( 'mphb_prevent_handle_booking_status_transition', array( $this, 'isPreventHandleStatusTransition' ), 10, 1 );
		add_filter( 'mphb_prevent_handle_payment_status_transition', array( $this, 'isPreventHandleStatusTransition' ), 10, 1 );
	}

	public function startImportProcess() {
		$this->importProcess = true;
	}

	public function endImportProcess() {
		$this->importProcess = false;
		do_action( 'mphb_import_end' );
	}

	/**
	 *
	 * @param bool $isPrevent
	 * @return bool
	 */
	public function isPreventHandleStatusTransition( $isPrevent ) {
		return $isPrevent ? $isPrevent : $this->importProcess;
	}

	/**
	 *
	 * @global WP_Import $wp_import
	 */
	public function backfillPostMeta() {
		global $wp_import;

		if ( is_a( $wp_import, '\WP_Import' ) ) {

			$this->processedPosts = $wp_import->processed_posts;
			$this->importedPosts  = array_keys( $wp_import->processed_posts );

			$this->backfillBookings();
			$this->backfillPayments();
			$this->backfillRooms();
			$this->backfillRoomTypes();
			$this->backfillRates();
			$this->backfillReservedRooms();
			$this->backfillCoupons();
		}
	}

	public function backfillRoomTypes() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->roomType()->getPostType() ) {

				// Fix ids gallery images ids
				$gallery = get_post_meta( $postId, 'mphb_gallery', true );
				if ( ! empty( $gallery ) ) {
					$gallery      = explode( ',', $gallery );
					$fixedGallery = array();
					foreach ( $gallery as $imageId ) {
						if ( isset( $this->processedPosts[ $imageId ] ) ) {
							$fixedGallery[] = $this->processedPosts[ $imageId ];
						}
					}
					update_post_meta( $postId, 'mphb_gallery', implode( ',', $fixedGallery ) );
				}

				// Fix services ids
				$services = get_post_meta( $postId, 'mphb_services', true );
				if ( ! empty( $services ) ) {
					$fixedServices = array();
					foreach ( $services as $serviceId ) {
						if ( isset( $this->processedPosts[ $serviceId ] ) ) {
							$fixedServices[] = $this->processedPosts[ $serviceId ];
						}
					}
					update_post_meta( $postId, 'mphb_services', $fixedServices );
				}
			}
		}
	}

	public function backfillRates() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->rate()->getPostType() ) {

				$roomType = get_post_meta( $postId, 'mphb_room_type_id', true );
				if ( ! empty( $roomType ) && isset( $this->processedPosts[ $roomType ] ) ) {
					update_post_meta( $postId, 'mphb_room_type_id', $this->processedPosts[ $roomType ] );
				}

				// Fix season ids
				$seasonPrices = get_post_meta( $postId, 'mphb_season_prices', true );

				if ( ! empty( $seasonPrices ) ) {

					$fixedSeasonPrices = array();

					foreach ( $seasonPrices as $seasonPrice ) {

						if ( ! empty( $seasonPrice['season'] ) && isset( $this->processedPosts[ $seasonPrice['season'] ] ) ) {
							$seasonPrice['season'] = $this->processedPosts[ $seasonPrice['season'] ];
							$fixedSeasonPrices[]   = $seasonPrice;
						}
					}

					update_post_meta( $postId, 'mphb_season_prices', $fixedSeasonPrices );
				}
			}
		}
	}

	public function backfillRooms() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->room()->getPostType() ) {

				// Fix Room Type Id
				$roomTypeId = get_post_meta( $postId, 'mphb_room_type_id', true );
				if ( ! empty( $roomTypeId ) && isset( $this->processedPosts[ $roomTypeId ] ) ) {
					$fixedRoomTypeId = isset( $this->processedPosts[ $roomTypeId ] ) ? $this->processedPosts[ $roomTypeId ] : '';
					update_post_meta( $postId, 'mphb_room_type_id', $fixedRoomTypeId );
				}
			}
		}
	}

	public function backfillBookings() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->booking()->getPostType() ) {

				// Fix "wait payment" id
				$paymentId = get_post_meta( $postId, '_mphb_wait_payment', true );
				if ( ! empty( $paymentId ) && isset( $this->processedPosts[ $paymentId ] ) ) {
					update_post_meta( $postId, '_mphb_wait_payment', $this->processedPosts[ $paymentId ] );
				}

				/* Deprecated meta ( since 2.0.0 ) */

				// Fix Room Id
				$roomId = get_post_meta( $postId, 'mphb_room_id', true );
				if ( ! empty( $roomId ) && isset( $this->processedPosts[ $roomId ] ) ) {
					update_post_meta( $postId, 'mphb_room_id', $this->processedPosts[ $roomId ] );
				}

				// Fix Rate Id
				$rateId = get_post_meta( $postId, 'mphb_room_rate_id', true );
				if ( ! empty( $rateId ) && isset( $this->processedPosts[ $rateId ] ) ) {
					update_post_meta( $postId, 'mphb_room_rate_id', $this->processedPosts[ $rateId ] );
				}

				// Fix Services Ids
				$services = get_post_meta( $postId, 'mphb_services', true );
				if ( ! empty( $services ) ) {
					foreach ( $services as &$serviceDetails ) {
						if ( isset( $serviceDetails['id'] ) && isset( $this->processedPosts[ $serviceDetails['id'] ] ) ) {
							$serviceDetails['id'] = $this->processedPosts[ $serviceDetails['id'] ];
						}
					}
					update_post_meta( $postId, 'mphb_services', $services );
				}

				// Fix Coupon Id
				$counponId = get_post_meta( $postId, 'mphb_coupon_id', true );
				if ( ! empty( $rateId ) && isset( $this->processedPosts[ $rateId ] ) ) {
					update_post_meta( $postId, 'mphb_coupon_id', $this->processedPosts[ $rateId ] );
				}
			}
		}
	}

	public function backfillPayments() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->payment()->getPostType() ) {

				// Fix Booking Id
				$bookingId = get_post_meta( $postId, '_mphb_booking_id', true );
				if ( ! empty( $bookingId ) && isset( $this->processedPosts[ $bookingId ] ) ) {
					update_post_meta( $bookingId, '_mphb_booking_id', $this->processedPosts[ $bookingId ] );
				}
			}
		}
	}

	public function backfillReservedRooms() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->reservedRoom()->getPostType() ) {

				// Fix Booking Id
				$bookingId = get_post_meta( $postId, '_mphb_booking_id', true );
				if ( ! empty( $bookingId ) && isset( $this->processedPosts[ $bookingId ] ) ) {
					update_post_meta( $bookingId, '_mphb_booking_id', $this->processedPosts[ $bookingId ] );
				}

				// Fix Room Id
				$roomId = get_post_meta( $postId, '_mphb_room_id', true );
				if ( ! empty( $roomId ) && isset( $this->processedPosts[ $roomId ] ) ) {
					update_post_meta( $postId, 'mphb_room_id', $this->processedPosts[ $roomId ] );
				}

				// Fix Rate Id
				$rateId = get_post_meta( $postId, '_mphb_rate_id', true );
				if ( ! empty( $rateId ) && isset( $this->processedPosts[ $rateId ] ) ) {
					update_post_meta( $postId, '_mphb_rate_id', $this->processedPosts[ $rateId ] );
				}

				// Fix Services Ids
				$services = get_post_meta( $postId, '_mphb_services', true );
				if ( ! empty( $services ) ) {
					foreach ( $services as &$serviceDetails ) {
						if ( isset( $serviceDetails['id'] ) && isset( $this->processedPosts[ $serviceDetails['id'] ] ) ) {
							$serviceDetails['id'] = $this->processedPosts[ $serviceDetails['id'] ];
						}
					}
					update_post_meta( $postId, 'mphb_services', $services );
				}
			}
		}
	}

	public function backfillCoupons() {
		foreach ( $this->importedPosts as $postId ) {
			if ( get_post_type( $postId ) === MPHB()->postTypes()->coupon()->getPostType() ) {

				// Fix Room Types
				$roomTypeIds = get_post_meta( $postId, '_mphb_include_room_types', true );
				if ( ! empty( $roomTypeIds ) && is_array( $roomTypeIds ) ) {
					foreach ( $roomTypeIds as &$roomTypeId ) {
						$roomTypeId = isset( $this->processedPosts[ $roomTypeId ] ) ? $this->processedPosts[ $roomTypeId ] : null;
					}
					$roomTypeIds = array_filter( $roomTypeIds );
					update_post_meta( $postId, '_mphb_include_room_types', $roomTypeIds );
				}
			}
		}
	}

}
