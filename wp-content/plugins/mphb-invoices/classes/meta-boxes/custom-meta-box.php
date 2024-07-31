<?php

namespace MPHB\Addons\Invoice\MetaBoxes;

use MPHB\Admin\Groups\MetaBoxGroup;

class CustomMetaBox extends MetaBoxGroup
{
    /**
     * @param string $name
     * @param string $label
     * @param string $postType
     * @param string $context Optional. The context within the screen where the
     *                        boxes should display. "normal", "side" or
     *                        "advanced"). "advanced" by default.
     * @param string $priority Optional. The priority within the context where
     *                         the boxes should show. "high", "default" or
     *                         "low". "default" by default.
     */
    public function __construct($name, $label, $postType, $context = 'advanced', $priority = 'default')
    {
        parent::__construct($name, $label, $postType, $context, $priority);

        $this->addActions();
        $this->registerFields();
    }

    protected function addActions()
    {
        // Register current instance of meta box in Hotel Booking - the plugin
        // will call the register() and save() methods
        add_action('mphb_edit_page_field_groups', array($this, 'registerInMphb'), 10, 2);
    }

    /**
     * @param \MPHB\Admin\Groups\MetaBoxGroup[] $metaBoxes
     * @param string $postType
     * @return \MPHB\Admin\Groups\MetaBoxGroup[]
     */
    public function registerInMphb($metaBoxes, $postType)
    {
        if ($postType == $this->postType) {
            $metaBoxes[] = $this;
        }

        return $metaBoxes;
    }

    protected function registerFields() {}
}
