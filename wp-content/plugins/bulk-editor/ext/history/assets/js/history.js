"use strict";

//flags
var wpbe_history_reverted = false;
var wpbe_history_reverting_going = false;//to block posts tab
var wpbe_history_data_is_changed = true;//for updating history list by ajax when its tab clicked

/*pagination*/
var wpbe_history_per_page = 10;
var wpbe_history_page_count = 0;

jQuery(function ($) {

    "use strict";

    //redraw posts if bulk revert done and clicked on tab Posts
    //https://learn.jquery.com/events/introduction-to-custom-events/
    jQuery(document).on("do_tabs-posts", {
        //foo: "bar"
    }, function () {
        if (wpbe_history_reverting_going) {
            alert(lang.history.wait_until_finish);
            return false;
        } else {
            if (wpbe_history_reverted) {
                //console.log( event.data.foo );
                wpbe_history_reverted = false;
                data_table.draw('page');
            }
        }

        __trigger_resize();
        return true;
    });

    //***

    jQuery(document).on("wpbe_page_field_updated", {}, function (event, post_id, field_key) {
        wpbe_history_data_is_changed = true;
        return true;
    });

    jQuery(document).on("wpbe_bulk_completed", {}, function (event) {
        wpbe_history_data_is_changed = true;
        return true;
    });

    //***
    //for history updating if data changed
    jQuery(document).on("do_tabs-history", {}, function () {
        if (wpbe_history_data_is_changed && !wpbe_history_reverting_going) {
            wpbe_history_update_list();
        }
        return true;
    });

    //***

//    jQuery('#wpbe_history_show_types').change(function () {
//        switch (parseInt(jQuery(this).val(), 10)) {
//            case 1:
//                jQuery('#wpbe_history_list li.solo_li').show();
//                jQuery('#wpbe_history_list li.bulk_li').hide();
//                break;
//            case 2:
//                jQuery('#wpbe_history_list li.solo_li').hide();
//                jQuery('#wpbe_history_list li.bulk_li').show();
//                break;
//            default:
//                //0
//                jQuery('#wpbe_history_list li').show();
//                break;
//        }
//
//        return true;
//    });

});

function wpbe_history_update_list() {
    jQuery('#wpbe_history_list_container').html('<h5>' + lang.loading + '</h5>');
    var nonce = jQuery('#wpbe_history_panel_nonce').val();
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_get_history_list',
	    wpbe_history_nonce: nonce
        },
        success: function (content) {
            jQuery('#wpbe_history_list_container').html(content);
            wpbe_history_init_pagination();
        },
        error: function () {
            alert(lang.error);
        }
    });

    //***
    //should be here!!
    wpbe_history_data_is_changed = false;
}

function wpbe_history_revert_solo(id, post_id) {
    if (confirm(lang.sure)) {

        wpbe_disable_bind_editing();

        //***
	var nonce = jQuery('#wpbe_history_panel_nonce').val();
        wpbe_message(lang.history.reverting, 'warning', 999999);
        jQuery('.wpbe_history_btn').hide();
        wpbe_history_is_going();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_history_revert_post',
                id: id,
		wpbe_history_nonce: nonce
            },
            success: function () {
                wpbe_message(lang.history.reverted, 'notice');

                if (jQuery('#post_row_' + post_id).length > 0) {
                    wpbe_redraw_table_row(jQuery('#post_row_' + post_id));
                }

                //wpbe_history_reverted = true;
                jQuery('#wpbe_history_' + id).remove();
                jQuery('.wpbe_history_btn').show();
                wpbe_history_is_going(true);
            },
            error: function () {
                alert(lang.error);
                wpbe_history_is_going(true);
            }
        });
    }
}

function wpbe_history_revert_bulk(bulk_key, bulk_id) {
    if (confirm(lang.sure)) {

        if (wpbe_bind_editing) {
            jQuery("[data-numcheck='wpbe_bind_editing']").trigger('click');
            wpbe_bind_editing = 0;
        }

        //***

        wpbe_message(lang.history.reverting, 'warning', 999999);
        wpbe_history_reverting_going = true;
        jQuery('.wpbe_history_btn').hide();
        wpbe_set_progress('wpbe_bulk_progress_' + bulk_id, 0);
        wpbe_history_is_going();
	var nonce = jQuery('#wpbe_history_panel_nonce').val();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_history_get_bulk_count',
                bulk_key: bulk_key,
		wpbe_history_nonce: nonce
            },
            success: function (total_count) {
                wpbe_history_revert_bulk_portion(bulk_id, bulk_key, total_count, 0);
            },
            error: function () {
                alert(lang.error);
                wpbe_history_reverting_going = false;
                wpbe_history_is_going(true);
            }
        });
    }
}

