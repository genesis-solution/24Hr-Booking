<?php
/**
 * The CustomPostTypeEntity entity.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

namespace Milenia\Core\Support\Entities;

use Milenia\Core\Interfaces\EntityInterface;

class CustomPostTypeEntity implements CustomPostTypeEntityInterface, EntityInterface
{
    /**
     * Contains a name of the custom post type.
     *
     * @access protected
     * @var string
     */
    protected $name;

    /**
     * Contains an arguments of the custom post type.
     *
     * @access protected
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Contains an array of meta boxes of the custom post type.
     *
     * @access protected
     * @var array
     */
    protected $metaboxes = array();

    /**
     * Constructor of the class.
     *
     * @param string $name
     * @param array $attributes
     */
    public function __construct($name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;

        add_action('add_meta_boxes', array($this, 'registerMetaBoxes'));
    }

    /**
     * Returns the entity name.
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the entity attributes.
     *
     * @access public
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Registers taxonomies for the custom post type.
     *
     * @param array $taxonomies
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function taxonomies( array $taxonomies = array() )
    {
        if(count($taxonomies)) {
            foreach ($taxonomies as $name => $args) {
                register_taxonomy($name, $this->name, $args);
            }
        }

        return $this;
    }

    /**
     * Registers meta boxes for the custom post type.
     *
     * @param array $metaboxes
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function metaboxes( array $metaboxes = array() )
    {
        $this->metaboxes = array_merge($this->metaboxes, $metaboxes);

        return $this;
    }

    /**
     * Registers actions for edit columns in the admin panel.
     *
     * @param string $callback_columns
     * @param string $callback_columns_content
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function adminColumns($columns_callback_name, $columns_content_callback_name )
    {
        add_action('manage_' . $this->name . '_posts_columns', $columns_callback_name);
        add_action('manage_' . $this->name . '_posts_custom_column', $columns_content_callback_name);

        return $this;
    }

    /**
     * Registers meta boxes of the custom post type in WordPress ecosystem.
     *
     * @access public
     * @return void
     */
    public function registerMetaBoxes()
    {
        if(count($this->metaboxes)) {
            foreach($this->meatboxes as $metabox) {
                add_meta_box($metabox);
            }
        }
    }

    /**
     * Edits admin columns.
     *
     * @param array $columns
     * @access public
     */
    public function editAdminColumns($columns)
    {
        return array_merge($this->admin_columns, $columns);
    }
}
?>
