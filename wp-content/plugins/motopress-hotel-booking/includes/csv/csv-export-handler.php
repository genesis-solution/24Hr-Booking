<?php

namespace MPHB\CSV;

class CSVExportHandler {

	public function __construct() {

		add_action(
			'init',
			function() {
				
				$this->maybeDownloadExportingData();
			}
		);
	}

	private function maybeDownloadExportingData() {

		$requestedAction = ! empty( $_GET['mphb_action'] ) ? sanitize_text_field( wp_unslash( $_GET['mphb_action'] ) ) : '';

		if ( ! is_admin() || 'download' !== $requestedAction ||
			! current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EXPORT_REPORTS ) ) {
			return;
		}

		$fileName = isset( $_GET['filename'] ) ? sanitize_text_field( wp_unslash( $_GET['filename'] ) ) : '';

		if ( ! mphb_verify_nonce( "mphb_download-{$fileName}" ) ) {

			wp_die(
				esc_html__( 'Nonce verification failed.', 'motopress-hotel-booking' ),
				esc_html__( 'Error', 'motopress-hotel-booking' ),
				array( 'response' => 403 )
			);
			exit;
		}

		$filePath = mphb_uploads_dir() . $fileName;

		$realFilePath = realpath( $filePath );
		$realBasePath = realpath( mphb_uploads_dir() ) . DIRECTORY_SEPARATOR;

		if ( empty( $fileName ) || ! file_exists( $filePath ) ||
			// check is uploads folder in the real file path
			false === $realFilePath || 0 !== strpos( $realFilePath, $realBasePath )
		) {

			wp_die(
				esc_html__( 'The file does not exist.', 'motopress-hotel-booking' ),
				esc_html__( 'Error', 'motopress-hotel-booking' ),
				array( 'response' => 403 )
			);
			exit;
		}

		$isDeleteFile = ! isset( $_GET['remove'] ) || 'no' !== $_GET['remove'];

		// download file
		ignore_user_abort( true );
		nocache_headers();

		$disabledFunction = explode( ',', ini_get( 'disable_functions' ) );

		if ( ! in_array( 'set_time_limit', $disabledFunction ) ) {
			set_time_limit( 0 );
		}

		$mime    = wp_check_filetype( $filePath );
		$content = @file_get_contents( $filePath );

		if ( $isDeleteFile ) {
			@unlink( $filePath );
		}

		header( 'Content-Type: ' . $mime['type'] . '; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $fileName );
		header( 'Expires: 0' );

        // phpcs:ignore
		echo $content;

		exit();
	}
}