function wpbe_history_revert_bulk_portion(bulk_id, bulk_key, total_count, removed) {
    var step = 10;

    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_history_revert_bulk_portion',
            bulk_key: bulk_key,
            limit: step,
            removed_count: removed,
            total_count: total_count
        },
        success: function () {

            wpbe_set_progress('wpbe_bulk_progress_' + bulk_id, (removed + step) * 100 / total_count);

            if ((total_count - (removed + step)) <= 0) {
                wpbe_message(lang.history.reverted, 'notice');
                wpbe_history_reverted = true;
                wpbe_history_reverting_going = false;
                jQuery('#wpbe_history_' + bulk_key).remove();
                jQuery('.wpbe_history_btn').show();
                wpbe_history_is_going(true);
            } else {
                wpbe_history_revert_bulk_portion(bulk_id, bulk_key, total_count, removed + step);
            }

        },
        error: function () {
            wpbe_history_is_going(true);
            wpbe_history_reverting_going = false;
            alert(lang.error);
        }
    });
}

function wpbe_history_clear() {

    if (confirm(lang.sure)) {
        wpbe_message(lang.history.clearing, 'warning', 999999);
	var nonce = jQuery('#wpbe_history_panel_nonce').val();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_history_clear',
		wpbe_history_nonce: nonce
            },
            success: function () {
                wpbe_message(lang.history.cleared, 'notice');
                jQuery('#wpbe_history_list_container').html('<h5>' + lang.history.cleared + '</h5>');
            },
            error: function () {
                alert(lang.error);
            }
        });
    }

}

function wpbe_history_delete_solo(id) {
    if (confirm(lang.sure)) {
        wpbe_message(lang.deleting, 'warning', 999999);
	var nonce = jQuery('#wpbe_history_panel_nonce').val();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_history_delete_solo',
                id: id,
		wpbe_history_nonce: nonce
            },
            success: function () {
                wpbe_message(lang.deleted, 'notice');
                jQuery('#wpbe_history_' + id).remove();
            },
            error: function () {
                alert(lang.error);
            }
        });
    }
}

function wpbe_history_delete_bulk(bulk_key) {
    if (confirm(lang.sure)) {
        wpbe_message(lang.deleting, 'warning', 999999);
	var nonce = jQuery('#wpbe_history_panel_nonce').val();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_history_delete_bulk',
                bulk_key: bulk_key,
		wpbe_history_nonce: nonce
            },
            success: function () {
                wpbe_message(lang.deleted, 'notice');
                jQuery('#wpbe_history_' + bulk_key).remove();
            },
            error: function () {
                alert(lang.error);
            }
        });
    }
}


function wpbe_history_is_going(clear = false) {
    if (clear) {
        jQuery('#wpbe_history_is_going').remove();
    } else {
        jQuery('#wp-admin-bar-root-default').append("<li id='wpbe_history_is_going'>" + lang.history.history_is_going + "</li>");
}

}

/* pagination */

function  wpbe_history_init_pagination() {
    /*actions*/
    jQuery("#wpbe_history_pagination_number").on("change", function () {
        wpbe_history_per_page = jQuery(this).val();
        if (wpbe_history_per_page == -1) {
            wpbe_history_per_page = 99999;
        }
        wpbe_history_check_pagination();
    });
    jQuery(".wpbe_history_pagination_prev").on("click", function () {
        wpbe_history_page_count -= wpbe_history_per_page;
        if (wpbe_history_page_count < 0) {
            wpbe_history_page_count = 0;
        }
        wpbe_history_check_pagination();
        return false;
    });
    jQuery(".wpbe_history_pagination_next").on("click", function () {
        wpbe_history_page_count += wpbe_history_per_page;
        wpbe_history_check_pagination();
        return false;
    });
    jQuery(".wpbe_calendar_clear").on("click", function () {
        var id = jQuery(this).data("val-id");
        jQuery(".wpbe_calendar[data-val-id='" + id + "']").val('').trigger('change');
        return false;
    });

    jQuery("#wpbe_history_filter_submit").on("click", function () {
        var filters = {};

        filters['author'] = "mselect_wpbe_history_filter_author";
        filters['date_from'] = "wpbe_history_filter_date_from";
        filters['date_to'] = "wpbe_history_filter_date_to";
        filters['fields'] = "wpbe_history_filter_field";
        filters['types'] = "wpbe_history_show_types";
        jQuery.each(filters, function (i, item) {
            var val = jQuery("#" + item).val();

            filters[i] = val;
        });

        /*reset pagination and do search*/
        wpbe_history_page_count = 0;
        wpbe_history_do_search(filters);
        wpbe_history_check_pagination();

    });

    jQuery("#wpbe_history_filter_reset").on("click", function () {
        wpbe_history_page_count = 0;
        wpbe_history_cleare_filters();
        wpbe_history_do_search(null);
        wpbe_history_check_pagination();

    });

    wpbe_history_check_pagination();
}

