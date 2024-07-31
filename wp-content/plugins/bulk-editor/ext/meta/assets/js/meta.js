"use strict";

jQuery(function ($) {

    "use strict";

    jQuery('body').on('click', '.wpbe_meta_delete', function () {
        jQuery(this).parents('li').remove();
        return false;
    });

    //***

    jQuery('#metaform').on('submit', function () {
        wpbe_save_form(this, 'wpbe_save_meta');
        return false;
    });

    //***

    //***
    //action for bulk meta_popup_editor
    jQuery(document).on("wpbe_act_meta_popup_editor_saved", {}, function (event, post_id, field_name, value) {

        if (post_id === 0) {
            //looks like we want to apply it for bulk editing
            jQuery('#meta_popup_editor').hide();
            jQuery("[name='wpbe_bulk[" + field_name + "][value]']").val(value);
            jQuery("[name='wpbe_bulk[" + field_name + "][behavior]']").val('new');
        }

        return true;
    });

    //***

    jQuery('#wpbe_meta_add_new_btn').on('click', function () {
        var key = jQuery('.wpbe_meta_key_input').val();
        key = key.trim();
        key = key.replace(/ /g, '_');

        if (key.length > 0) {
            var html = jQuery('#wpbe_meta_li_tpl').html();
            html = html.replace(/__META_KEY__/gi, key);
            html = html.replace(/__TITLE__/gi, lang.meta.new_key);
            jQuery('#wpbe_meta_list').prepend(html);
            jQuery('.wpbe_meta_key_input').val('');
        } else {
            wpbe_message(lang.meta.enter_key, 'error');
        }

        return false;
    });

    jQuery('.wpbe_meta_key_input').on('keydown', function (e) {
        if (e.keyCode == 13) {
            jQuery('#wpbe_meta_add_new_btn').trigger('click');
        }
    });

    //***

    jQuery('#wpbe_meta_get_btn').on('click', function () {
        var id = parseInt(jQuery('.wpbe_meta_keys_get_input').val(), 10);

        if (id > 0) {

            jQuery('.wpbe_meta_keys_get_input').val('');

            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_meta_get_keys',
                    post_id: id
                },
                success: function (keys) {
                    if (keys.length > 0) {
                        keys = JSON.parse(keys);
                        var html = jQuery('#wpbe_meta_li_tpl').html();
                        for (var i = 0; i < keys.length; i++) {
                            var li = html.replace(/__META_KEY__/gi, keys[i]);
                            li = li.replace(/__TITLE__/gi, keys[i]);
                            jQuery('#wpbe_meta_list').prepend(li);
                        }
                    } else {
                        wpbe_message(lang.meta.no_keys_found, 'error');
                    }
                }
            });

        } else {
            wpbe_message(lang.meta.enter_prod_id, 'error');
        }

        return false;
    });

    jQuery('.wpbe_meta_keys_get_input').on('keydown', function (e) {
        if (e.keyCode == 13) {
            jQuery('#wpbe_meta_get_btn').trigger('click');
        }
    });

    //***

    jQuery('body').on('change', '.wpbe_meta_view_selector', function () {
        var value = jQuery(this).val();
        var type_selector = jQuery(this).parents('li').find('.wpbe_meta_type_selector');
        switch (value) {
            case 'popupeditor':
                jQuery(type_selector).val('string');
                jQuery(type_selector).parent().hide();
                break;

            case 'meta_popup_editor':
                jQuery(type_selector).val('string');
                jQuery(type_selector).parent().hide();
                break;

            case 'switcher':
                jQuery(type_selector).val('number');
                jQuery(type_selector).parent().hide();
                break;

            case 'calendar':
                jQuery(type_selector).val('number');
                jQuery(type_selector).parent().hide();
                break;

            case 'gallery_popup_editor':
                jQuery(type_selector).val('string');
                jQuery(type_selector).parent().hide();
                break;

            default:
                jQuery(type_selector).parent().show();
                break;
        }

        return true;
    });

});

//*********************

