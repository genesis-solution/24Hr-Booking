<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\EditCPTPages;
use \MPHB\Admin\ManageCPTPages;

abstract class EditableCPT extends AbstractCPT {

	/**
	 *
	 * @var EditCPTPages\EditCPTPage
	 */
	protected $editPage;

	/**
	 *
	 * @var ManageCPTPages\ManageCPTPage
	 */
	protected $managePage;

	protected function addActions() {

		parent::addActions();

		add_action(
			'init',
			function() {

				if ( is_admin() ) {

					$this->editPage = $this->createEditPage();
					$this->managePage = $this->createManagePage();
				}
			}
		);

		add_action( 'admin_footer', array( $this, 'addBackButton' ) );
	}

	/**
	 *
	 * @return \MPHB\Admin\EditCPTPages\EditCPTPage
	 */
	protected function createEditPage() {
		return new EditCPTPages\EditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	/**
	 *
	 * @return \MPHB\Admin\ManageCPTPages\ManageCPTPage
	 */
	protected function createManagePage() {
		return new ManageCPTPages\ManageCPTPage( $this->postType );
	}

	/**
	 *
	 * @return EditCPTPages\EditCPTPage
	 */
	public function getEditPage() {
		return $this->editPage;
	}

	/**
	 *
	 * @return ManageCPTPages\ManageCPTPage
	 */
	public function getManagePage() {
		return $this->managePage;
	}

	public function getMenuSlug() {
		return 'edit.php?post_type=' . $this->getPostType();
	}

	public function registerMetaBoxes() {
		do_action( "mphb_register_{$this->postType}_metaboxes" );
	}

	abstract public function getFieldGroups();

	public function addBackButton() {

		if ( $this->isCurrentEditPage() ) {

			$url = admin_url( $this->getMenuSlug() );

			?>
			<script type="text/javascript">
				(function( $ ) {
					$( function() {
						var backButton = $( '<a />', {
							'class': 'page-title-action wp-exclude-emoji',
							'html': '<?php esc_html_e( 'Back', 'motopress-hotel-booking' ); ?> &#10548;&#xFE0E;',
							'href': '<?php echo esc_url( $url ); ?>',
						} );
						jQuery( '#wpbody-content > .wrap > .wp-heading-inline' ).after( backButton.clone() );
					} );
				})( jQuery );
			</script>
			<?php
		}
	}

	public function isCurrentEditPage() {
		global $typenow, $pagenow;
		return is_admin() && $typenow === $this->postType && ( $pagenow === 'post.php' || $pagenow === 'post-new.php' );
	}

}
