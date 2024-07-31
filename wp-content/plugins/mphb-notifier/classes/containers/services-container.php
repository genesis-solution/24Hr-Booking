<?php

namespace MPHB\Notifier\Containers;

use MPHB\Notifier\Services;

/**
 * @since 1.0
 */
class ServicesContainer
{
    protected $sendNotifications = null;

    /**
     * @return \MPHB\Notifier\Services\SendNotifications
     */
    public function sendNotifications()
    {
        if (is_null($this->sendNotifications)) {
            $this->sendNotifications = new Services\SendNotifications();
        }

        return $this->sendNotifications;
    }
}
