<?php


namespace MPHB\Repositories;

use MPHB\Entities\AccommodationAttribute;

class AttributeRepository extends AbstractPostRepository {

	protected $type = 'attribute';
	/**
	 *
	 * @param int  $id
	 * @param bool $force
	 * @return AccommodationAttribute
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	public function mapPostToEntity( $post ) {
		$id                         = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;
		$atts['id']                 = $id;
		$atts['status']             = get_post_status( $id );
		$atts['title']              = get_the_title( $id );
		$atts['enable_archives']    = boolval( get_post_meta( $id, 'mphb_public', true ) );
		$atts['visible_in_details'] = boolval( get_post_meta( $id, 'mphb_visible', true ) );
		$atts['default_sort_order'] = get_post_meta( $id, 'mphb_orderby', true );
		$atts['default_text']       = get_post_meta( $id, 'mphb_default_text', true );

		return new AccommodationAttribute( $atts );
	}

	public function mapEntityToPostData( $entity ) {
		$postAtts = array(
			'ID'          => $entity->getId(),
			'post_metas'  => array(),
			'post_status' => $entity->getStatus(),
			'post_title'  => $entity->getTitle(),
			'post_type'   => MPHB()->postTypes()->attributes()->getPostType(),
		);

		$postAtts['post_metas'] = array(
			'mphb_public'       => $entity->getEnableArchives(),
			'mphb_visible'      => $entity->getVisibleInDetails(),
			'mphb_orderby'      => $entity->getDefaultSortOrder(),
			'mphb_default_text' => $entity->getDefaultText(),
		);

		return new \MPHB\Entities\WPPostData( $postAtts );
	}

}