function wpbe_act_meta_popup_editor(_this) {
    wpbe_popup_clicked = jQuery(_this);
    var post_id = parseInt(jQuery(_this).data('post_id'), 10);
    var key = jQuery(_this).data('key');
    jQuery('#meta_popup_editor .wpbe-modal-title').html(jQuery(_this).data('name') + ' [' + key + ']');

    //***
    //console.log(jQuery(_this).find('.meta_popup_btn_data').html());
    var meta = JSON.parse(jQuery(_this).find('.meta_popup_btn_data').html());


    if (Object.keys(meta).length > 0 && post_id > 0) {
        var html = '';

        try {
            jQuery.each(meta, function (k, v) {
                var li_html = jQuery('#meta_popup_editor_li').html();
                li_html = li_html.replace(/__KEY__/gi, k);

                if (Array.isArray(v)) {
                    var ul = '<ul class="meta_popup_editor_child_ul">';

                    jQuery.each(v, function (kk, vv) {
                        var li_html2 = jQuery('#meta_popup_editor_li').html();
                        li_html2 = li_html2.replace(/__KEY__/gi, kk);

                        if (typeof vv === 'string') {
                            li_html2 = li_html2.replace(/__VALUE__/gi, vv);
                        } else {
                            li_html2 = li_html2.replace(/__VALUE__/gi, JSON.stringify(vv));
                        }

                        li_html2 = li_html2.replace(/__CHILD_LIST__/gi, '');
                        li_html2 = li_html2.replace('keys[]', 'keys[' + k + '][]');
                        li_html2 = li_html2.replace('values[]', 'values[' + k + '][]');

                        ul += li_html2;
                    });

                    ul += '</ul>';

                    li_html = li_html.replace(/__CHILD_LIST__/gi, ul + '&nbsp;<a href="#" class="meta_popup_editor_add_sub_item" data-key="' + k + '">' + lang.append_sub_item + '</a><br />');
                    li_html = li_html.replace(/__VALUE__/gi, 'delete-this');
                } else if (jQuery.isPlainObject(v)) {
                    var ul = '<ul class="meta_popup_editor_child_ul">';
                    jQuery.each(v, function (kk, vv) {
                        var li_html_obj = jQuery('#meta_popup_editor_li_object').html();
                        li_html_obj = li_html_obj.replace(/__KEY__/gi, kk);

                        if (typeof vv === 'string') {
                            li_html_obj = li_html_obj.replace(/__VALUE__/gi, vv);
                        } else {
                            li_html_obj = li_html_obj.replace(/__VALUE__/gi, JSON.stringify(vv));
                        }

                        li_html_obj = li_html_obj.replace('keys2[]', 'keys2[' + k + '][]');
                        li_html_obj = li_html_obj.replace('values2[]', 'values2[' + k + '][]');

                        ul += li_html_obj;
                    });
                    ul += '</ul>';

                    li_html = li_html.replace(/__CHILD_LIST__/gi, ul + '&nbsp;<a href="#" class="meta_popup_editor_add_sub_item2" data-key="' + k + '">' + lang.append_sub_item + '</a><br />');
                    li_html = li_html.replace(/__VALUE__/gi, 'delete-this');

                } else {
                    li_html = li_html.replace(/__VALUE__/gi, v.replace(/"/g, '&quot;').replace(/'/g, '&apos;'));
                    li_html = li_html.replace(/__CHILD_LIST__/gi, '<ul class="meta_popup_editor_child_ul" data-key="' + k + '"></ul>&nbsp;<a href="#" class="meta_popup_editor_add_sub_item" data-key="' + k + '">' + lang.append_sub_item + '</a><br />');
                }

                html += li_html;
            });
        } catch (e) {
            //console.log(e);
        }

        jQuery('#meta_popup_editor form').html('<ul class="wpbe_fields_tmp">' + html + '</ul>');
        jQuery('#meta_popup_editor form').find("input[value='delete-this']").remove();

        jQuery('.meta_popup_editor_li_key2').parents('ul.wpbe_fields_tmp').find('.meta_popup_editor_li_key:not(.meta_popup_editor_li_key2)').attr('readonly', true);


        jQuery('#meta_popup_editor').show();
        __wpbe_init_meta_popup_editor();

    } else {

        if (post_id > 0) {
            jQuery('#meta_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
        } else {
            //this we need do for another applications, for example bulk editor
            if (jQuery('#meta_popup_editor form .wpbe_fields_tmp').length == 0) {
                jQuery('#meta_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
            }
        }

        jQuery('#meta_popup_editor').show();
        __wpbe_init_meta_popup_editor();
    }

    //***


    jQuery('.wpbe-modal-save10').off('click');
    jQuery('.wpbe-modal-save10').on('click', function () {

        var post_id = wpbe_popup_clicked.data('post_id');
        var key = wpbe_popup_clicked.data('key');

        if (post_id > 0) {
            jQuery('#meta_popup_editor').hide();
            wpbe_message(lang.saving, 'warning');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
                    post_id: post_id,
                    field: key,
                    value: jQuery('#meta_popup_editor_form').serialize(),
                    is_serialized: 1
                },
                success: function (answer) {
                    jQuery(_this).find('.meta_popup_btn_data').html(answer);
                    wpbe_message(lang.saved, 'notice');
                    jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, jQuery('#meta_popup_editor_form').serialize()]);
                }
            });
        } else {
            //for buttons in any extensions
            jQuery(document).trigger('wpbe_act_meta_popup_editor_saved', [post_id, key, jQuery('#meta_popup_editor_form').serialize()]);
        }

        return false;
    });

    jQuery('.wpbe-modal-close10').off('click');
    jQuery('.wpbe-modal-close10').on('click', function () {
        //jQuery('#meta_popup_editor_editor form').html(''); - do not do this, as it make incompatibility with another extensions
        jQuery('#meta_popup_editor').hide();
        return false;
    });

}

