<?php

namespace MPHB\CheckoutFields\Entities;

/**
 * @since 1.0
 */
class CheckoutField {

	public $id              = 0;
	public $title           = '';
	public $name            = '';
	public $type            = 'text';
	public $innerLabel      = '';
	public $textContent     = '';
	public $placeholder     = '';
	public $pattern         = '';
	public $description     = '';
	public $cssClass        = '';
	public $options         = array();
	public $fileTypes       = array();
	public $uploadSize      = 0;
	public $protectedUpload = true;
	public $isChecked       = false;
	public $isEnabled       = true;
	public $isRequired      = true;

	public $value = null;


	public function __construct( $args = array() ) {

		$args = array_merge(
			array(
				'id'           => $this->id,
				'title'        => $this->title,
				'name'         => $this->name,
				'type'         => $this->type,
				'inner_label'  => $this->innerLabel,
				'text_content' => $this->textContent,
				'placeholder'  => $this->placeholder,
				'pattern'      => $this->pattern,
				'description'  => $this->description,
				'css_class'    => $this->cssClass,
				'options'      => $this->options,
				'file_types'   => $this->fileTypes,
				'upload_size'  => $this->uploadSize,
				'checked'      => $this->isChecked,
				'enabled'      => $this->isEnabled,
				'required'     => $this->isRequired,
			),
			$args
		);

		$this->id          = $args['id'];
		$this->title       = $args['title'];
		$this->name        = $args['name'];
		$this->type        = $args['type'];
		$this->innerLabel  = $args['inner_label'];
		$this->textContent = $args['text_content'];
		$this->placeholder = $args['placeholder'];
		$this->pattern     = $args['pattern'];
		$this->description = $args['description'];
		$this->cssClass    = $args['css_class'];
		$this->options     = $args['options'];
		$this->fileTypes   = $this->parseFileTypes( $args['file_types'] );
		$this->uploadSize  = $this->parseUploadSize( $args['upload_size'] );
		$this->isChecked   = $args['checked'];
		$this->isEnabled   = $args['enabled'];
		$this->isRequired  = $args['required'];
	}

	/**
	 *
	 * @param int $uploadSize
	 *
	 * @since 1.0.6
	 */
	protected function parseUploadSize( $uploadSize ) {

		if ( ! empty( $uploadSize ) ) {

			$uploadSize = absint( $uploadSize );

			if ( 0 < $uploadSize ) {
				return $uploadSize;
			}
		}

		return wp_max_upload_size();
	}

	/**
	 * Some classes like repositories call getId() to get an ID of the entity.
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @since 1.0.5
	 */
	protected function parseFileTypes( $allowedFileTypesString ) {

		$allowedFileTypes = ! empty( $allowedFileTypesString ) ? explode( ',', $allowedFileTypesString ) : array();

		if ( ! empty( $allowedFileTypes ) ) {

			$allowedFileTypes = array_map( 'trim', $allowedFileTypes );
		}

		return ! empty( $allowedFileTypes ) ? $allowedFileTypes : array();
	}
}
