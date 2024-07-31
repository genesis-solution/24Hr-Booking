<?php

namespace MPHB\CheckoutFields\CPTPages;

use MPHB\Admin\EditCPTPages\EditCPTPage;
use MPHB\CheckoutFields\CheckoutFieldsHelper;
use MPHB\CheckoutFields\Plugin;

/**
 * @since 1.0
 */
class EditCheckoutFieldsPage extends EditCPTPage {

	public function addActions() {

		parent::addActions();

		add_filter( 'enter_title_here', array( $this, 'changeTitlePlaceholder' ) );
	}

	public function enqueueAdminScripts() {

		parent::enqueueAdminScripts();

		if ( $this->isCurrentPage() ) {

			wp_enqueue_script(
				'mphb-edit-checkout-field-scripts',
				CheckoutFieldsHelper::getUrlToFile( 'assets/js/edit-checkout-field-page.js' ),
				array( 'jquery' ),
				Plugin::getInstance()->getPluginVersion(),
				true
			);

			// Add info about type for translated pages that don't have the "Type" field
			wp_localize_script(
				'mphb-edit-checkout-field-scripts',
				'MPHBCheckoutField',
				array(
					'type' => get_post_meta( get_the_ID(), 'mphb_cf_type', true ),
				)
			);
		}
	}

	public function customizeMetaBoxes() {

		parent::customizeMetaBoxes();

		remove_meta_box( 'submitdiv', $this->postType, 'side' );
		add_meta_box( 'submitdiv', esc_html__( 'Update Checkout Field', 'mphb-checkout-fields' ), array( $this, 'displaySubmitMetabox' ), $this->postType, 'side' );
	}

	public function displaySubmitMetabox( $post ) {
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'mphb-checkout-fields' ); ?></span>
						<strong><?php echo date_i18n( mphb()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_date ) ); ?></strong>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) && ! CheckoutFieldsHelper::isDefaultCheckoutFieldPost( $post ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$deleteText = esc_html__( 'Delete Permanently', 'mphb-checkout-fields' );
						} else {
							$deleteText = esc_html__( 'Move to Trash', 'mphb-checkout-fields' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $deleteText; ?></a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'mphb-checkout-fields' ); ?>" />
					<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create', 'mphb-checkout-fields' ) : esc_attr_e( 'Update', 'mphb-checkout-fields' ); ?>" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function saveMetaBoxes( $postId, $post, $update ) {

		if ( ! parent::saveMetaBoxes( $postId, $post, $update ) ) {
			return false;
		}

		// Validate Name
		if ( isset( $_POST['mphb_cf_name'] ) ) {

			$name = $_POST['mphb_cf_name'];

			// Generate slug -> sanitize and decode any %## encoding in the title
			// to string with lowercased words, underscores "_" and dashes "-".
			$slug = sanitize_title( urldecode( $name ) );
		
			// Decode any %## encoding again after function sanitize_title(), to
			// translate something like "%d0%be%d0%b4%d0%b8%d0%bd" into "один"
			$validName = urldecode( $slug );

			$defaultFields = array_keys( mphb_get_default_customer_fields() );

			if ( in_array( $validName, $defaultFields ) ) {
				$validName .= $postId;
			}

			if ( $validName !== $name ) {
				update_post_meta( $postId, 'mphb_cf_name', $validName );
			}
		}
	}

	public function changeTitlePlaceholder( $title ) {
        
		if ( $this->isCurrentPage() ) {
			$title = esc_html__( 'Field Label', 'mphb-checkout-fields' );
		}

		return $title;
	}
}
