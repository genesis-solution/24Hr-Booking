<?php

namespace MPHB\CSV;

class CSVFile {

	protected $filepath  = '';
	protected $mode      = 'w';
	protected $delimeter = ',';

	/**
	 * @var resource|false
	 */
	protected $file = null;

	/**
	 * @param string $file
	 * @param string $mode Optional. "w" by default.
	 *
	 * @since 3.7.0 added new filter - "mphb_csv_file_delimeter".
	 */
	public function __construct( $file, $mode = 'w' ) {

		$this->filepath  = $file;
		$this->mode      = $mode;
		$this->delimeter = apply_filters( 'mphb_csv_file_delimeter', ',' );
	}

	public function __destruct() {

		if ( $this->file !== false ) {
			fclose( $this->file );
		}
	}

	/**
	 * @param array $fields
	 * @return self
	 */
	public function put( $fields ) {

		if ( is_null( $this->file ) ) {
			$this->file = fopen( $this->filepath, $this->mode );
		}

		if ( $this->file !== false ) {
			// It's better to strip keys before calling fputcsv()
			// See https://www.php.net/manual/en/function.fputcsv.php#123807
			fputcsv( $this->file, array_values( $fields ), $this->delimeter );
		}

		return $this;
	}
}
