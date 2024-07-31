<?php

namespace MPHB\Notifier\Containers;

use MPHB\Notifier\PostTypes;

/**
 * @since 1.0
 */
class CptContainer
{
    protected $notificationCpt = null;

    /**
     * @return \MPHB\Notifier\PostTypes\NotificationCPT
     */
    public function notification()
    {
        if (is_null($this->notificationCpt)) {
            $this->notificationCpt = new PostTypes\NotificationCPT();
        }

        return $this->notificationCpt;
    }
}
