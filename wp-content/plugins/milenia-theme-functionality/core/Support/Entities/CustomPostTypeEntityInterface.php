<?php
/**
 * The CustomPostTypeEntityInterface interface.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

namespace Milenia\Core\Support\Entities;

interface CustomPostTypeEntityInterface
{
    /**
     * Registers taxonomies for the custom post type.
     *
     * @param array $taxonomies
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function taxonomies( array $taxonomies = array() );

    /**
     * Registers taxonomies for the custom post type.
     *
     * @param array $metaboxes
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function metaboxes( array $metaboxes = array() );

    /**
     * Registers actions for edit columns in the admin panel.
     *
     * @param string $callback_columns
     * @param string $callback_columns_content
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function adminColumns( $columns_callback_name, $columns_content_callback_name );
}
?>
