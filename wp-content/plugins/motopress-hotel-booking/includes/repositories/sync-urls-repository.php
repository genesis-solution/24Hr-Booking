<?php

namespace MPHB\Repositories;

class SyncUrlsRepository {

	protected $tableName = 'mphb_sync_urls';

	public function __construct() {
		global $wpdb;
		$this->tableName = $wpdb->prefix . $this->tableName;
	}

	protected function prepareUrls( $urls ) {
		$urls2 = array();

		foreach ( $urls as $url ) {
			$syncId           = md5( $url );
			$urls2[ $syncId ] = $url;
		}

		return $urls2;
	}

	public function insertUrls( $roomId, $urls ) {
		global $wpdb;

		if ( empty( $urls ) ) {
			return;
		}

		$urls   = $this->prepareUrls( $urls );
		$values = array();

		foreach ( $urls as $syncId => $url ) {
			$values[] = $wpdb->prepare( '(%d, %s, %s)', $roomId, $syncId, $url );
		}

		$sql = "INSERT INTO {$this->tableName} (room_id, sync_id, calendar_url)"
			. ' VALUES ' . implode( ', ', $values );

		$wpdb->query( $sql );
	}

	/**
	 * @since 4.2.2
	 *
	 * @return int[]
	 *
	 * @global \wpdb $wpdb
	 */
	public function getAllRoomIds() {
		global $wpdb;

		$roomIds = $wpdb->get_col( "SELECT DISTINCT room_id FROM {$this->tableName}" );
		$roomIds = array_map( 'absint', $roomIds );

		return $roomIds;
	}

	public function getUrls( $roomId ) {
		global $wpdb;

		$sql  = $wpdb->prepare( "SELECT sync_id, calendar_url FROM {$this->tableName} WHERE room_id = %d", $roomId );
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $rows ) ) {
			return array();
		}

		$urls = array();
		foreach ( $rows as $row ) {
			$urls[ $row['sync_id'] ] = $row['calendar_url'];
		}

		return $urls;
	}

	/**
	 * @param int $roomId
	 * @return array [%syncId% => [%roomIds%, %calendarUrl%]]
	 *
	 * @global \wpdb $wpdb
	 */
	public function getDuplicatingUrls( $roomId ) {
		global $wpdb;

		$sql  = $wpdb->prepare(
			'SELECT urls2.room_id, urls.sync_id, urls.calendar_url'
			. " FROM {$this->tableName} AS urls"
			. " INNER JOIN {$this->tableName} AS urls2 ON urls2.sync_id = urls.sync_id AND urls2.room_id != %d"
			. ' WHERE urls.room_id = %d',
			$roomId,
			$roomId
		);
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $rows ) ) {
			return array();
		}

		$urls = array();
		foreach ( $rows as $row ) {
			$syncId = $row['sync_id'];

			if ( ! isset( $urls[ $syncId ] ) ) {
				$urls[ $syncId ] = array(
					'roomIds'     => array(),
					'calendarUrl' => $row['calendar_url'],
				);
			}

			$urls[ $syncId ]['roomIds'][] = $row['room_id'];
		}

		return $urls;
	}

	public function updateUrls( $roomId, $urls ) {
		if ( empty( $urls ) ) {
			$this->removeUrls( $roomId );
		} else {
			$newUrls      = $this->prepareUrls( $urls );
			$existingUrls = $this->getUrls( $roomId );
			$toInsert     = array_diff_key( $newUrls, $existingUrls );
			$toRemove     = array_diff_key( $existingUrls, $newUrls );

			if ( ! empty( $toInsert ) ) {
				$this->insertUrls( $roomId, $toInsert );
			}

			if ( ! empty( $toRemove ) ) {
				$this->removeUrls( $roomId, array_keys( $toRemove ) );
			}
		}
	}

	/**
	 * @param int                  $roomId
	 * @param null|string|string[] $syncId
	 *
	 * @global \wpdb $wpdb
	 */
	public function removeUrls( $roomId, $syncId = null ) {
		global $wpdb;

		if ( is_null( $syncId ) ) {
			$sql = $wpdb->prepare( "DELETE FROM {$this->tableName} WHERE room_id = %d", $roomId );
		} else {
			if ( is_array( $syncId ) ) {
				$syncIds = esc_sql( $syncId );
				$syncIds = "'" . implode( "', '", $syncIds ) . "'";
				$sql     = $wpdb->prepare( "DELETE FROM {$this->tableName} WHERE room_id = %d AND sync_id IN ({$syncIds})", $roomId );
			} else {
				$sql = $wpdb->prepare( "DELETE FROM {$this->tableName} WHERE room_id = %d AND sync_id = %s", $roomId, $syncId );
			}
		}

		$wpdb->query( $sql );
	}
}
