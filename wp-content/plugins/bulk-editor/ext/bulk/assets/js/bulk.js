"use strict";

var wpbe_current_bulk_key = '';
var wpbe_current_bulk_field_keys = [];
var wpbe_bulk_chosen_inited = false;//just fix to init chosen
var wpbe_bulk_xhr = null;//current ajax request (for cancel)
var wpbe_bulk_user_cancel = false;//current ajax request (for cancel)
var wpbe_bind_editing = 0;

//***

jQuery(function ($) {

    "use strict";

    //init chosen by first click because chosen init doesn work for hidden containers
    jQuery(document).on("do_tabs-bulk", {}, function () {
        //if (!wpbe_bulk_chosen_inited) {
        setTimeout(function () {
            //set chosen
            jQuery('#tabs-bulk .chosen-select').chosen('destroy');
            jQuery('#tabs-bulk .chosen-select').chosen();
            wpbe_bulk_chosen_inited = true;
        }, 150);
        //}

        return true;
    });

    //***
    //meta finder
    jQuery('#wpbe_meta_finder').on('keyup keypress', function (e) {

        var keyCode = e.keyCode || e.which;
        //preventing form submit if press Enter button
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }

        //***

        var search = jQuery(this).val().toLowerCase();

        if (search.length > 0) {
            jQuery('#wpbe_meta_list li').hide();
        } else {
            jQuery('#wpbe_meta_list li').show();
        }


        jQuery('#wpbe_meta_list li.wpbe_options_li .wpbe_column_li_option1, #wpbe_meta_list li.wpbe_options_li .wpbe_column_li_option2').each(function (index, input) {
            if (jQuery(input).val().toLowerCase().indexOf(search) != -1) {
                jQuery(input).parents('li').show();
            }
        });

        return true;
    });

    jQuery('#js_check_wpbe_bind_editing').on('check_changed', function (event) {
        wpbe_bind_editing = parseInt(jQuery(this).val(), 10);
        return true;
    });

    //***
    //we need to synhronize selection for calculator and bul edit form
    jQuery('.wpbe_num_rounding').on('change',function () {
        jQuery('.wpbe_num_rounding').val(jQuery(this).val());
        return true;
    });

    //***

    jQuery(document).on("wpbe_page_field_updated", {}, function (event, post_id, field_name, value, operation) {
        if (wpbe_bind_editing > 0) {

            if ((wpbe_checked_posts.length - 1) > 0 && post_id > 0 && field_name != 0 && (typeof value != 'undefined')) {

                var behavior = 'new';
                if (typeof operation != 'undefined') {
                    behavior = operation;
                }

                //console.log(post_id);
                //console.log(field_name);
                //console.log(value);
                //console.log(operation);

                //***

                try {
                    if (!wpbe_active_fields[field_name]['direct']) {
                        alert(lang.is_deactivated_in_free);
                        return false;
                    }
                } catch (e) {
                    console.log(e);
                }


                //***

                wpbe_set_progress('wpbe_bulk_progress', 0);
                wpbe_message(lang.bulk.bulking, 'warning', 999999);
                wpbe_current_bulk_key = wpbe_get_random_string(16);
                jQuery('.wpbe_bulk_terminate').show();
                wpbe_bulk_is_going();
                wpbe_disable_bind_editing();
                //***
		
                wpbe_bulk_xhr = jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_posts_count',
                        bulk_data: jQuery('#wpbe_bulk_form').serialize(),
                        no_filter: 1,
                        bulk_key: wpbe_current_bulk_key,
                        posts_count: wpbe_checked_posts.length - 1,
                        wpbe_bind_editing: 1,
                        field: field_name,
                        val: value,
			wpbe_nonce: wpbe_field_update_nonce,
                        behavior: behavior
                    },
                    success: function () {
                        var arrayWithout = wpbe_checked_posts.filter(function (value) {
                            return value != post_id;
                        });

                        __wpbe_bulk_posts(arrayWithout, 0, wpbe_current_bulk_key, field_name);
                        // wpbe_disable_bind_editing();
                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });



            }
        }

        //***

        __trigger_resize();

        return true;
    });

    //***

    wpbe_init_bulk_panel();

    //placeholder label
    jQuery('#wpbe_bulk_form input[placeholder]:not(.wpbe_calendar)').placeholderLabel();

    //***

    jQuery('.wpbe_bulk_terminate').on('click', function () {
        wpbe_bulk_terminate();
        return false;
    });

    //***

    //***

    jQuery(document).on("taxonomy_data_redrawn", {}, function (event, tax_key, term_id) {

        var select_id = 'wpbe_bulk_taxonomies_' + tax_key;
        var select = jQuery('#' + select_id);
        jQuery(select).empty();
        __wpbe_fill_select(select_id, taxonomies_terms[tax_key]);
        jQuery(jQuery('#' + select_id)).chosen({
            width: '100%'
        }).trigger("chosen:updated");

        return true;
    });

    //***
    //action for bulk gallery images
    jQuery(document).on("wpbe_act_gallery_editor_saved", {}, function (event, post_id, field_name, value) {


        if (post_id === 0) {
            //looks like we want to apply it for bulk editing

            jQuery('#gallery_popup_editor').hide();
            jQuery("[name='wpbe_bulk[gallery][value]']").val(value);
            jQuery("[name='wpbe_bulk[gallery][behavior]']").val(jQuery('#wpbe_gall_operations').val());

            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_bulk_draw_gallery_btn',
                    post_id: 0,
                    field: field_name,
                    images: value,
		    wpbe_nonce: wpbe_field_update_nonce,
                },
                success: function (response) {
                    response = JSON.parse(response);
                    jQuery('#popup_val_gallery_0').parent().html(response.html);
                }
            });
        }



        return true;
    });

    //***
    //action for bulk upsells
    jQuery(document).on("wpbe_act_upsells_editor_saved", {}, function (event, post_id, field_name, value) {

        if (post_id === 0) {
            //looks like we want to apply it for bulk editing

            jQuery('#upsells_popup_editor').hide();
            jQuery("[name='wpbe_bulk[upsell_ids][value]']").val(value);
            jQuery("[name='wpbe_bulk[upsell_ids][behavior]']").val(jQuery('#wpbe_upsells_operations').val());

            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_bulk_draw_upsell_ids_btn',
                    post_id: 0,
                    field: field_name,
                    posts: value,
		    wpbe_nonce: wpbe_field_update_nonce,
                },
                success: function (html) {
                    jQuery('#upsell_ids_upsell_ids_0').parent().html(html);
                }
            });
        }


        return true;
    });

    //***

    wpbe_bulk_init_additional();
});

