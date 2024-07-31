<?php

namespace MPHB\CheckoutFields\Repositories;

use MPHB\CheckoutFields\Entities\CheckoutField;
use MPHB\Entities\WPPostData;
use MPHB\Repositories\AbstractPostRepository;
use MPHB\CheckoutFields\Plugin;

/**
 * @since 1.0
 */
class CheckoutFieldRepository extends AbstractPostRepository
{
    protected $type = 'checkout_field';

    /**
     * @param \WP_Post|int $post
     * @return \MPHB\CheckoutFields\Entities\CheckoutField
     */
    public function mapPostToEntity($post)
    {
        if( !is_a($post, '\WP_Post') ) {
            $post = get_post($post);
        }

        $postId = isset($post->ID) ? (int)$post->ID : null;
        $postTitle = isset($post->post_title) ? $post->post_title : '';

        $optionsValue = get_post_meta($postId, 'mphb_cf_options', true);
        $options = [];

        if (is_array($optionsValue)) {
            foreach ($optionsValue as $optionsRow) {
                $value = $optionsRow['value'];
                $label = $optionsRow['label'];

                $options[$value] = $label;
            }
        }

        $atts = [
            'id'           => absint($postId),
            'title'        => $postTitle,
            'name'         => get_post_meta($postId, 'mphb_cf_name', true),
            'type'         => get_post_meta($postId, 'mphb_cf_type', true),
            'inner_label'  => get_post_meta($postId, 'mphb_cf_inner_label', true),
            'text_content' => get_post_meta($postId, 'mphb_cf_text_content', true),
            'placeholder'  => get_post_meta($postId, 'mphb_cf_placeholder', true),
            'pattern'      => get_post_meta($postId, 'mphb_cf_pattern', true),
            'description'  => get_post_meta($postId, 'mphb_cf_description', true),
            'css_class'    => get_post_meta($postId, 'mphb_cf_css_class', true),
            'options'      => $options,
            'file_types'   => get_post_meta($postId, 'mphb_cf_file_types', true),
            'upload_size'  => get_post_meta($postId, 'mphb_cf_upload_size', true),
            'checked'      => (bool)get_post_meta($postId, 'mphb_cf_checked', true),
            'enabled'      => (bool)get_post_meta($postId, 'mphb_cf_enabled', true),
            'required'     => (bool)get_post_meta($postId, 'mphb_cf_required', true)
        ];

        return new CheckoutField($atts);
    }

    /**
     * @param \MPHB\CheckoutFields\Entities\CheckoutField $entity
     * @return \MPHB\Entities\WPPostData
     */
    public function mapEntityToPostData($entity)
    {
        $optionsSet = [];

        foreach ($entity->options as $value => $label) {
            $optionsSet[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        $atts = [
            'ID'         => $entity->id,
            'post_type'  => Plugin::getInstance()->getCheckoutFieldsPostType()->getPostType(),
            'post_title' => $entity->title,
            'post_metas' => [
                'mphb_cf_name'         => $entity->name,
                'mphb_cf_type'         => $entity->type,
                'mphb_cf_inner_label'  => $entity->innerLabel,
                'mphb_cf_text_content' => $entity->textContent,
                'mphb_cf_placeholder'  => $entity->placeholder,
                'mphb_cf_pattern'      => $entity->pattern,
                'mphb_cf_description'  => $entity->description,
                'mphb_cf_css_class'    => $entity->cssClass,
                'mphb_cf_options'      => $optionsSet,
                'mphb_cf_checked'      => (int)$entity->isChecked,
                'mphb_cf_enabled'      => (int)$entity->isEnabled,
                'mphb_cf_required'     => (int)$entity->isRequired
            ]
        ];

        return new WPPostData($atts);
    }
}
