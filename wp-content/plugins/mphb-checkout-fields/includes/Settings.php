<?php

namespace MPHB\CheckoutFields;

/**
 * @since 1.0
 */
class Settings
{
    /**
     * @return bool
     */
    public function isActivated()
    {
        return (bool)get_option('mphb_checkout_fields_activated', false);
    }

    /**
     * @param bool $activated
     */
    public function setActivated($activated = true)
    {
        update_option('mphb_checkout_fields_activated', $activated, false);
    }
}
