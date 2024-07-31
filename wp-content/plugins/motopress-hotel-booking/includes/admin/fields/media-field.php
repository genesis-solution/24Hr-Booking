<?php

namespace MPHB\Admin\Fields;

/**
 * @since 3.8.6
 */
class MediaField extends InputField {

	/** @since 3.8.6 */
	const TYPE = 'media';

	/**
	 * @var string
	 *
	 * @since 3.8.6
	 */
	protected $delimiter = ',';

	/**
	 * @var bool
	 *
	 * @since 3.8.6
	 */
	protected $isSingle = true;

	/**
	 * @var string
	 *
	 * @since 3.8.6
	 */
	protected $thumbSize = 'full';

	/**
	 * @param string $name
	 * @param array  $details
	 * @param mixed  $value Optional. '' by default.
	 *
	 * @since 3.8.6
	 */
	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		$this->delimiter = isset( $details['delimiter'] ) ? $details['delimiter'] : $this->delimiter;
		$this->isSingle  = isset( $details['single'] ) ? $details['single'] : $this->isSingle;
		$this->thumbSize = isset( $details['thumbnail_size'] ) ? $details['thumbnail_size'] : $this->thumbSize;
	}

	/**
	 * @return string
	 *
	 * @since 3.8.6
	 */
	protected function renderInput() {
		$previewSrc     = '';
		$previewClasses = 'media-ctrl-thumbnail attachment-post-thumbnail size-post-thumbnail'
			. ( $this->isSingle ? ' single-image-control' : '' );
		$addClasses     = 'mphb-admin-organize-image-add';
		$removeClasses  = 'mphb-admin-organize-image-remove';

		if ( empty( $this->value ) ) {
			$previewClasses .= ' mphb-hide';
			$removeClasses  .= ' mphb-hide';
		} else {
			$addClasses .= ' mphb-hide';

			$thumbIds  = explode( $this->delimiter, $this->value );
			$previewId = absint( array_shift( $thumbIds ) );
			$imageData = wp_get_attachment_image_src( $previewId, $this->thumbSize, false );

			if ( $imageData !== false ) {
				$previewSrc = $imageData[0];
			}
		}

		$addLabel    = $this->isSingle ? __( 'Add image', 'motopress-hotel-booking' ) : __( 'Add gallery', 'motopress-hotel-booking' );
		$removeLabel = $this->isSingle ? __( 'Remove image', 'motopress-hotel-booking' ) : __( 'Remove gallery', 'motopress-hotel-booking' );

		$inputId = MPHB()->addPrefix( $this->getName() );

		// Render Input
		$result = '<input type="hidden" id="' . esc_attr( $inputId ) . '" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->value ) . '" ' . $this->generateAttrs() . '>';

		$result     .= '<div class="mphb-preview-wrapper">';
			$result .= '<img src="' . esc_url( $previewSrc ) . '" class="' . esc_attr( $previewClasses ) . '">';
		$result     .= '</div>';

		$result .= '<a href="#" class="' . esc_attr( $addClasses ) . '">' . esc_html( $addLabel ) . '</a>';
		$result .= '<a href="#" class="' . esc_attr( $removeClasses ) . '">' . esc_html( $removeLabel ) . '</a>';

		return $result;
	}

	/**
	 * @return string
	 *
	 * @since 3.8.6
	 */
	protected function generateAttrs() {
		$attrs = parent::generateAttrs();

		$attrs .= $this->isSingle ? ' is-single="is-single"' : '';
		$attrs .= ' image-size="' . esc_attr( $this->thumbSize ) . '"';

		return $attrs;
	}

	/**
	 * @param string $value
	 * @return int|string
	 *
	 * @since 3.8.6
	 */
	public function sanitize( $value ) {
		$thumbs = explode( $this->delimiter, $value );

		// Remove unexisting thumbnail IDs
		foreach ( $thumbs as $index => $thumbId ) {
			if ( ! wp_attachment_is_image( $thumbId ) ) {
				unset( $thumbs[ $index ] );
			}
		}

		if ( empty( $thumbs ) ) {
			return '';
		} elseif ( $this->isSingle ) {
			return absint( array_shift( $thumbs ) );
		} else {
			return implode( $this->delimiter, $thumbs );
		}
	}
}
