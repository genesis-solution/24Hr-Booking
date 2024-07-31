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

interface RegistratorInterface
{

    /**
     * Registers the entity in the system.
     *
     * @param mixed $entity
     * @access public
     * @return mixed
     */
    public function register($entity);

    /**
     * Unregisters the entity in the system.
     *
     * @param mixed $entity
     * @access public
     * @return mixed
     */
    public function unregister($entity);
}
?>
