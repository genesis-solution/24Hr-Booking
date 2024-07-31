<?php

namespace MPHB\Admin\EditCPTPages;

use \MPHB\Entities;

class AttributesEditCPTPage extends EditCPTPage {

	public function customizeMetaBoxes() {
		remove_meta_box( 'submitdiv', $this->postType, 'side' );

		add_meta_box( 'submitdiv', translate( 'Publish' ), array( $this, 'renderPublishMetabox' ), $this->postType, 'side' );

		if ( ! $this->isCurrentAddNewPage() ) {
			// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
			add_meta_box( 'terms', __( 'Terms', 'motopress-hotel-booking' ), array( $this, 'renderTermsMetaBox' ), $this->postType, 'normal' );
		}
	}

	public function renderPublishMetabox( $post, $metabox ) {
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<?php if ( ! $this->isCurrentAddNewPage() ) { ?>
					<div id="misc-publishing-actions">
						<div class="misc-pub-section">
							<span><?php esc_html_e( 'Created on:', 'motopress-hotel-booking' ); ?></span>
							<strong><?php echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_date ) ) ); ?></strong>
						</div>
					</div>
				<?php } ?>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$deleteText = translate( 'Delete Permanently' );
						} else {
							$deleteText = translate( 'Move to Trash' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $deleteText ); ?></a>
						<?php
					}
					?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php $this->isCurrentAddNewPage() ? esc_attr_e( 'Publish' ) : esc_attr_e( 'Update' ); ?>" />
					<?php if ( $this->isCurrentAddNewPage() ) { ?>
						<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php esc_attr_e( 'Publish' ); ?>" />
					<?php } else { ?>
						<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ); ?>" />
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function renderTermsMetaBox( $post, $metabox ) {
		$postId = MPHB()->translation()->getOriginalId( $post->ID, $this->postType, false );

		if ( is_null( $postId ) ) {
			// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
			esc_html_e( 'Please add attribute in default language to configure terms.', 'motopress-hotel-booking' );
			return;
		}

		if ( $postId != $post->ID ) {
			// Get original post
			$post = get_post( $postId );
		}

		$attributeName = mphb_sanitize_attribute_name( $post->post_name );
		$terms         = MPHB()->getAttributesPersistence()->getTermsIdTitleList( $attributeName );

		$configureTermsUrl = add_query_arg(
			array(
				'taxonomy'  => mphb_attribute_taxonomy_name( $attributeName ),
				'post_type' => MPHB()->postTypes()->roomType()->getPostType(),
			),
			admin_url( 'edit-tags.php' )
		);

		?>
		<p>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ! empty( $terms ) ? esc_html( implode( ', ', $terms ) ) : static::EMPTY_VALUE_PLACEHOLDER;
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $configureTermsUrl ); ?>">
															  <?php
																// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
																esc_html_e( 'Configure terms', 'motopress-hotel-booking' );
																?>
				</a>
		</p>
		<?php
	}

}