//***

function wpbe_init_bulk_panel() {

    jQuery('.bulk_checker').on('click', function () {
        var disable = false;
        if (jQuery('.bulk_checker:checked').length > 0) {
            jQuery('#wpbe_bulk_posts_btn').show();
        } else {
            jQuery('#wpbe_bulk_posts_btn').hide();
            disable = true;
        }

        jQuery(this).parents('.filter-unit-wrap').find('input[type=text],input[type=number]').prop("disabled", disable);
        jQuery(this).parents('.filter-unit-wrap').find('select').prop("disabled", disable).trigger("chosen:updated");

        if (!disable) {
            jQuery(this).parents('.filter-unit-wrap').find('label').css('color', 'rgb(1, 1, 1) !important');
        } else {
            jQuery(this).parents('.filter-unit-wrap').find('label').css('color', 'rgb(170, 170, 170)');
        }
    });

    //***

    jQuery('.wpbe_bulk_add_special_key').on('change',function () {
        var input = jQuery(this).parents('.filter-unit-wrap').eq(0).find('.wpbe_bulk_value').eq(0);
        var caretPos = input[0].selectionStart;
        var textAreaTxt = input.val();
        jQuery(input).focus();//to up its placeholder
        jQuery(input).trigger('click');//to up its placeholder
        input.val(textAreaTxt.substring(0, caretPos) + jQuery(this).val() + textAreaTxt.substring(caretPos));

        //jQuery(input).selectionStart = caretPos +  jQuery(this).val().length;
        jQuery(this).val(-1);
    });

    //***

    jQuery('.wpbe_bulk_value_signs').on('change',function () {
        var key = jQuery(this).data('key');

        if (jQuery(this).val() === 'replace') {
            jQuery('.wpbe_bulk_replace_to_' + key).show();
        } else {
            jQuery('.wpbe_bulk_replace_to_' + key).hide();
        }

    });

    //***

    jQuery('#wpbe_bulk_posts_btn').on('click', function () {

        var bulk_txt = lang.bulk.want_to_bulk + '\n';
        wpbe_current_bulk_field_keys = [];
        jQuery('.bulk_checker').each(function (index, ch) {
            if (jQuery(ch).is(':checked')) {
                bulk_txt += jQuery(ch).data('title') + '\n';
                wpbe_current_bulk_field_keys.push(jQuery(ch).data('field-key'));
            }
        });

	if ( typeof wpbe_checked_posts != 'undefined' && wpbe_checked_posts.length ){
	    bulk_txt += '\n' + lang.checked_post + ": " + wpbe_checked_posts.length;
	}
        //***

        if (confirm(bulk_txt)) {
            jQuery('#wpbe_bulk_posts_btn').hide();
            wpbe_set_progress('wpbe_bulk_progress', 0);
            wpbe_message(lang.bulk.bulking, 'warning', 999999);
            wpbe_current_bulk_key = wpbe_get_random_string(16);
            jQuery('.wpbe_bulk_terminate').show();
            wpbe_bulk_is_going();

            if (wpbe_checked_posts.length > 0) {
                wpbe_bulk_xhr = jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_posts_count',
                        bulk_data: jQuery('#wpbe_bulk_form').serialize(),
                        no_filter: 1,
                        bulk_key: wpbe_current_bulk_key,
                        posts_count: wpbe_checked_posts.length,
			wpbe_nonce: wpbe_field_update_nonce,
                    },
                    success: function () {
                        __wpbe_bulk_posts(wpbe_checked_posts, 0, wpbe_current_bulk_key);
                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });
            } else {
                wpbe_bulk_xhr = jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_posts_count',
                        bulk_data: jQuery('#wpbe_bulk_form').serialize(),
                        bulk_key: wpbe_current_bulk_key,
			wpbe_nonce: wpbe_field_update_nonce,
                        filter_current_key: wpbe_filter_current_key//!!! IMPORTANT !!!
                    },
                    success: function (posts_ids) {
                        posts_ids = JSON.parse(posts_ids);

                        if (posts_ids.length) {
                            jQuery('#wpbe_bulk_progress').show();
                            __wpbe_bulk_posts(posts_ids, 0, wpbe_current_bulk_key);
                        }

                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });
            }
        }

        return false;
    });



    jQuery('#wpbe_bulk_delete_posts_btn_fuse').on('click', function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#wpbe_bulk_delete_posts_btn').removeAttr("disabled");
        } else {
            jQuery('#wpbe_bulk_delete_posts_btn').attr("disabled", "disabled");
        }
    });

    //DELETE button!!!
    jQuery('#wpbe_bulk_delete_posts_btn').on('click', function () {
        if (!jQuery('#wpbe_bulk_delete_posts_btn_fuse').is(':checked')) {
            return false;
        }

        var delete_txt = lang.bulk.want_to_delete + '\n';
        wpbe_current_bulk_field_keys = [];
        if (confirm(delete_txt)) {
            jQuery('#wpbe_bulk_delete_posts_btn').hide();
            wpbe_set_progress('wpbe_bulk_progress', 0);
            wpbe_message(lang.bulk.deleting, 'warning', 999999);
            wpbe_current_bulk_key = wpbe_get_random_string(16);
            jQuery('.wpbe_bulk_terminate').show();
            wpbe_bulk_is_going();

            if (wpbe_checked_posts.length > 0) {
                wpbe_bulk_xhr = jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_delete_posts_count',
                        bulk_data: jQuery('#wpbe_bulk_form').serialize(),
                        no_filter: 1,
                        bulk_key: wpbe_current_bulk_key,
                        posts_count: wpbe_checked_posts.length,
			wpbe_nonce: wpbe_field_update_nonce
                    },
                    success: function () {
                        __wpbe_bulk_delete_posts(wpbe_checked_posts, 0, wpbe_current_bulk_key);
                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });
            } else {
                wpbe_bulk_xhr = jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_delete_posts_count',
                        bulk_data: jQuery('#wpbe_bulk_form').serialize(),
                        bulk_key: wpbe_current_bulk_key,
                        filter_current_key: wpbe_filter_current_key//!!! IMPORTANT !!!
                    },
                    success: function (posts_ids) {
                        posts_ids = JSON.parse(posts_ids);

                        if (posts_ids.length) {
                            jQuery('#wpbe_bulk_progress').show();
                            __wpbe_bulk_delete_posts(posts_ids, 0, wpbe_current_bulk_key);
                        }

                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });
            }
        }
        return false;
    });



    //END DELETE button!!!
    //***
    //variation targeting
    jQuery('#wpbe_bulk_add_combination_to_apply').on('click', function () {

        var select = jQuery('#wpbe_bulk_combination_attributes');

        if (jQuery(select).val()) {

            wpbe_message(lang.loading, 'warning');

            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_bulk_get_att_terms',
                    attributes: jQuery(select).val(),
                    hash_key: wpbe_get_random_string(8).toLowerCase()
                },
                success: function (html) {
                    wpbe_message(lang.loaded, 'notice');

                    jQuery('#wpbe_bulk_to_var_combinations_apply').append('<li>' + html + '&nbsp;<a href="javascript: void(0);" class="wpbe_bulk_get_att_terms_del button">x</a></li>');

                    jQuery('.wpbe_bulk_get_att_terms_del').off('click');
                    jQuery('.wpbe_bulk_get_att_terms_del').on('click', function () {
                        jQuery(this).parent().remove();
                        return false;
                    });
                }
            });
        }


        return false;
    });
}

