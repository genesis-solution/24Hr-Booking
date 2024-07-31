<?php

namespace MPHB\Admin\Fields;

class ServiceChooserField extends InputField {

	const TYPE = 'service-chooser';

	protected $default    = array();
	protected $showAddNew = false;
	protected $showPrices = false;

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->showAddNew = isset( $details['show_add_new'] ) ? $details['show_add_new'] : $this->showAddNew;
		$this->showPrices = isset( $details['show_prices'] ) ? $details['show_prices'] : $this->showPrices;
	}

	protected function renderInput() {
		ob_start();
		?>
		<div class="categorydiv" id="<?php echo esc_attr( MPHB()->addPrefix( $this->getName() ) ); ?>" >
			<div class="tabs-panel">
				<input type="hidden" name="<?php echo esc_attr( $this->getName() ); ?>" value="">
				<ul class="categorychecklist form-no-clear">
					<?php foreach ( MPHB()->getServiceRepository()->findAll() as $service ) { ?>
						<li class="popular-category">
							<?php $labelHtml = ( $this->showPrices ? $service->getTitle() . ' (' . $service->getPriceWithConditions() . ')' : $service->getTitle() ); ?>
							<label class="selectit">
								<input value="<?php echo esc_attr( $service->getId() ); ?>"
									   type="checkbox"
									   name="<?php echo esc_attr( $this->getName() ); ?>[]"
									   <?php echo in_array( $service->getId(), $this->value ) ? 'checked="checked"' : ''; ?>
									   style="margin-top: 0;"
									   />
									   <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo $labelHtml;
										?>
							</label>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
		if ( $this->showAddNew ) {
			$servicePostTypeObj = get_post_type_object( MPHB()->postTypes()->service()->getPostType() );
			?>
			<a  href="<?php echo esc_attr( MPHB()->postTypes()->service()->getEditPage()->getUrl( array(), true ) ); ?>"
				target="_blank"
				class="taxonomy-add-new"
				>+ 
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $servicePostTypeObj->labels->add_new_item;
				?>
				</a>
			<?php
		}
		return ob_get_clean();
	}

}
