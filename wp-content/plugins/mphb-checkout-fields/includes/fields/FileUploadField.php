<?php

namespace MPHB\CheckoutFields\Fields;

use MPHB\Admin\Fields\InputField;

/**
 *
 * @since 1.0.5
 */
class FileUploadField extends InputField {

	const TYPE = 'file-upload';

	const SECRET_UPLOADS = 'mphb_protected_uploads';

	/**
	 * @var string
	 */
	protected $default = '';

	/**
	 * @var string
	 */
	protected $value = '';

	private $fileTypes;
	private $uploadSize;

	public function __construct( $name, $args, $value = '' ) {

		parent::__construct( $name, $args, $value );

		$this->fileTypes  = isset( $args['file_types'] ) ? $args['file_types'] : array();
		$this->uploadSize = isset( $args['upload_size'] ) ? (int) $args['upload_size'] : 0;
	}

	/**
	 * @return string
	 */
	protected function renderInput() {
		return $this->renderFields();
	}

	public function renderFields(): string {

		$inputAtts = array(
			'name' => $this->name,
			'id'   => $this->name,
		);

		if ( $this->required ) {
			$inputAtts['required'] = 'required';
		}

		$output  = '<input type="file"' . mphb_tmpl_render_atts( $inputAtts ) . '/>';
		$output .= $this->renderUploadFileSize();
		$output .= $this->renderUploadFileType();

		return $output;
	}

	protected function renderUploadFileSize() {

		$output = '<br><span class="mphb-max-upload-file">';

		$output .= sprintf(
			/* translators: %s: Maximum allowed file size. */
			__( 'Maximum upload file size: %s.' ),
			esc_html( size_format( $this->uploadSize ) )
		);

		$output .= '</span>';

		return $output;
	}

	protected function renderUploadFileType() {

		$output = '';

		$types = $this->fileTypes;

		if ( ! empty( $types ) && is_array( $types ) ) {
			$fileTypesStr = implode( ', ', $types );

			$output .= '<br><span class="mphp-accepted-upload-types">';

			$output .= sprintf(
				/* translators: %s: Accepted file types. */
				__( 'Accepted file types: %s.' ),
				esc_html( $fileTypesStr )
			);

			$output .= '</span>';
		}

		return $output;
	}

	/**
	 * @param string $fieldName - for example 'First Name'
	 * @param array  $data is a single element of $_FILES
	 * @param array  $field - contains field data from Hotel Booking plugin, example: [
	 *       'label' => '...',
	 *       'type' => 'text',
	 *       'enabled' => true,
	 *       'required' => false,
	 *       'labels' => [ 'required_error' => __('First name is required.', 'motopress-hotel-booking'), ... ]
	 * ]
	 *
	 * @return string related path of uploaded file or error
	 * @throws Exception when file upload failed
	 */
	public static function uploadFile( string $fieldName, array $data, array $field ) {

		if ( !$field['required'] && 0 == $data['size'] ) {
			return;
		}

		$maxAllowedFileSize = isset( $field['upload_size'] ) ? $field['upload_size'] : 0;

		if ( ! empty( $maxAllowedFileSize ) && $maxAllowedFileSize < absint( $data['size'] ) ) {

			// translators: %s is a name of a field, for example 'Your ID photo'
			throw new \Exception( sprintf( __( 'The size of the uploading %s file is over the limit.', 'mphb-checkout-fields' ), $field['label'] ) );
		}

		$supportedFileExtensions = isset( $field['file_types'] ) ? $field['file_types'] : array();

		$allowedFileMimeTypes = wp_get_mime_types();

		if ( ! empty( $supportedFileExtensions ) ) {

			$allowedFileMimeTypes = array_filter(
				$allowedFileMimeTypes,
				function( $arrayValue, $arrayKey ) use ( $supportedFileExtensions ) {
					$keys = explode( '|', $arrayKey );
					if ( ! empty( array_intersect( $keys, $supportedFileExtensions ) ) ) {
						return true;
					}
				},
				ARRAY_FILTER_USE_BOTH
			);

			if ( empty( $allowedFileMimeTypes ) || ! in_array( $data['type'], $allowedFileMimeTypes ) ) {

				// translators: %s is a name of a field, for example 'Your ID photo' and the second %s is a file type like image/jpeg
				throw new \Exception( sprintf( __( 'Uploading %1$s file %2$s is not allowed.', 'mphb-checkout-fields' ), $field['label'], $data['type'] ) );
			}
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin' . '/includes/file.php';
		}

		$wpHandleUploadOverrides['test_form'] = false;

		if ( ! empty( $allowedFileMimeTypes ) ) {
			$wpHandleUploadOverrides['mimes'] = $allowedFileMimeTypes;
		}

		$wpHandleUploadOverrides['unique_filename_callback'] = function( string $dir, string $baseFilename, string $extension ) use ( $fieldName ) {

			$uploadingFileName  = MPHB()->addPrefix( mb_strtolower( $fieldName ), '-' );
			$now                = \DateTime::createFromFormat( 'U.u', microtime( true ) );
			$uploadingFileName .= '-' . $now->format( 'mdY-His-u' );

			$index        = 0;
			$fullFileName = '';

			do {

				$index++;
				$fullFileName = $uploadingFileName . "-$index" . $extension;

			} while ( file_exists( $dir . '/' . $fullFileName ) );

			return $fullFileName;
		};

		$changeWPUploadFolder = function ( $param ) {
			$mydir         = '/' . \MPHB\CheckoutFields\Fields\FileUploadField::SECRET_UPLOADS;
			$param['path'] = $param['basedir'] . $mydir;
			$param['url']  = $param['baseurl'] . $mydir;

			return $param;
		};

		add_filter( 'upload_dir', $changeWPUploadFolder, 10 );

		$uploadResult = wp_handle_upload( $data, $wpHandleUploadOverrides );

		remove_filter( 'upload_dir', $changeWPUploadFolder, 10 );

		if ( isset( $uploadResult['error'] ) ) {

			// translators: %s is a name of a field, for example 'Your ID photo'
			throw new \Exception(
				sprintf( __( 'Uploading %s file went through with some kind of error.', 'mphb-checkout-fields' ), $field['label'] ) .
				' ' . $uploadResult['error']
			);
		}

		if ( isset( $uploadResult['file'] ) ) {
			$path = str_replace( ABSPATH, '', $uploadResult['file'] );
			return $path;
		}

		// translators: %s is a name of a field, for example 'Your ID photo'
		throw new \Exception( sprintf( __( 'Uploading %s file went through with some kind of error.', 'mphb-checkout-fields' ), $field['label'] ) );
	}