function __wpbe_bulk_delete_posts(posts, start, bulk_key, field_key) {
    var step = 10;

    var posts_ids = posts.slice(start, start + step);

    wpbe_bulk_xhr = jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_bulk_delete_posts',
            posts_ids: posts_ids,
            wpbe_show_variations: wpbe_show_variations,
            bulk_key: wpbe_current_bulk_key,
	    wpbe_nonce: wpbe_field_update_nonce,
        },
        success: function (data) {
            console.log(data);
            if ((start + step) > posts.length) {
                var update_data_table = true;
                //***
                var posts_count_bulked = 10;
                if (update_data_table) {
                    wpbe_message(lang.bulk.bulked, 'notice', 30000);
                    posts_count_bulked = parseInt(posts_count_bulked, 10);

                    if (posts_count_bulked > 4) {
                        //https://datatables.net/reference/api/draw()
                        data_table.draw('page');
                    } else {
                        //updated <= 4 rows, lets redraw only them
                        for (var i = 0; i < posts.length; i++) {
                            wpbe_redraw_table_row(jQuery('#post_row_' + posts[i]), false);
                        }
                    }
                } else {
                    wpbe_message(lang.bulk.deleted, 'notice');
                }


                //***
                setTimeout(function () {
                    //if after bulk edit of filtrated posts they will get out from filtration                        
                    if (wpbe_checked_posts.length > 0) {
                        var to_delete = [];
                        for (var i = 0; i < wpbe_checked_posts.length; i++) {
                            if (!jQuery('#post_row_' + wpbe_checked_posts[i]).length) {
                                //console.log(wpbe_checked_posts[i]);
                                to_delete.push(wpbe_checked_posts[i]);
                            }
                        }

                        //+++

                        for (var i = 0; i < to_delete.length; i++) {
                            wpbe_checked_posts.splice(wpbe_checked_posts.indexOf(to_delete[i]), 1);
                        }
                        //console.log(wpbe_checked_posts);

                        __wpbe_action_will_be_applied_to();
                    }

                }, 2000);


                //***

                jQuery('#wpbe_bulk_delete_posts_btn').show();
                jQuery('.wpbe_bulk_terminate').hide();
                wpbe_set_progress('wpbe_bulk_progress', 100);
                jQuery(document).trigger('wpbe_bulk_completed');
                wpbe_bulk_is_going(false);
                jQuery('.wpbe_num_rounding').val(0);

            } else {
                //show %
                var percents = (start + step) * 100 / posts.length;
                wpbe_set_progress('wpbe_bulk_progress', percents);
                wpbe_bulk_is_going_txt(percents.toFixed(2));
                __wpbe_bulk_delete_posts(posts, start + step, bulk_key, field_key);
            }
        },
        error: function () {
            if (!wpbe_bulk_user_cancel) {
                alert(lang.error);
                wpbe_bulk_terminate();
            }
            wpbe_bulk_is_going(false);
        }
    });
}


