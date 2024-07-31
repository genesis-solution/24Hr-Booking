<?php

namespace MPHB\CSV\Bookings;

use MPHB\CSV\CSVFile;

/**
 * @since 3.5.0
 */
class BookingsExporter extends \MPHB\BackgroundProcess {

	protected $action = 'bookings_csv';

	protected $abortOption;
	protected $fileOption;
	protected $columnsOption;
	protected $filterRoomOption;

	public function __construct() {

		parent::__construct();

		$this->abortOption      = $this->identifier . '_aborting';
		$this->fileOption       = $this->identifier . '_file';
		$this->columnsOption    = $this->identifier . '_columns';
		$this->filterRoomOption = $this->identifier . '_filter_id';
	}

	/**
	 * @return string "/path/to/wordpress/wp-content/uploads/mphb/filename.csv"
	 */
	public function pathToFile() {
		return mphb_uploads_dir() . $this->getFile();
	}

	/**
	 * @return string|false "http://example.com/wp-content/uploads/mphb/filename.csv"
	 *     or false if the file was not created.
	 */
	public function getDownloadLink() {
		$file = $this->getFile();

		return add_query_arg(
			array(
				'mphb_action' => 'download',
				'mphb_nonce'  => wp_create_nonce( 'mphb_download-' . $file ),
				'filename'    => $file,
			),
			admin_url()
		);
	}

	/**
	 * @param array  $args
	 * @param array  $args['room'] Room type ID.
	 * @param array  $args['columns']
	 * @param string $args['start_date'] Start date in format "Y-m-d" or empty string "".
	 * @param string $args['end_date'] End date in format "Y-m-d" or empty string "".
	 * @return self
	 */
	public function setupOutput( $args ) {
		// Remove old file
		$this->removeFile();

		// Generate new file
		// mphb-bookings-20190523-124250
		$newFilename = 'mphb-bookings-' . date( 'Ymd-His' );

		if ( ! empty( $args['start_date'] ) ) {
			// mphb-bookings-20190523-124250_20190601
			$newFilename .= '_' . str_replace( '-', '', $args['start_date'] );
		}

		if ( ! empty( $args['end_date'] ) ) {
			// mphb-bookings-20190523-124250_20190601-
			$newFilename .= ! empty( $args['start_date'] ) ? '-' : '_';
			// mphb-bookings-20190523-124250_20190601-20190630
			$newFilename .= str_replace( '-', '', $args['end_date'] );
		}

		// mphb-bookings-20190523-124250_20190601-20190630.csv
		$newFilename .= '.csv';

		// Save new values and arguments
		$this->setFile( $newFilename );
		$this->setColumns( $args['columns'] );
		$this->setFilterId( $args['room'] );

		// Get column names
		$allColumnNames = \MPHB\CSV\Bookings\BookingsExporterHelper::getBookingsExportColumns();
		$columnNames    = array_intersect_key( $allColumnNames, array_flip( $args['columns'] ) );

		// Push headers to new file
		$csv = new CSVFile( $this->pathToFile() );
		$csv->put( $columnNames );

		return $this;
	}

	/**
	 * @param int $bookingId All imported bookings already filtered.
	 * @return boolean
	 */
	protected function task( $bookingId ) {

		if ( $this->isAborting() ) {
			// Prevent updating the batch data
			add_filter( $this->identifier . '_aborting', '__return_true' );

			// Prevent doing all the current tasks
			add_filter( $this->identifier . '_time_exceeded', '__return_true' );

			$this->removeFile();

			return false;
		}

		$booking = MPHB()->getBookingRepository()->findById( $bookingId );

		if ( is_null( $booking ) ) {
			return false;
		}

		$csv         = new CSVFile( $this->pathToFile(), 'a' );
		$columnNames = $this->getColumns();
		$filterId    = $this->getFilterId();

		foreach ( $booking->getReservedRooms() as $room ) {

			if ( -1 != $filterId && $filterId != $room->getRoomTypeId() ) {
				continue;
			}

			$reservedRoomData = BookingsExporterHelper::getReservedRoomData( $room, $columnNames );
			$csv->put( $reservedRoomData );
		}

		return false;
	}

	public function abort() {
		if ( $this->isInProgress() ) {
			update_option( $this->abortOption, true, 'no' );

			$this->cancel_process();
		}
	}

	public function isAborting() {
		global $wpdb;

		// The code partly from function get_option()
		$suppressStatus = $wpdb->suppress_errors();
		$query          = $wpdb->prepare( "SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = %s LIMIT 1", $this->abortOption );
		$row            = $wpdb->get_row( $query );
		$wpdb->suppress_errors( $suppressStatus );

		if ( is_object( $row ) ) {
			return maybe_unserialize( $row->option_value );
		} else {
			return false;
		}
	}

	protected function afterComplete() {
		parent::afterComplete();

		delete_option( $this->abortOption );
		delete_option( $this->columnsOption );
		delete_option( $this->filterRoomOption );
	}

	/**
	 * @param string $filename Filename like "filename.csv".
	 */
	protected function setFile( $filename ) {
		update_option( $this->fileOption, $filename, 'no' );
	}

	protected function getFile() {
		return get_option( $this->fileOption, '' );
	}

	protected function removeFile() {
		$oldFile = $this->pathToFile();

		if ( file_exists( $oldFile ) ) {
			@unlink( $oldFile );
		}
	}

	/**
	 * @param array $columns
	 */
	protected function setColumns( $columns ) {
		update_option( $this->columnsOption, $columns, 'no' );
	}

	/**
	 * @return array
	 */
	protected function getColumns() {
		return get_option( $this->columnsOption, array() );
	}

	/**
	 * @param int $roomTypeId
	 */
	protected function setFilterId( $roomTypeId ) {
		update_option( $this->filterRoomOption, $roomTypeId, 'no' );
	}

	/**
	 * @return int
	 */
	protected function getFilterId() {
		return (int) get_option( $this->filterRoomOption, -1 );
	}
}
