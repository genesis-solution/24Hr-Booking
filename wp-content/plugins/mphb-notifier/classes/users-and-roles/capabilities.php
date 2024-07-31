<?php

/**
 * @since 1.0.4
 */

namespace MPHB\Notifier\UsersAndRoles;
class Capabilities
{
    const POST_TYPES = 'mphb_notifications';

    /**
     * @var string
     */
    public $capabilities;

    /**
     * @var string
     */
    public $roles;

    public function __construct()
    {
        $this->mapCapabilitiesToRoles();
        $this->mapRolesToCapabilities();
    }

    public function mapCapabilitiesToRoles()
    {
        $plural = self::POST_TYPES;

        $caps = array(
            "edit_{$plural}",
            "edit_private_{$plural}",
            "edit_others_{$plural}",
            "edit_published_{$plural}",
            "delete_{$plural}",
            "delete_private_{$plural}",
            "delete_others_{$plural}",
            "delete_published_{$plural}",
            "read_{$plural}",
            "read_private_{$plural}",
            "publish_{$plural}"
        );

        foreach ($caps as $cap) {
            if (!isset($this->capabilities[$cap])) {
                $this->capabilities[$cap] = array();
            }
            array_push($this->capabilities[$cap], 'administrator');

            if (
                class_exists('\MPHB\UsersAndRoles\Roles') &&
                defined('\MPHB\UsersAndRoles\Roles::MANAGER')
            ) {
                array_push($this->capabilities[$cap], \MPHB\UsersAndRoles\Roles::MANAGER);
            }
        }
    }

    public function mapRolesToCapabilities()
    {
        if (!empty($this->capabilities)) {
            foreach ($this->capabilities as $capability => $roles) {
                array_map(function ($role) use ($capability) {
                    if (!isset($this->roles[$role])) {
                        $this->roles[$role] = array();
                    }
                    if (!in_array($capability, $this->roles[$role])) {
                        array_push($this->roles[$role], $capability);
                    }
                }, $roles);
            }
        }
    }

    public static function setup()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        $customRoles = mphb_notifier()->getCapabilities()->getRoles();

        if (!empty($customRoles)) {
            foreach ($customRoles as $role => $capabilities) {
                if (!empty($capabilities)) {
                    foreach ($capabilities as $cap) {
                        $wp_roles->add_cap($role, $cap);
                    }
                }
            }
        }
    }

    /**
     * 
     * @return array
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function getRoles()
    {
        return $this->roles;
    }
}