function __wpbe_bulk_posts(posts, start, bulk_key, field_key) {
    //var step = 10;
    var step = 50;
    var posts_ids = posts.slice(start, start + step);
    
    var rand_data = {
	action: jQuery('#wpbe_random_action').eq(0).val(),
	decimal: jQuery('#wpbe_random_decimal').eq(0).val(),
	from: jQuery('#wpbe_random_from').eq(0).val(),
	to: jQuery('#wpbe_random_to').eq(0).val()
    };

    wpbe_bulk_xhr = jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_bulk_posts',
            posts_ids: posts_ids,
            bulk_key: bulk_key,
            //filter_current_key: wpbe_filter_current_key, - do not need here as we use posts_ids
            wpbe_show_variations: wpbe_show_variations,
            num_rounding: jQuery('.wpbe_num_rounding').eq(0).val(),
	    num_formula_action: jQuery('.wpbe_formula_action').eq(0).val(),
	    num_formula_value: jQuery('.wpbe_formula_value').eq(0).val(),
	    num_rand_data: rand_data,
	    wpbe_nonce: wpbe_field_update_nonce,
        },
        success: function (e) {
            //console.log(e);
            if ((start + step) > posts.length) {
                jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_bulk_finish',
                        bulk_key: wpbe_current_bulk_key,
                        filter_current_key: wpbe_filter_current_key,
			wpbe_nonce: wpbe_field_update_nonce,
                    },
                    success: function (posts_count_bulked) {

                        var update_data_table = true;

                        if (typeof field_key !== 'undefined' && wpbe_active_fields[field_key] !== 'undefined') {
                            if (wpbe_active_fields[field_key].edit_view === 'popupeditor') {
                                //There is no sense in redrawing buttons with text if the value was updated only there in bind mode
                               // update_data_table = false;
                            }
                        } else {
                            if (wpbe_current_bulk_field_keys.length > 0) {
                                update_data_table = false;
                                //There is no sense in redrawing buttons with text if the value was updated only there in bulk mode
                                for (var i = 0; i < wpbe_current_bulk_field_keys.length; i++) {
                                    if (typeof wpbe_active_fields[wpbe_current_bulk_field_keys[i]] != 'undefined') {

                                        //if (wpbe_active_fields[wpbe_current_bulk_field_keys[i]].edit_view != 'popupeditor') {
                                            update_data_table = true;
                                            break;
                                       // }

                                    } else {
                                        update_data_table = true;
                                        break;
                                    }
                                }
                            }
                        }

                        //***

                        if (update_data_table) {
                            wpbe_message(lang.bulk.bulked, 'notice', 30000);
                            posts_count_bulked = parseInt(posts_count_bulked, 10);

                            if (posts_count_bulked > 4) {
                                //https://datatables.net/reference/api/draw()
                                data_table.draw('page');
                            } else {
                                //updated <= 4 rows, lets redraw only them
                                for (var i = 0; i < posts.length; i++) {
                                    wpbe_redraw_table_row(jQuery('#post_row_' + posts[i]), false);
                                }
                            }
                        } else {
                            wpbe_message(lang.bulk.bulked2, 'notice');
                        }


                        //***
                        setTimeout(function () {
                            //if after bulk edit of filtrated posts they will get out from filtration                        
                            if (wpbe_checked_posts.length > 0) {
                                var to_delete = [];
                                for (var i = 0; i < wpbe_checked_posts.length; i++) {
                                    if (!jQuery('#post_row_' + wpbe_checked_posts[i]).length) {
                                        //console.log(wpbe_checked_posts[i]);
                                        to_delete.push(wpbe_checked_posts[i]);
                                    }
                                }

                                //+++

                                for (var i = 0; i < to_delete.length; i++) {
                                    wpbe_checked_posts.splice(wpbe_checked_posts.indexOf(to_delete[i]), 1);
                                }
                                //console.log(wpbe_checked_posts);

                                __wpbe_action_will_be_applied_to();
                            }

                        }, 2000);


                        //***

                        jQuery('#wpbe_bulk_posts_btn').show();
                        jQuery('.wpbe_bulk_terminate').hide();
                        wpbe_set_progress('wpbe_bulk_progress', 100);
                        jQuery(document).trigger('wpbe_bulk_completed');
                        wpbe_bulk_is_going(false);
                        jQuery('.wpbe_num_rounding').val(0);
                    },
                    error: function () {
                        if (!wpbe_bulk_user_cancel) {
                            alert(lang.error);
                            wpbe_bulk_terminate();
                        }
                        wpbe_bulk_is_going(false);
                    }
                });

            } else {
                //show %
                var percents = (start + step) * 100 / posts.length;
                wpbe_set_progress('wpbe_bulk_progress', percents);
                wpbe_bulk_is_going_txt(percents.toFixed(2));
                __wpbe_bulk_posts(posts, start + step, bulk_key, field_key);
            }
        },
        error: function () {
            if (!wpbe_bulk_user_cancel) {
                alert(lang.error);
                wpbe_bulk_terminate();
            }
            wpbe_bulk_is_going(false);
        }
    });
}

