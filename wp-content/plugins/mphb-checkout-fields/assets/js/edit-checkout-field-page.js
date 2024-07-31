jQuery(function () {
    'use strict';

    var $typeField = jQuery('select[name="mphb_cf_type"]').first();

    var $innerLabelRow  = jQuery('.mphb-inner-label-ctrl').parents('tr').first();
    var $textContentRow = jQuery('.mphb-text-content-ctrl').parents('tr').first();
    var $placeholderRow = jQuery('.mphb-placeholder-ctrl').parents('tr').first();
    var $patternRow     = jQuery('.mphb-pattern-ctrl').parents('tr').first();
    var $descriptionRow = jQuery('.mphb-description-ctrl').parents('tr').first();
    var $optionsRow     = jQuery('.mphb-options-ctrl').parents('tr').first();
    var $checkedRow     = jQuery('.mphb-checked-ctrl').parents('tr').first();
    var $fileTypesRow   = jQuery('.mphb-file-types-ctrl').parents('tr').first();
    var $uploadSizeRow   = jQuery('.mphb-upload-size-ctrl').parents('tr').first();
    var $uploadFileRow  = jQuery('.mphb-protected-upload-ctrl').parents('tr').first();
    var $requiredRow  = jQuery('.mphb-required-ctrl').parents('tr').first();

    if ($typeField.length == 1) {
        $typeField.on('change', onTypeChange);
    }

    // Change the initial state
    onTypeChange();

    function onTypeChange()
    {
        var selectedType = $typeField.val() || MPHBCheckoutField.type;

        $innerLabelRow.toggleClass('mphb-hide', selectedType != 'checkbox');
        $textContentRow.toggleClass('mphb-hide', selectedType != 'paragraph');
        $placeholderRow.toggleClass('mphb-hide', ['email', 'phone', 'text', 'textarea'].indexOf(selectedType) == -1);
        $patternRow.toggleClass('mphb-hide', ['phone', 'text'].indexOf(selectedType) == -1);
        $descriptionRow.toggleClass('mphb-hide', ['heading', 'paragraph'].indexOf(selectedType) != -1);
        $optionsRow.toggleClass('mphb-hide', selectedType != 'select');
        $checkedRow.toggleClass('mphb-hide', selectedType != 'checkbox');
        $uploadFileRow.toggleClass('mphb-hide', selectedType != 'file_upload');
        $fileTypesRow.toggleClass('mphb-hide', selectedType != 'file_upload');
        $uploadSizeRow.toggleClass('mphb-hide', selectedType != 'file_upload');
        $requiredRow.toggleClass('mphb-hide', ['heading', 'paragraph'].indexOf(selectedType) != -1);
    }
});
