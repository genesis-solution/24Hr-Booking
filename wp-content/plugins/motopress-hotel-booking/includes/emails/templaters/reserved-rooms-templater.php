<?php

namespace MPHB\Emails\Templaters;

class ReservedRoomsTemplater extends AbstractTemplater {

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	protected $booking;

	/**
	 *
	 * @var int
	 */
	private $reservedRoomNumber;

	/**
	 *
	 * @var \MPHB\Entities\ReservedRoom
	 */
	private $reservedRoom;

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function process( $booking ) {
		$content = '';

		$this->booking = $booking;
		$reservedRooms = $booking->getReservedRooms();

		if ( ! empty( $reservedRooms ) ) {
			$reservedRoomTemplate = MPHB()->settings()->emails()->getReservedRoomDetailsTemplate();
			$reservedRoomTagsStr  = $this->_generateTagsFindString( $this->tags );
			foreach ( $reservedRooms as $key => $reservedRoom ) {
				$this->reservedRoom       = $reservedRoom;
				$this->reservedRoomNumber = $key + 1;

				if ( ! empty( $this->tags ) ) {
					$content .= preg_replace_callback( $reservedRoomTagsStr, array( $this, 'replaceTag' ), $reservedRoomTemplate );
				}

				$this->reservedRoom       = null;
				$this->reservedRoomNumber = null;
			}
		}

		return $content;
	}

	/**
	 * @param array  $match
	 * @param string $match[0] The tag.
	 * @return string
	 *
	 * @since 3.6.1 added new macros - %guest_name%.
	 */
	public function replaceTag( $match ) {
		$tag = str_replace( '%', '', $match[0] );

		$replaceText = '';

		switch ( $tag ) {
			case 'adults':
				if ( isset( $this->reservedRoom ) ) {
					$replaceText = $this->reservedRoom->getAdults();
				}
				break;
			case 'children':
				if ( isset( $this->reservedRoom ) ) {
					$replaceText = $this->reservedRoom->getChildren();
				}
				break;
			case 'services':
				if ( isset( $this->reservedRoom ) ) {
					ob_start();
					\MPHB\Views\ReservedRoomView::renderServicesList( $this->reservedRoom );
					$replaceText = ob_get_clean();
				}
				break;
			case 'room_type_id':
				if ( isset( $this->reservedRoom ) ) {
					$roomType    = MPHB()->getRoomTypeRepository()->findById( $this->reservedRoom->getRoomTypeId() );
					$replaceText = ( $roomType ) ? $roomType->getId() : '';
				}
				break;
			case 'room_type_link':
				if ( isset( $this->reservedRoom ) ) {
					$roomType    = MPHB()->getRoomTypeRepository()->findById( $this->reservedRoom->getRoomTypeId() );
					$roomType    = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getLink() : '';
				}
				break;
			case 'room_type_title':
				if ( isset( $this->reservedRoom ) ) {
					$roomType    = MPHB()->getRoomTypeRepository()->findById( $this->reservedRoom->getRoomTypeId() );
					$roomType    = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getTitle() : '';
				}
				break;
			case 'room_title':
				if ( isset( $this->reservedRoom ) ) {
					$room = MPHB()->getRoomRepository()->findById( $this->reservedRoom->getRoomId() );
					$replaceText = ( $room ) ? $room->getTitle() : '';
				}
				break;
			case 'room_type_categories':
				if ( isset( $this->reservedRoom ) ) {
					$roomType = MPHB()->getRoomTypeRepository()->findById( $this->reservedRoom->getRoomTypeId() );
					$roomType = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					if ( $roomType ) {
						$categories    = $roomType->getCategories();
						$categoryNames = wp_list_pluck( $categories, 'name' );
						$replaceText   = implode( ', ', $categoryNames );
					}
				}
				break;
			case 'room_type_bed_type':
				if ( isset( $this->reservedRoom ) ) {
					$roomType    = MPHB()->getRoomTypeRepository()->findById( $this->reservedRoom->getRoomTypeId() );
					$roomType    = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getBedType() : '';
				}
				break;
			case 'room_rate_title':
				if ( isset( $this->reservedRoom ) ) {
					$roomRate    = MPHB()->getRateRepository()->findById( $this->reservedRoom->getRateId() );
					$roomRate    = $roomRate ? apply_filters( '_mphb_translate_rate', $roomRate, $this->booking->getLanguage() ) : $roomRate;
					$replaceText = $roomRate ? $roomRate->getTitle() : '';
				}
				break;
			case 'room_rate_description':
				if ( isset( $this->reservedRoom ) ) {
					$roomRate    = MPHB()->getRateRepository()->findById( $this->reservedRoom->getRateId() );
					$roomRate    = $roomRate ? apply_filters( '_mphb_translate_rate', $roomRate, $this->booking->getLanguage() ) : $roomRate;
					$replaceText = $roomRate ? $roomRate->getDescription() : '';
				}
				break;
			case 'room_key':
				if ( isset( $this->reservedRoomNumber ) ) {
					$replaceText = $this->reservedRoomNumber;
				}
				break;
			case 'guest_name':
				if ( isset( $this->reservedRoom ) ) {
					$replaceText = $this->reservedRoom->getGuestName();
				}
				break;
		}

		return $replaceText;
	}

	public function setupTags() {
		$tags = array(
			array(
				'name'        => 'adults',
				'description' => __( 'Adults', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'children',
				'description' => __( 'Children', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'services',
				'description' => __( 'Services', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_type_id',
				'description' => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_type_link',
				'description' => __( 'Accommodation Type Link', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_type_title',
				'description' => __( 'Accommodation Type Title', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_title',
				'description' => __( 'Accommodation Title', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_type_categories',
				'description' => __( 'Accommodation Type Categories', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_type_bed_type',
				'description' => __( 'Accommodation Type Bed', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_rate_title',
				'description' => __( 'Accommodation Rate Title', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_rate_description',
				'description' => __( 'Accommodation Rate Description', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'room_key',
				'description' => __( 'Sequential Number of Accommodation', 'motopress-hotel-booking' ),
			),
			array(
				'name'        => 'guest_name',
				'description' => __( 'Full Guest Name', 'motopress-hotel-booking' ),
			),
		);

		foreach ( $tags as $tagDetails ) {
			$this->addTag( $tagDetails['name'], $tagDetails['description'], $tagDetails );
		}
	}

}