function __wpbe_init_meta_popup_editor() {

    jQuery("#meta_popup_editor form .wpbe_fields_tmp, #meta_popup_editor form .meta_popup_editor_child_ul").sortable({
        update: function (event, ui) {
            //***
        },
        opacity: 0.8,
        cursor: "crosshair",
        handle: '.wpbe_drag_and_drope',
        placeholder: 'wpbe-options-highlight'
    });

    //***

    jQuery('.meta_popup_editor_insert_new').off('click');
    jQuery('.meta_popup_editor_insert_new').on('click', function () {
        var li_html = jQuery('#meta_popup_editor_li').html();

        li_html = li_html.replace(/__KEY__/gi, '');
        li_html = li_html.replace(/__VALUE__/gi, '');
        li_html = li_html.replace(/__CHILD_LIST__/gi, '<ul class="meta_popup_editor_child_ul"></ul>&nbsp;<a href="#" class="meta_popup_editor_add_sub_item" data-key="">' + lang.append_sub_item + '</a><br />');

        if (jQuery(this).data('place') == 'top') {
            jQuery('#meta_popup_editor form .wpbe_fields_tmp').prepend(li_html);
        } else {
            jQuery('#meta_popup_editor form .wpbe_fields_tmp').append(li_html);
        }
        __wpbe_init_meta_popup_editor();

        return false;
    });

    //***

    jQuery('.meta_popup_editor_insert_new_o').off('click');
    jQuery('.meta_popup_editor_insert_new_o').on('click', function () {
        var li_html = jQuery('#meta_popup_editor_li_o').html();
        var li_sub = jQuery('#meta_popup_editor_li_object').html();

        li_html = li_html.replace(/__KEY__/gi, '');
        li_html = li_html.replace(/__VALUE__/gi, '');

        li_sub = li_sub.replace(/__KEY__/gi, '');
        li_sub = li_sub.replace(/__VALUE__/gi, '');

        li_html = li_html.replace(/__CHILD_LIST__/gi, '<ul class="meta_popup_editor_child_ul">' + li_sub + '</ul>&nbsp;<a href="#" class="meta_popup_editor_add_sub_item2" data-key="">' + lang.append_sub_item + '</a><br />');

        if (jQuery(this).data('place') == 'top') {
            jQuery('#meta_popup_editor form .wpbe_fields_tmp').prepend(li_html);
        } else {
            jQuery('#meta_popup_editor form .wpbe_fields_tmp').append(li_html);
        }
        __wpbe_init_meta_popup_editor();

        return false;
    });

    //***

    jQuery('.meta_popup_editor_add_sub_item, .meta_popup_editor_add_sub_item2').off('click');
    jQuery('.meta_popup_editor_add_sub_item, .meta_popup_editor_add_sub_item2').on('click', function () {

        if (jQuery(this).hasClass('meta_popup_editor_add_sub_item')) {
            var li_html = jQuery('#meta_popup_editor_li').html();
        } else {
            //meta_popup_editor_add_sub_item2
            var li_html = jQuery('#meta_popup_editor_li_object').html();
        }

        //***

        li_html = li_html.replace(/__KEY__/gi, '');
        li_html = li_html.replace(/__VALUE__/gi, '');
        li_html = li_html.replace(/__CHILD_LIST__/gi, '');

        var key = jQuery(this).data('key');
        if (key.length === 0) {
            key = jQuery(this).prev('.meta_popup_editor_child_ul').data('key');
        }

        if (typeof key == 'undefined') {
            key = jQuery(this).parent().find('.meta_popup_editor_li_key').eq(0).val();
        }

        li_html = li_html.replace('keys[]', 'keys[' + key + '][]');
        li_html = li_html.replace('values[]', 'values[' + key + '][]');

        li_html = li_html.replace('keys2[]', 'keys2[' + key + '][]');
        li_html = li_html.replace('values2[]', 'values2[' + key + '][]');

        //remove value textinput of the parent
        jQuery(this).parent().find("[name='values[]']").remove();

        if (jQuery(this).data('place') == 'top') {
            jQuery(this).prev('.meta_popup_editor_child_ul').prepend(li_html);
        } else {
            jQuery(this).prev('.meta_popup_editor_child_ul').append(li_html);
        }
        __wpbe_init_meta_popup_editor();

        return false;
    });

    //***

    jQuery('.meta_popup_editor_li_key').off('click');
    jQuery('.meta_popup_editor_li_key').on('keyup', function () {

        jQuery(this).parent().find('.meta_popup_editor_child_ul .meta_popup_editor_li_key').attr('name', 'keys[' + jQuery(this).val() + '][]');
        jQuery(this).parent().find('.meta_popup_editor_child_ul .meta_popup_editor_li_value').attr('name', 'values[' + jQuery(this).val() + '][]');

        jQuery(this).parent().find('.meta_popup_editor_child_ul .meta_popup_editor_li_key2').attr('name', 'keys2[' + jQuery(this).val() + '][]');
        jQuery(this).parent().find('.meta_popup_editor_child_ul .meta_popup_editor_li_value2').attr('name', 'values2[' + jQuery(this).val() + '][]');


        return true;
    });

    //***

    jQuery('.wpbe_prod_delete').off('click');
    jQuery('.wpbe_prod_delete').on('click', function () {
        jQuery(this).parent('li').remove();
        return false;
    });
}

