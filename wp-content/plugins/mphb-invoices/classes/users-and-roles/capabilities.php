<?php

/**
 * @since 1.1.2
 */

namespace MPHB\Addons\Invoice\UsersAndRoles;

class Capabilities
{
    const GENERATE_INVOICES = 'mphb_invoices_generate';

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

    public static function setup()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        $customRoles = mphbinvoice()->capabilities()->getRoles();

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

    public function mapCapabilitiesToRoles()
    {
        $this->capabilities[self::GENERATE_INVOICES] = array(
            'administrator'
        );

        if (
            class_exists('\MPHB\UsersAndRoles\Roles') &&
            defined('\MPHB\UsersAndRoles\Roles::MANAGER')
        ) {
            array_push($this->capabilities[self::GENERATE_INVOICES], \MPHB\UsersAndRoles\Roles::MANAGER);
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

    /**
     * 
     * @return array
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * 
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
