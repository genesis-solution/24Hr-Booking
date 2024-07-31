<?php

namespace MPHB\Admin\Fields;

class RichEditorField extends TextareaField {

	const TYPE = 'rich-editor';

	protected $rows              = 10;
	protected $isMediaButtonsOn  = true;
	protected $tinymceSettings   = array(
		'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_adv',
	);
	protected $quicktagsSettings = true;

	public function __construct( $name, $details, $value = '' ) {

		parent::__construct( $name, $details, $value );

		$this->rows              = ( isset( $details['rows'] ) ) ? $details['rows'] : $this->rows;
		$this->isMediaButtonsOn  = ( isset( $details['isMediaButtonsOn'] ) ) ? $details['isMediaButtonsOn'] : $this->isMediaButtonsOn;
		$this->tinymceSettings   = ( isset( $details['tinymceSettings'] ) ) ? $details['tinymceSettings'] : $this->tinymceSettings;
		$this->quicktagsSettings = ( isset( $details['quicktagsSettings'] ) ) ? $details['quicktagsSettings'] : $this->quicktagsSettings;
	}

	protected function renderInput() {

		ob_start();

		wp_editor(
			$this->value,
			'mphb_field_' . $this->getName(),
			array(
				'wpautop'       => false,
				'media_buttons' => $this->isMediaButtonsOn,
				'textarea_name' => esc_attr( $this->getName() ),
				'textarea_rows' => $this->rows,
				'editor_class'  => $this->generateSizeClasses(),
				'tinymce'       => $this->tinymceSettings,
				'quicktags'     => $this->quicktagsSettings,
			)
		);

		$result = ob_get_clean();

		return $result;
	}

}
