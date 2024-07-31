<?php

namespace MPHB\Addons\Invoice\MetaBoxes;

use MPHB\Addons\Invoice\Utils\BookingUtils;
use MPHB\Admin\Fields\FieldFactory;
use MPHB\Utils\ValidateUtils;
use MPHB\Addons\Invoice\UsersAndRoles\Capabilities;

class InvoiceMetaBox extends CustomMetaBox
{
    /**
     * @param \MPHB\Admin\Groups\MetaBoxGroup[] $metaBoxes
     * @param string $postType
     * @return \MPHB\Admin\Groups\MetaBoxGroup[]
     */
    public function registerInMphb($metaBoxes, $postType)
    {
        if (current_user_can(Capabilities::GENERATE_INVOICES)) {

            if ($postType == $this->postType) {
                $booking = BookingUtils::getEditingBooking();
                if (!is_null($booking) && !$booking->isImported()) {
                    $metaBoxes[] = $this;
                }
            }
        }

        return $metaBoxes;
    
    }

    protected function registerFields()
    {

    }

    public function render()
    {
        $nonce = wp_create_nonce('mphb-invoice');
        parent::render();
        $booking = BookingUtils::getEditingBooking();
        if (is_null($booking) || $booking->isImported()) {
            return;
        }

        echo '<p class="mphb-invoice">';
            echo '<a target="_blank" href="'.admin_url( 'admin.php?post=' . $booking->getId() . '&action=mphb-invoice&_wpnonce='.$nonce ).'"  class="button button-primary button-large" >' . esc_attr__('Generate Invoice', 'mphb-invoices') . '</a>';
        echo '</p>';
    }

    public function save()
    {
        parent::save();
    }
}
