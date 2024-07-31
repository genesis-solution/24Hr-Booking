"use strict";

var wpbe_export_current_xhr = null;//current ajax request (for cancel)
var wpbe_export_user_cancel = false;
var wpbe_export_time_postfix = null;
var wpbe_export_file_url = null;

function wpbe_export_to_csv() {
    wpbe_export_time_postfix = wpbe_regenerate_exp_file_postfix();
    wpbe_export('csv');
}
function wpbe_export_to_xml() {
    wpbe_export_time_postfix = wpbe_regenerate_exp_file_postfix();
    wpbe_export('xml');
}
function wpbe_export_to_excel() {
    wpbe_export_time_postfix = wpbe_regenerate_exp_file_postfix();
    wpbe_export('excel');//todo
}

jQuery(function ($) {

    "use strict";

    jQuery(document).on("do_tabs-export", {}, function () {

        //if (!wpbe_bulk_chosen_inited) {
        setTimeout(function () {
            //set chosen
            jQuery('#tabs-export .chosen-select').chosen('destroy');
            jQuery('#tabs-export .chosen-select').chosen();
            //wpbe_bulk_chosen_inited = true;
        }, 150);
        //}
        wpbe_export_file_url = jQuery('.wpbe_export_posts_btn_down').attr('href');
    
        return true;
    });

});
function wpbe_regenerate_exp_file_postfix() {
    let currentTime = new Date();

    let d = currentTime.getDate();
    if (d < 10) {
        d = '0' + d;
    }

    let m = currentTime.getMonth() + 1;
    if (m < 10) {
        m = '0' + m;
    }

    let h = currentTime.getHours();
    if (h < 10) {
        h = '0' + h;
    }

    let min = currentTime.getMinutes();
    if (min < 10) {
        min = '0' + min;
    }

    return '_' + d + '-' + m + '-' + currentTime.getFullYear() + '-' + h + '-' + min;
}
function wpbe_export(format) {

    var export_txt = lang.export.want_to_export + '\n';

    //***   

    jQuery('.wpbe_export_posts_btn').hide();
    jQuery('.wpbe_export_posts_btn_down').hide();
    jQuery('.wpbe_export_posts_btn_cancel').show();
    wpbe_export_is_going();

    //***

    jQuery('.wpbe_progress_export').show();
    wpbe_message(lang.export.exporting, 'warning', 999999);

    if (wpbe_checked_posts.length > 0) {
        wpbe_export_current_xhr = jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_export_posts_count',
                format: format,
                no_filter: 1,
                csv_delimiter: jQuery('#wpbe_export_delimiter').val(),
                file_postfix: wpbe_export_time_postfix
            },
            success: function () {
                wpbe_set_progress('wpbe_export_progress', 0);
                __wpbe_export_posts(format, wpbe_checked_posts, 0);
            },
            error: function () {
                alert(lang.error);
                wpbe_export_is_going(false);
            }
        });
    } else {
        wpbe_export_current_xhr = jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_export_posts_count',
                format: format,
                filter_current_key: wpbe_filter_current_key,
                csv_delimiter: jQuery('#wpbe_export_delimiter').val(),
                file_postfix: wpbe_export_time_postfix
            },
            success: function (posts_ids) {
                posts_ids = JSON.parse(posts_ids);

                if (posts_ids.length) {
                    wpbe_set_progress('wpbe_export_progress', 0);
                    __wpbe_export_posts(format, posts_ids, 0);
                } else {
                    wpbe_export_is_going(false);
                }

            },
            error: function () {
                if (!wpbe_export_user_cancel) {
                    alert(lang.error);
                    wpbe_export_to_csv_cancel();
                }
                wpbe_export_is_going(false);
            }
        });
    }


    return false;
}

//service
function __wpbe_export_posts(format, posts, start) {
    var step = 10;
    var posts_ids = posts.slice(start, start + step);
    var behavior = jQuery("#wpbe_bulk_combination_attributes_export_behavior").val();
    wpbe_export_current_xhr = jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_export_posts',
            posts_ids: posts_ids,
            format: format,
            csv_delimiter: jQuery('#wpbe_export_delimiter').val(),
            behavior: behavior,
            file_postfix: wpbe_export_time_postfix
        },
        success: function (e) {
            //console.log(e);
            //return
            if ((start + step) > posts.length) {
                wpbe_message(lang.export.exported, 'notice');
                jQuery('.wpbe_export_posts_btn').show();
                if (format === 'xml') {
                    jQuery('.wpbe_export_posts_btn_down_xml').show();
		     jQuery('.wpbe_export_posts_btn_down_xml').attr('href', wpbe_export_file_url + 'wpbe_exported' + wpbe_export_time_postfix + '.xml');
                } else {
                    jQuery('.wpbe_export_posts_btn_down').show();
		    jQuery('.wpbe_export_posts_btn_down').attr('href', wpbe_export_file_url + 'wpbe_exported' + wpbe_export_time_postfix + '.csv');
                }
                jQuery('.wpbe_export_posts_btn_cancel').hide();
                wpbe_set_progress('wpbe_export_progress', 100);
                wpbe_export_is_going(false);
            } else {
                //show %
                wpbe_set_progress('wpbe_export_progress', (start + step) * 100 / posts.length);
                __wpbe_export_posts(format, posts, start + step);
            }
        },
        error: function () {
            if (!wpbe_export_user_cancel) {
                alert(lang.error);
                wpbe_export_to_csv_cancel();
            }
            wpbe_export_is_going(false);
        }
    });
}

function wpbe_export_to_csv_cancel() {
    wpbe_export_user_cancel = true;
    wpbe_export_current_xhr.abort();
    wpbe_hide_progress('wpbe_export_progress');
    jQuery('.wpbe_export_posts_btn').show();
    jQuery('.wpbe_export_posts_btn_down').hide();
    jQuery('.wpbe_export_posts_btn_cancel').hide();
    wpbe_message(lang.canceled, 'error');
    wpbe_export_user_cancel = false;
    wpbe_export_is_going(false);
}

function wpbe_export_is_going(go = true) {
    if (go) {
        jQuery('#wp-admin-bar-root-default').append("<li id='wpbe_export_is_going'>" + lang.export.export_is_going + "</li>");

    } else {
        jQuery('#wpbe_export_is_going').remove();
}

}