function wpbe_bulk_terminate() {
    wpbe_bulk_user_cancel = true;
    wpbe_bulk_xhr.abort();
    wpbe_hide_progress('wpbe_bulk_progress');
    jQuery('#wpbe_bulk_posts_btn').show();
    jQuery('.wpbe_bulk_terminate').hide();
    wpbe_message(lang.canceled, 'error');
    wpbe_bulk_user_cancel = false;
    wpbe_bulk_is_going(false);
}

function wpbe_bulk_is_going(going = true) {
    if (going) {
        jQuery('#wp-admin-bar-root-default').append("<li id='wpbe_bulk_is_going'>" + lang.bulk.bulk_is_going + " 0%</li>");
    } else {
        jQuery('#wpbe_bulk_is_going').remove();
    }

    //any way bulk edition been done in some way
    jQuery(document).trigger('wpbe_page_field_updated', [0, 0, 0]);

}

function wpbe_bulk_is_going_txt(val) {
    jQuery('#wpbe_bulk_is_going').html(lang.bulk.bulk_is_going + ' ' + val + '%');
}

function wpbe_bulk_init_additional() {

    jQuery('#wpbe_bulk_select_thumb_btn').on('click', function ()
    {
        var input_object = jQuery(this).parents('.filter-unit-wrap').find('.wpbe_bulk_value').eq(0);
        var image = wp.media({
            title: lang.upload_file,
            multiple: false
        }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    // We convert uploaded_image to a JSON object to make accessing it easier
                    uploaded_image = uploaded_image.toJSON();
                    if (typeof uploaded_image.url != 'undefined') {
                        jQuery('#wpbe_bulk_select_thumb').prop('src', uploaded_image.url);
                        jQuery(input_object).val(uploaded_image.id);
                    }
                });

        return false;
    });
}