	public static function processViewUploadedFileRequest() {

		if ( ! isset( $_GET['upload_id'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			auth_redirect();
			exit();
		}

		$args = apply_filters(
			'mphb_cf_process_upload_link_args',
			array(
				'upload_id'  => isset( $_GET['upload_id'] ) ? (int) $_GET['upload_id'] : null,
				'booking_id' => isset( $_GET['booking_id'] ) ? (int) $_GET['booking_id'] : null,
			)
		);

		if ( ! $args['booking_id'] ) {
			return;
		}

		$args['has_access'] = current_user_can( 'read_mphb_checkout_fields' );

		$args['has_access'] = apply_filters( 'mphb_cf_upload_link_has_access', $args['has_access'], $args );

		if ( ! $args['has_access'] ) {
			return;
		}

		$metaValue = get_metadata_by_mid( 'post', $args['upload_id'] );

		if ( ! $metaValue || ! $metaValue->meta_value ) {
			return;
		}

		$file = $metaValue->meta_value;

		if ( ! file_exists( $file ) ) {
			return;
		}

		$mime = wp_check_filetype( $file );

		if ( false === $mime['type'] && function_exists( 'mime_content_type' ) ) {
			$mime['type'] = mime_content_type( $file );
		}

		if ( $mime['type'] ) {
			$mimetype = $mime['type'];
		} else {
			$mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );
		}

		nocache_headers();
		header( 'Robots: none' );
		header( 'Content-Type: ' . $mimetype . ';charset=' . mb_detect_encoding( file_get_contents( $file ), mb_list_encodings() ) );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Content-Length: ' . filesize( $file ) );

		if ( empty( $_SERVER['SERVER_SOFTWARE'] ) ||
			false === strpos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'Microsoft-IIS' ) ) {

			header( 'Content-Length: ' . filesize( $file ) );
		}

		$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
		$etag          = '"' . md5( $last_modified ) . '"';

		header( "Last-Modified: $last_modified GMT" );
		header( 'ETag: ' . $etag );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );

		// Support for Conditional GET
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) : false;

		if ( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
		}

		$client_last_modified = trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) );
		// If string is empty, return 0. If not, attempt to parse into a timestamp
		$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

		// Make a timestamp for our most recent modification...
		$modified_timestamp = strtotime( $last_modified );

		if ( ( $client_last_modified && $client_etag )
			? ( ( $client_modified_timestamp >= $modified_timestamp ) && ( $client_etag == $etag ) )
			: ( ( $client_modified_timestamp >= $modified_timestamp ) || ( $client_etag == $etag ) )
			) {
			status_header( 304 );
			exit;
		}

		@readfile( $file );
		exit( 0 );
	}

	public static function getUploadedFileLink( int $bookingId, string $fileUploadFieldName ) {

		global $wpdb;

		$uploadFieldMetaData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $bookingId, $fileUploadFieldName ) );

		$link = '';

		if ( ! empty( $uploadFieldMetaData->meta_id ) && ! empty( $uploadFieldMetaData->meta_value ) ) {

			$link = add_query_arg(
				array(
					'booking_id' => (int) $bookingId,
					'upload_id'  => (int) $uploadFieldMetaData->meta_id,
				),
				get_site_url( get_current_blog_id() )
			);
		}

		return $link;
	}

	public static function deleteUploadedCheckoutFieldsFilesOfBooking( int $wpPostId ): void {

		if ( get_post_type( $wpPostId ) != MPHB()->postTypes()->booking()->getPostType() ) {
			return;
		}

		$customFields = MPHB()->settings()->main()->getCustomerBundle()->getCustomerFields();

		if ( empty( $customFields ) ) {
			return;
		}

		foreach ( $customFields as $fieldName => $customField ) {

			if ( 'file_upload' == $customField['type'] ) {

				$fileUploadFieldName = MPHB()->addPrefix( $fieldName, '_' );

				$uploadedFilePathes = get_post_meta( $wpPostId, $fileUploadFieldName );

				foreach ( $uploadedFilePathes as $filePath ) {

					wp_delete_file( ABSPATH . $filePath );
				}
			}
		}
	}
}
