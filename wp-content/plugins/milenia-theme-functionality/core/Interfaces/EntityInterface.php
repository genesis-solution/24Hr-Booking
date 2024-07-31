<?php
/**
 * Common interface that allows customer to register an entity in the
 * system.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

namespace Milenia\Core\Interfaces;

interface EntityInterface
{
    /**
     * Returns the entity name.
     *
     * @access public
     * @return string
     */
    public function getName();

    /**
     * Returns the entity attributes.
     *
     * @access public
     * @return array
     */
    public function getAttributes();
}
?>