function wpbe_history_cleare_filters() {

    jQuery(".wpbe_history_filters .wpbe_calendar").val('').trigger('change');
    jQuery("#wpbe_history_filter_field").val('');
    jQuery(".wpbe_history_filter_author").val(-1);
    jQuery("#wpbe_history_show_types").val(0);

}

function   wpbe_history_check_pagination() {
    var items = jQuery("li.wpbe_history_li_show");
    var show_item = wpbe_history_per_page;
    jQuery("li.wpbe_history_item").hide();
    jQuery.each(items, function (i, item) {
        if (i >= wpbe_history_page_count && show_item) {
            jQuery(item).show();
            show_item--;
        } else {
            jQuery(item).hide();
        }
    });

    if (wpbe_history_page_count <= 0) {
        jQuery(".wpbe_history_pagination_prev").hide();
    } else {
        jQuery(".wpbe_history_pagination_prev").show();
    }

    if (wpbe_history_page_count + wpbe_history_per_page >= items.length) {
        jQuery(".wpbe_history_pagination_next").hide();
    } else {
        jQuery(".wpbe_history_pagination_next").show();
    }
    jQuery(".wpbe_history_pagination_count").text(" " + items.length);
    var from = 0
    var to = items.length;
    from = wpbe_history_page_count;
    if (wpbe_history_page_count + wpbe_history_per_page < items.length) {
        to = parseInt(wpbe_history_page_count) + parseInt(wpbe_history_per_page);
    }

    jQuery(".wpbe_history_pagination_current_count").text(from + "-" + to + " ");
}

/*filter*/



function wpbe_history_do_search(filters) {
    var histories = jQuery(".wpbe_history_item");
    
    if (filters) {
	    var date_from = '';
	    var date_to = '';	

	    if (filters['date_from']) {
		var m_d = filters['date_from'].split(/\D/);
		if (m_d.length == 5) {
		     date_from =  new Date(Date.UTC(+m_d[0], +m_d[1] - 1, +m_d[2], +m_d[3], +m_d[4], 0)).getTime() / 1000;
		}	
	    }
	    if (filters['date_to']) {
		var m_d = filters['date_to'].split(/\D/);
		if (m_d.length == 5) {
		    date_to =  new Date(Date.UTC(+m_d[0], +m_d[1] - 1, +m_d[2], +m_d[3], +m_d[4], 0)).getTime() / 1000;
		    
		}	
	    }		
        jQuery.each(histories, function (i, item) {

            var data = jQuery(item).find(".wpbe_history_data")
            var author = data.data("author");
            var date = data.data("date");
            var fields = data.data("fields");
            var type = data.data("types");
            var hide = false;

            if (type != filters['types'] && filters['types'] != 0) {
                hide = true;
            }
            if (author != filters['author'] && filters['author'] != -1) {
                hide = true;
            }

            if (!hide && date_from && date_from > date) {

                hide = true;
            }
            if (!hide && date_to && date_to < date && filters['date_to'] != 0) {
                hide = true;

            }
            if (!hide && filters['fields'] && fields.indexOf(filters['fields']) == -1) {
                hide = true;
            }


            if (hide) {
                jQuery(item).removeClass("wpbe_history_li_show");
            } else {
                jQuery(item).addClass("wpbe_history_li_show");
            }

        });
    } else {
        jQuery(".wpbe_history_item").addClass("wpbe_history_li_show");
    }
}