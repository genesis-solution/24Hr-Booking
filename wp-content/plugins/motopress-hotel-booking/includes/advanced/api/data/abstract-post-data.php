<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Advanced\Api\ApiHelper;

abstract class AbstractPostData extends AbstractData {

	/**
	 * @return \MPHB\Repositories\AbstractPostRepository
	 */
	abstract public static function getRepository();

	/**
	 * @param  int $id
	 *
	 * @return static|null
	 */
	public static function findById( int $id ) {
		$entity = static::getRepository()->findById( $id );
		if ( is_null( $entity ) ) {
			return null;
		}
		return new static( $entity );
	}


	/**
	 * @return bool
	 */
	protected function isDataChanged() {
		if ( $this->entity == $this->_entity_init_state ) {
			return false;
		}
		return true;
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function save() {
		if ( ! $this->isDataChanged() ) {
			return true;
		}

		if ( static::getRepository()->save( $this->entity ) ) {
			return true;
		}

		throw new \Exception( 'The entity has not been saved.' );
	}

	protected function getDateCreated() {
		return get_post_time( ApiHelper::DATETIME_FORMAT_ISO8601, false, $this->entity->getId() );
	}

	protected function getDateCreatedUtc() {
		return get_post_time( ApiHelper::DATETIME_FORMAT_ISO8601, true, $this->entity->getId() );
	}

	protected function getDateModified() {
		return get_post_modified_time( ApiHelper::DATETIME_FORMAT_ISO8601, false, $this->entity->getId() );
	}

	protected function getDateModifiedUtc() {
		return get_post_modified_time( ApiHelper::DATETIME_FORMAT_ISO8601, true, $this->entity->getId() );
	}
}
