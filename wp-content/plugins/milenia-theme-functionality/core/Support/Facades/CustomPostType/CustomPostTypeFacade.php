<?php
/**
 * This interface describes the basic functionality of the custom post type instance.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

namespace Milenia\Core\Support\Facades;

use Milenia\Core\Interfaces\RegistratorInterface;

class CustomPostTypeFacade implements RegistratorInterface
{
    /**
     * Registers the custom post type in the system.
     *
     * @param CustomPostTypeEntityInterface $CustomPostTypeEntity
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function register($CustomPostTypeEntity)
    {
        if(!post_type_exists($CustomPostTypeEntity->getName())) {
            register_post_type($CustomPostTypeEntity->getName(), $CustomPostTypeEntity->getAttributes());
        }

        return $CustomPostTypeEntity;
    }

    /**
     * Unregisters the custom post type in the system.
     *
     * @param CustomPostTypeEntityInterface $CustomPostTypeEntity
     * @access public
     * @return CustomPostTypeEntityInterface
     */
    public function unregister($CustomPostTypeEntity)
    {
        if(!post_type_exists($CustomPostTypeEntity->getName())) {
            unregister_post_type($CustomPostTypeEntity->getName());
        }

        return $CustomPostTypeEntity;
    }
}
?>
