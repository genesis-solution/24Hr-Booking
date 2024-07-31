<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\Room;

class AccommodationData extends AbstractPostData {

	/**
	 * @var Room
	 */
	public $entity;

	/**
	 * @return \MPHB\Repositories\RoomRepository
	 */
	public static function getRepository() {
		return MPHB()->getRoomRepository();
	}

	public static function getProperties() {
		return array(
			'id'                    => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'                => array(
				'description' => 'Accommodation status.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'accommodation_type_id' => array(
				'description' => 'Unique identifier for the accommodation type resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'                 => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'excerpt'               => array(
				'description' => 'Excerpt.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
		);
	}

	/**
	 * @return int
	 */
	protected function getAccommodationTypeId() {
		return intval( $this->entity->getRoomTypeId() );
	}

	protected function setAccommodationTypeId( $id ) {
		$accommodationTypePost = MPHB()->getRoomTypePersistence()->getPost( $id );

		if ( is_null( $accommodationTypePost ) || $accommodationTypePost->post_status != 'publish' ) {
			throw new \Exception( sprintf( 'Invalid %s: %d.', 'accommodation_type_id', $id ) );
		}

		$this->accommodation_type_id = $id;
	}

	protected function getExcerpt() {
		return $this->entity->getDescription();
	}

	protected function setExcerpt( $excerpt ) {
		$this->entity->setDescription( $excerpt );
	}

	private function setDataToEntity() {
		$atts = array(
			'id'           => $this->id,
			'status'       => $this->status ?: 'publish',
			'room_type_id' => $this->accommodation_type_id,
		);

		$fields = static::getWritableFieldKeys();
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'excerpt':
					$atts['description'] = $this->getExcerpt();
					break;
				default:
					$atts[ $field ] = $this->{$field};
					if ( isset( $this->{$field} ) ) {
						unset( $this->{$field} );
					}
			}
		}
		$this->entity = new Room( $atts );
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function save() {
		$this->setDataToEntity();
		if ( $this->isDataChanged() ) {
			parent::save();
		}

		return true;
	}
}
