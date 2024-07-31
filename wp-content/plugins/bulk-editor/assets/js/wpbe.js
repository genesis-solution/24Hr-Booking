"use strict";

var wpbe_popup_clicked = null;
var wpbe_sort_order = [];
var wpbe_checked_posts = [];//post id which been checked
var wpbe_last_checked_post = {id: 0, checked: false};
var wpbe_tools_panel_full_width = 0;


(function ($) {

    "use strict";

    jQuery(function () {

        "use strict";

        jQuery('.wpbe-tabs').wpbeTabs();

        //***

        jQuery(document).on('keyup', function (e) {
            if (e.keyCode === 27) {
                jQuery('.wpbe-modal-close').trigger('click');
            }
        });

        wpbe_init_tips(jQuery('.zebra_tips1'));

        //***
        //for columns coloring
        try {
            jQuery('.wpbe-color-picker').wpColorPicker();
        } catch (e) {
            console.log(e);
        }

        setTimeout(function () {
            jQuery('.wpbe_column_color_pickers').each(function (index, picker) {
                jQuery(picker).find('span.wp-color-result-text').eq(0).html(lang.color_picker_col);
                jQuery(picker).find('span.wp-color-result-text').eq(1).html(lang.color_picker_txt);
                //jQuery('.button.wp-color-result').attr('disabled', true);
            });
        }, 1000);

        //***

        jQuery(".wpbe_fields").sortable({
            items: "li:not(.unsortable)",
            update: function (event, ui) {
                wpbe_sort_order = [];
                jQuery('.wpbe_fields').children('li').each(function (index, value) {
                    var key = jQuery(this).data('key');
                    wpbe_sort_order.push(key);
                });
                jQuery('input[name="wpbe[items_order]"]').val(wpbe_sort_order.toString());
            },
            opacity: 0.8,
            cursor: "crosshair",
            handle: '.wpbe_drag_and_drope',
            placeholder: 'wpbe-options-highlight'
        });

        //fix: to avoid jumping
        jQuery('body').on('click', '.wpbe_drag_and_drope', function () {
            return false;
        });

        //***

        jQuery('#tabs_f .wpbe_calendar_cell_clear').on('click', function () {
            jQuery(this).parent().find('.wpbe_calendar').val('').trigger('change');
            return false;
        });


        //options saving
        jQuery('#mainform').on('submit', function () {
            wpbe_save_form(this, 'wpbe_save_options');
            return false;
        });

        //***

        jQuery('#show_all_columns').on('click', function () {
            jQuery('.wpbe_fields li').show();
            jQuery(this).parent().remove();
            return false;
        });

        //columns finder
        jQuery('#wpbe_columns_finder').on('keyup keypress', function (e) {
            var keyCode = e.keyCode || e.which;
            //preventing form submit if press Enter button
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }

            //***

            jQuery('#tabs-settings .wpbe_fields li').show();
            var search = jQuery(this).val().toLowerCase();

            jQuery('#tabs-settings .wpbe_fields li.wpbe_options_li .wpbe_column_li_option').each(function (index, input) {
                var txt = jQuery(input).val().toLowerCase();
                if (txt.indexOf(search) != -1) {
                    jQuery(input).parents('li').show();
                } else {
                    jQuery(input).parents('li').hide();
                }
            });

            return true;
        });


        //*****************************************

        jQuery('body').on('click', '.wpbe_select_image', function ()
        {
            var input_object = jQuery(this).prev('input[type=text]');
            window.send_to_editor = function (html)
            {
                jQuery('#wpbe_buffer').html(html);
                var imgurl = jQuery('#wpbe_buffer').find('a').eq(0).attr('href');
                jQuery('#wpbe_buffer').html("");
                jQuery(input_object).val(imgurl);
                jQuery(input_object).trigger('change');
                tb_remove();
            };
            tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

            return false;
        });

        //***

        wpbe_init_advanced_panel();
        if (parseInt(wpbe_get_from_storage('wpbe_tools_panel_full_width_btn'), 10)) {
            jQuery('.wpbe_tools_panel_full_width_btn').trigger('click');
        }
        //wpbe_init_bulk_panel();

        //options columns switchers only!
        wpbe_init_switchery(false);

        //***
        jQuery(document).scroll(function (e) {
            var offset = (jQuery('#tabs').offset().top + 15) - jQuery(document).scrollTop();

            if (offset < 0) {
                if (!jQuery('#wpbe_tools_panel').hasClass('wpbe-adv-panel-fixed')) {
                    jQuery('#wpbe_tools_panel').addClass('wpbe-adv-panel-fixed');
                    jQuery('#wpbe_tools_panel').css('top', jQuery('#wpadminbar').height() + 'px');
                    jQuery('#wpbe_tools_panel').css('width', jQuery('#tabs-posts').width() + 'px');
                }
            } else {
                jQuery('#wpbe_tools_panel').removeClass('wpbe-adv-panel-fixed');
            }
        });


        //***

        setTimeout(function () {
            jQuery('.dataTables_scrollBody').scrollbar({
                autoScrollSize: false,
                scrollx: jQuery('.external-scroll_x'),
                scrolly: jQuery('.external-scroll_y')
            });

            //***


            jQuery(document).on("tab_switched", {}, function (e, tab_id) {

                var allow = ['tabs-posts'];

                /*
                 * moved to observer
                 if (jQuery.inArray(tab_id, allow) > -1) {
                 jQuery('.external-scroll_wrapper').show();
                 } else {
                 jQuery('.external-scroll_wrapper').hide();
                 }
                 */

                return true;
            });

        }, 2000);

        //***

        jQuery('.site_editor_visibility').on('click', function () {
            var key = jQuery(this).data('key');
            var val = 0;

            if (jQuery(this).is(':checked')) {
                val = 1;
            }

            jQuery("input[name='wpbe_options[fields][" + key + "][site_editor_visibility]']").val(val);
            return true;
        });



        //https://stackoverflow.com/questions/123999/how-can-i-tell-if-a-dom-element-is-visible-in-the-current-viewport
        //https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
        (new window.IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                //enter
                jQuery('.external-scroll_wrapper').show();
                return;
            }
            //leave
            jQuery('.external-scroll_wrapper').hide();
        }, {
            root: null,
            threshold: 1.0, // set offset 0.1 means trigger if atleast 10% of element in viewport
        })).observe(document.querySelector('#wpbe_tools_panel'));



    });

})(jQuery);


function wpbe_init_advanced_panel() {

    //full width button
    jQuery('.wpbe_tools_panel_full_width_btn').on('click', function () {
        if (wpbe_tools_panel_full_width === 0) {
            wpbe_tools_panel_full_width = jQuery('#adminmenuwrap').width();
            jQuery('#adminmenuback').hide();
            jQuery('#adminmenuwrap').hide();
            jQuery('#wpcontent').css('margin-left', '0px');
            jQuery(this).addClass('button-primary');
            wpbe_set_to_storage('wpbe_tools_panel_full_width_btn', 1);
        } else {
            jQuery('#adminmenuback').show();
            jQuery('#adminmenuwrap').show();
            jQuery('#wpcontent').css('margin-left', wpbe_tools_panel_full_width + 'px');
            jQuery(this).removeClass('button-primary');
            wpbe_tools_panel_full_width = 0;
            wpbe_set_to_storage('wpbe_tools_panel_full_width_btn', 0);
        }

        __trigger_resize();

        return false;
    });

    //***

    jQuery('.wpbe_tools_panel_profile_btn').on('click', function () {
        //jQuery('#wpbe_tools_panel_profile_popup .wpbe-modal-title').html(jQuery(this).data('name') + ' [' +jQuery(this).data('key') + ']');
        jQuery('#wpbe_tools_panel_profile_popup').show();
        jQuery('#wpbe_new_profile').focus();

        return false;
    });


    //***

    jQuery('.wpbe-modal-close8').on('click', function () {
        jQuery('#wpbe_tools_panel_profile_popup').hide();
    });

    //***

    wpbe_init_profiles();

    //***
    //creating of new post
    jQuery('.wpbe_tools_panel_newprod_btn').on('click', function () {

        var count = 1;

        if (count = prompt(lang.enter_new_count, 1)) {
            if (count > 0) {
                wpbe_message(lang.creating, 'warning');
                __wpbe_post_new(count, 0);
            }
        }

        return false;
    });

    //***

    jQuery('.wpbe_tools_panel_duplicate_btn').on('click', function () {

        var posts_ids = [];
        jQuery('.wpbe_post_check').each(function (ii, ch) {
            if (jQuery(ch).prop('checked')) {
                posts_ids.push(jQuery(ch).data('post-id'));
            }
        });

        if (posts_ids.length) {
            var count = 1;
            if (count = prompt(lang.enter_duplicate_count, 1)) {
                if (count > 0) {
                    var posts = [];
                    for (var i = 0; i < count; i++) {
                        for (var y = 0; y < posts_ids.length; y++) {
                            posts.push(posts_ids[y]);
                        }
                    }

                    posts = posts.reverse();

                    wpbe_message(lang.duplicating, 'warning', 99999);
                    __wpbe_post_duplication(posts, 0, 0);
                }
            }
        }

        return false;
    });

    //hide or show duplicate button
    jQuery('body').on('click', '.wpbe_post_check', function (e) {

        var post_id = parseInt(jQuery(this).data('post-id'), 10);

        //if keep SHIFT button and check post checkbox - possible to select/deselect posts rows
        if (e.shiftKey) {

            if (jQuery(this).prop('checked')) {
                var to_check = true;
            } else {
                var to_check = false;
            }
            var distance_now = jQuery('#post_row_' + jQuery(this).data('post-id')).offset().top;
            var distance_last = jQuery('#post_row_' + wpbe_last_checked_post.id).offset().top;
            var rows = jQuery('#advanced-table tbody tr');

            if (distance_now > distance_last) {
                //check/uncheck all above to wpbe_last_checked_post.id
                jQuery(rows).each(function (index, tr) {
                    var d = jQuery(tr).offset().top;
                    if (d < distance_now && d > distance_last) {
                        jQuery(tr).find('.wpbe_post_check').prop('checked', to_check);
                    }
                });
            } else {
                //check/uncheck all below to wpbe_last_checked_post.id
                jQuery(rows).each(function (index, tr) {
                    var d = jQuery(tr).offset().top;
                    if (d > distance_now && d < distance_last) {
                        jQuery(tr).find('.wpbe_post_check').prop('checked', to_check);
                    }
                });
            }
        }

        //***

        if (jQuery(this).prop('checked')) {
            wpbe_select_row(post_id);
            wpbe_checked_posts.push(post_id);
            wpbe_last_checked_post.checked = true;
        } else {
            wpbe_select_row(post_id, false);
            //wpbe_checked_posts.splice(wpbe_checked_posts.indexOf(post_id), 1);
            wpbe_checked_posts = jQuery.grep(wpbe_checked_posts, function (value) {
                return value != post_id;
            });
            wpbe_last_checked_post.checked = false;
        }

        //***

        //push all another checked ids
        if (e.shiftKey) {
            jQuery(rows).each(function (index, tr) {
                var p_id = parseInt(jQuery(tr).data('post-id'), 10);
                if (jQuery(tr).find('.wpbe_post_check').prop('checked')) {
                    //console.log(p_id);
                    wpbe_checked_posts.push(p_id);
                    wpbe_select_row(p_id);
                } else {
                    //console.log('---' + p_id);
                    //wpbe_checked_posts.splice(wpbe_checked_posts.indexOf(p_id), 1);
                    for (var i = 0; i < wpbe_checked_posts.length; i++) {
                        if (p_id === wpbe_checked_posts[i]) {
                            wpbe_select_row(wpbe_checked_posts[i], false);
                            delete wpbe_checked_posts[i];
                        }
                    }
                }
            });

        }

        //***

        //remove duplicates if exists and filter values
        wpbe_checked_posts = Array.from(new Set(wpbe_checked_posts));
        wpbe_checked_posts = wpbe_checked_posts.filter(function (n) {
            return n != undefined;
        });
        //console.log(wpbe_checked_posts);

        //***
        wpbe_last_checked_post.id = post_id;
        __wpbe_action_will_be_applied_to();
        __manipulate_by_depend_buttons();
        wpbe_add_info_top_panel();
    });

    //***
    //check all posts
    jQuery('.all_posts_checker').on('click', function () {
        if (wpbe_show_variations > 0) {
            jQuery('tr .wpbe_post_check').trigger('click');
            if (jQuery('tr .wpbe_post_check:checked').length) {
                jQuery(this).prop('checked', 'checked');
            }
        } else {
            //post_type_variation
            jQuery('tr:not(.post_type_variation) .wpbe_post_check').trigger('click');
            if (jQuery('tr:not(.post_type_variation) .wpbe_post_check:checked').length) {
                jQuery(this).prop('checked', 'checked');
            }
        }
    });

    //uncheck all posts
    jQuery('.wpbe_tools_panel_uncheck_all').on('click', function () {
        jQuery('.wpbe_post_check').prop('checked', false);
        jQuery('.all_posts_checker').prop('checked', false);
        wpbe_checked_posts = [];
        __manipulate_by_depend_buttons();
        __wpbe_action_will_be_applied_to();
        jQuery('.wpbe_checked_info').remove();
        return false;
    });

    //***

    jQuery('.wpbe_tools_panel_delete_btn').on('click', function () {

        if (confirm(lang.sure)) {
            var posts_ids = [];
            jQuery('.wpbe_post_check').each(function (ii, ch) {
                if (jQuery(ch).prop('checked')) {
                    posts_ids.push(jQuery(ch).data('post-id'));
                }
            });

            if (posts_ids.length) {
                wpbe_message(lang.deleting, 'warning', 999999);
                __wpbe_post_removing(posts_ids, 0, 0);
            }
        }

        return false;
    });

    //***
    //another way chosen drop-downs width is 0
    setTimeout(function () {
        jQuery('.wpbe_top_panel').hide();
        //jQuery('.wpbe_top_panel').height(500);
        jQuery('.wpbe_top_panel').css('margin-top', '-' + jQuery('.wpbe_top_panel').height());
        //page loader fade
        jQuery(".wpbe-admin-preloader").fadeOut("slow");
    }, 1000);
    /*
     window.onresize = function (event) {
     jQuery('.wpbe_top_panel').hide();
     jQuery('.wpbe_top_panel').height(500);
     jQuery('.wpbe_top_panel').css('margin-top', 0);
     };
     */

    //Show/Hide button for filter
    jQuery('.wpbe_top_panel_btn').on('click', function () {
        var _this = this;
        jQuery('.wpbe_top_panel').slideToggle('slow', function () {
            if (jQuery(this).is(':visible')) {
                jQuery(_this).html(lang.close_panel);
                //jQuery('#wpbe_scroll_left').hide();
                //jQuery('#wpbe_scroll_right').hide();
            } else {
                /*
                 if (jQuery('#advanced-table').width() > jQuery('#tabs-posts').width()) {
                 jQuery('#wpbe_scroll_left').show();
                 jQuery('#wpbe_scroll_right').show();
                 }
                 */

                jQuery(_this).html(lang.show_panel);
            }
        });

        jQuery(document).trigger("wpbe_top_panel_clicked");

        return false;
    });


    jQuery('.wpbe_top_panel_btn2').on('click', function (e) {
        jQuery('.wpbe_top_panel_btn').trigger('click');
        return false;
    });

    //***

    jQuery('#js_check_wpbe_show_variations').on('check_changed', function () {

        wpbe_show_variations = parseInt(jQuery(this).val(), 10);
        wpbe_set_to_storage('wpbe_show_variations', wpbe_show_variations);

        if (wpbe_show_variations > 0) {
            if (jQuery('tr.post_type_variation').length > 0) {
                jQuery('tr.post_type_variation').show();
            } else {
                data_table.draw('page');

            }
            jQuery('.not-for-variations').hide();
            jQuery('#wpbe_show_variations_mode').show();

            //***

            jQuery('#wpbe_select_all_vars').show();

        } else {
            jQuery('tr.post_type_variation').hide();
            jQuery('.not-for-variations').show();
            wpbe_init_js_intab('tabs-bulk');
            jQuery('#wpbe_show_variations_mode').hide();

            //***
            //uncheck all checked attributes to avoid confusing with any bulk operation!
            if (jQuery('tr.post_type_variation.wpbe_selected_row').length > 0) {
                jQuery('tr.post_type_variation.wpbe_selected_row .wpbe_post_check').prop('checked', false);
                //jQuery('.all_posts_checker').prop('checked', false);

                jQuery('tr.post_type_variation.wpbe_selected_row').each(function (index, row) {
                    var post_id = parseInt(jQuery(row).data('post-id'));

                    //https://stackoverflow.com/questions/3596089/how-to-remove-specific-value-from-array-using-jquery
                    wpbe_checked_posts = jQuery.grep(wpbe_checked_posts, function (value) {
                        return value != post_id;
                    });

                });

                __manipulate_by_depend_buttons();
                __wpbe_action_will_be_applied_to();
            }

            //***

            jQuery('#wpbe_select_all_vars').hide();
            //__trigger_resize();
            wpbe_init_js_intab('tabs-posts');
        }

        //***

        jQuery('#tabs-bulk .chosen-select').chosen('destroy');
        jQuery('#tabs-bulk .chosen-select').chosen();

        jQuery('#tabs-export .chosen-select').chosen('destroy');
        jQuery('#tabs-export .chosen-select').chosen();
        //***

        return true;
    });

    if (wpbe_show_variations > 0) {
        jQuery("[data-numcheck='wpbe_show_variations']").prop('checked', true);
        jQuery('#js_check_wpbe_show_variations').prop('value', 1);
    }

    //***

    jQuery('#wpbe_post_type_selector').on('change', function () {
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_set_active_post_type',
                post_type: jQuery(this).val()
            },
            success: function () {
                window.location.reload();
            },
            error: function () {
                alert(lang.error);
            }
        });

        return true;
    });

}

//init special function  for variation
function wpbe_init_special_variation() {
    jQuery("select[data-field='tax_class']").find("option[value='parent']").hide();
    jQuery(".post_type_variation select[data-field='tax_class']").find("option[value='parent']").show();
    if (wpbe_show_variations > 0) {
        jQuery('select[name="wpbe_bulk[tax_class][value]"]').find("option[value='parent']").show();
    } else {
        jQuery('select[name="wpbe_bulk[tax_class][value]"]').find("option[value='parent']").hide();
    }
}

//service
function __wpbe_post_new(count, created) {

    var step = 10;
    var to_create = (created + step) < count ? step : count - created;
    wpbe_message(lang.creating + ' (' + (created + to_create) + ')', 'warning');
    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_create_new_post',
            to_create: to_create,
	    wpbe_nonce: wpbe_nonce
        },
        success: function () {
            if ((created + step) < count) {
                created += step;
                __wpbe_post_new(count, created);
            } else {
                //https://stackoverflow.com/questions/25929347/how-to-redraw-datatable-with-new-data
                //data_table.clear().draw();
                wpbe_checked_posts = [];
                __manipulate_by_depend_buttons();
                data_table.order([1, 'desc']).draw();
                //data_table.rows.add(NewlyCreatedData); // Add new data
                //data_table.columns.adjust().draw(); // Redraw the DataTable
                wpbe_message(lang.created, 'notice');
            }
        },
        error: function () {
            alert(lang.error);
        }
    });

}

//service
var wpbe_post_duplication_errors = 0;
function __wpbe_post_duplication(posts, start, duplicated) {

    var step = 2;
    var posts_ids = posts.slice(start, start + step);
    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_duplicate_posts',
            posts_ids: posts_ids,
	    wpbe_nonce: wpbe_nonce
        },
        success: function () {
            if ((start + step) > posts.length) {
                //data_table.clear().draw();
                wpbe_checked_posts = [];
                __manipulate_by_depend_buttons();
                data_table.order([1, 'desc']).draw();
                wpbe_message(lang.duplicated, 'notice', 99999);
            } else {
                duplicated += step;
                if (duplicated > posts.length) {
                    duplicated = posts.length;
                }
                wpbe_message(lang.duplicating + ' (' + (posts.length - duplicated) + ')', 'warning', 99999);
                __wpbe_post_duplication(posts, start + step, duplicated);
            }
        },
        error: function () {
            wpbe_message(lang.error, 'error');
            wpbe_post_duplication_errors++;
            if (wpbe_post_duplication_errors > 5) {
                alert(lang.error);
                wpbe_post_duplication_errors = 0;
            } else {
                //lets try again
                __wpbe_post_duplication(posts, start, duplicated);
            }
        }
    });


}


//service
function __wpbe_post_removing(posts, start, deleted) {
    var step = 10;

    var posts_ids_portion = posts.slice(start, start + step);
    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_delete_posts',
            posts_ids: posts_ids_portion,
	    wpbe_nonce: wpbe_nonce
        },
        success: function () {
            if ((start + step) > posts.length) {
                //data_table.clear().draw();
                wpbe_checked_posts = jQuery(wpbe_checked_posts).not(posts).get();

                for (var i = 0; i < posts.length; i++) {
                    if (jQuery('#post_row_' + posts[i]).hasClass('post_type_variable')) {
                        (jQuery('#post_row_' + posts[i]).nextAll('tr')).each(function (index, tr) {
                            if (jQuery(tr).hasClass('post_type_variation')) {
                                jQuery(tr).remove();
                            } else {
                                return false;
                            }
                        });
                    }

                    jQuery('#post_row_' + posts[i]).remove();
                }
                wpbe_message(lang.deleted, 'notice');

                __manipulate_by_depend_buttons();
                __wpbe_action_will_be_applied_to();
            } else {
                deleted += step;
                if (deleted > posts.length) {
                    deleted = posts.length;
                }
                wpbe_message(lang.deleting + ' (' + (posts.length - deleted) + ')', 'warning');
                __wpbe_post_removing(posts, start + step, deleted);
            }
        },
        error: function () {
            alert(lang.error);
        }
    });


}


//service

function wpbe_add_info_top_panel() {
    jQuery('.wpbe_checked_info').remove();
    if (typeof wpbe_checked_posts != 'undefined' && wpbe_checked_posts.length) {
        var text_info = "<span class='wpbe_checked_info'>" + lang.checked_post + ": <b>" + wpbe_checked_posts.length + "</b></span>";
        jQuery('#advanced-table_wrapper').prepend(text_info);
    }
}
var __manipulate_by_depend_color_rows_lock = false;
function __manipulate_by_depend_buttons(show = true) {

    if (show) {
        show = jQuery('.wpbe_post_check:checked').length;
    }

    //***

    if (show) {
        jQuery('.wpbe_tools_panel_duplicate_btn').show();
        jQuery('.wpbe_tools_panel_delete_btn').show();
    } else {
        jQuery('.wpbe_tools_panel_duplicate_btn').hide();
        jQuery('.wpbe_tools_panel_delete_btn').hide();
    }

    //***

    if (wpbe_checked_posts.length) {
        jQuery('.wpbe_tools_panel_uncheck_all').show();

        if (!__manipulate_by_depend_color_rows_lock) {
            setTimeout(function () {

                for (var i = 0; i < wpbe_checked_posts.length; i++) {
                    wpbe_select_row(wpbe_checked_posts[i]);
                }

                __manipulate_by_depend_color_rows_lock = false;
            }, 777);
            __manipulate_by_depend_color_rows_lock = true;
        }

    } else {
        jQuery('.wpbe_tools_panel_uncheck_all').hide();
        jQuery('#advanced-table tr').removeClass('wpbe_selected_row');
}
}

function wpbe_select_row(post_id, select = true) {
    if (select) {
        jQuery('#post_row_' + post_id).addClass('wpbe_selected_row');
    } else {
        jQuery('#post_row_' + post_id).removeClass('wpbe_selected_row');
}
}

function wpbe_init_tips(obj) {
    //https://www.jqueryscript.net/demo/Lightweight-Highly-Customizable-jQuery-Tooltip-Plugin-Zebra-Tooltips/examples/
    new jQuery.Zebra_Tooltips(obj, {
        background_color: '#333',
        color: '#FFF'
    });
}


function wpbe_init_switchery(only_data_table = true, post_id = 0) {

    var adv_tbl_id_string = '#advanced-table ';
    if (!only_data_table) {
        adv_tbl_id_string = '';//initialization switches for options too
    }

    //reinit only 1 row
    if (post_id > 0) {
        adv_tbl_id_string = adv_tbl_id_string + '#post_row_' + post_id + ' ';
    }

    //***

    //http://abpetkov.github.io/switchery/
    if (typeof Switchery !== 'undefined') {
        var elems = Array.prototype.slice.call(document.querySelectorAll(adv_tbl_id_string + '.js-switch'));
        elems.forEach(function (ch) {
            new Switchery(ch);
            //while reinit draws duplicates of switchers
            jQuery(ch).parent().find('span.switchery:not(:first)').remove();
        });
    }

    //***

    if (jQuery(adv_tbl_id_string + '.js-check-change').length > 0) {

        jQuery.each(jQuery(adv_tbl_id_string + '.js-check-change'), function (index, item) {

            jQuery(item).off('change');
            jQuery(item).on('change', function () {
                var state = item.checked.toString();
                var numcheck = jQuery(item).data('numcheck');
                var trigger_target = jQuery(item).data('trigger-target');
                var label = jQuery("*[data-label-numcheck='" + numcheck + "']");
                var hidden = jQuery("*[data-hidden-numcheck='" + numcheck + "']");
                label.html(jQuery(item).data(state));
                jQuery(label).removeClass(jQuery(item).data('class-' + (!(item.checked)).toString()));
                jQuery(label).addClass(jQuery(item).data('class-' + state));
                var val = jQuery(item).data('val-' + state);
                var field_name = jQuery(hidden).attr('name');
                jQuery(hidden).val(val);

                if (trigger_target.length) {
                    jQuery(this).trigger("check_changed", [trigger_target, field_name, item.checked, val, numcheck]);
                    jQuery('#' + trigger_target).trigger("check_changed");//for any single switchers
                }
            });

        });

        //***
        jQuery("#advanced-table .js-check-change").off('check_changed');
        jQuery("#advanced-table .js-check-change").on("check_changed", function (event, trigger_target, field_name, is_checked, val, post_id) {
            wpbe_message(lang.saving, '');

            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
                    post_id: post_id,
                    field: field_name,
                    value: val
                },
                success: function () {
                    jQuery(document).trigger('wpbe_page_field_updated', [parseInt(post_id, 10), field_name, val]);
                    wpbe_message(lang.saved, 'notice');
                }
            });
        });

}
}

/**************************************************************************/


function wpbe_set_progress(id, width) {
    if (jQuery('#' + id).length > 0) {
        jQuery('#' + id).parents('.wpbe_progress').show();
        //document.getElementById(id).parentElement.style.display = 'block';
        document.getElementById(id).style.width = width + '%';
        document.getElementById(id).innerHTML = width.toFixed(2) + '%';
    }
}

function wpbe_hide_progress(id) {
    if (jQuery('#' + id).length > 0) {
        wpbe_set_progress(id, 0);
        jQuery('#' + id).parents('.wpbe_progress').hide();
    }
}

//attach event for any manipulations with content of the tabs by their id
function wpbe_init_js_intab(tab_id) {
    jQuery(document).trigger("do_" + tab_id);
    jQuery(document).trigger("tab_switched", [tab_id]);
    return true;
}


function wpbe_get_from_storage(key) {
    if (typeof (Storage) !== "undefined") {
        return localStorage.getItem(key);
    }

    return 0;
}

function wpbe_set_to_storage(key, value) {
    if (typeof (Storage) !== "undefined") {
        localStorage.setItem(key, value);
        return key;
    }

    return 0;
}

function wpbe_save_form(form, action) {
    wpbe_message(lang.saving, 'warning');
    var nonce = jQuery('#wpbe_settings_nonce').val();
    jQuery('[type=submit]').replaceWith('<img src="' + spinner + '" width="60" alt="" />');
    var data = {
        action: action,
        formdata: jQuery(form).serialize(),
	save_nonce: nonce
    };
    jQuery.post(ajaxurl, data, function () {
        window.location.reload();
    });
}


//give info about to which posts will be applied bulk edition
function __wpbe_action_will_be_applied_to() {
    //wpbe_action_will_be_applied_to
    if (wpbe_checked_posts.length) {
        //high priority
        jQuery('.wpbe_action_will_be_applied_to').html(lang.action_state_31 + ': ' + wpbe_checked_posts.length + '. ' + lang.action_state_32);
    } else {
        if (wpbe_filtering_is_going) {
            //if there is filtering going
            jQuery('.wpbe_action_will_be_applied_to').html(lang.action_state_2);
        } else {
            //no filtering and no checked posts
            jQuery('.wpbe_action_will_be_applied_to').html(lang.action_state_1);
        }
    }
}

function wpbe_get_random_string(len = 16) {
    var charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
        var randomPoz = Math.floor(Math.random() * charSet.length);
        randomString += charSet.substring(randomPoz, randomPoz + 1);
    }
    return randomString;
}


function __wpbe_fill_select(select_id, data, selected = [], level = 0, val_as_slug = false) {

    var margin_string = '';
    if (level > 0) {
        for (var i = 0; i < level; i++) {
            margin_string += '&nbsp;&nbsp;&nbsp;';
        }
    }

    //***

    jQuery(data).each(function (i, d) {
        var sel = '';
        var val = d.term_id;
        if (val_as_slug) {
            val = d.slug;
        }

        //***

        if (jQuery.inArray(val, selected) > -1) {
            sel = 'selected';
        }
        jQuery('#' + select_id).append('<option ' + sel + ' value="' + val + '">' + margin_string + d.name + '</option>');
        if (d.childs) {
            __wpbe_fill_select(select_id, d.childs, selected, level + 1, val_as_slug);
        }
    });
}


function wpbe_init_profiles() {
    jQuery('#wpbe_load_profile').on('change', function () {

        var profile_key = jQuery(this).val();
        if (profile_key != 0) {
            jQuery('#wpbe_load_profile_actions').show();
        } else {
            jQuery('#wpbe_load_profile_actions').hide();
        }

    });

    //***

    jQuery('#wpbe_load_profile_btn').on('click', function () {

        var profile_key = jQuery('#wpbe_load_profile').val();
	var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
        jQuery('.wpbe-modal-close8').trigger('click');

        if (profile_key != 0) {
            wpbe_message(lang.loading, 'warning');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_load_profile',
                    profile_key: profile_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (answer) {
                    wpbe_message(lang.loading, 'warning');
                    window.location.reload();
                }
            });
        }

    });

    //***

    jQuery('#wpbe_new_profile_btn').on('click', function () {
        var profile_title = jQuery('#wpbe_new_profile').val();
	var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
        if (profile_title.length) {
            wpbe_message(lang.creating, 'warning');
            //jQuery('.wpbe-modal-close8').trigger('click');
            jQuery('#wpbe_new_profile').val('');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_create_profile',
                    profile_title: profile_title,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (key) {
                    if (parseInt(key, 10) !== -2) {
                        jQuery('#wpbe_load_profile').append('<option selected value="' + key + '">' + profile_title + '</option>');
                        wpbe_message(lang.saved, 'notice');
                    } else {
                        alert(lang.free_ver_profiles);
                        wpbe_message('', 'clean');
                    }
                }
            });
        } else {
            wpbe_message(lang.fill_up_data, 'warning');
        }
    });

    jQuery('#wpbe_new_profile').keydown('keydown', function (e) {
        if (e.keyCode == 13) {
            jQuery('#wpbe_new_profile_btn').trigger('click');
        }
    });

    //***

    jQuery('.wpbe_delete_profile').on('click', function () {

        var profile_key = jQuery(this).attr('href');
        if (profile_key === '#') {
            profile_key = jQuery('#wpbe_load_profile').val();
        }

        if (jQuery.inArray(profile_key, wpbe_non_deletable_profiles) > 1) {
            wpbe_message(lang.no_deletable, 'warning');
            return false;
        }

        //***

        if (confirm(lang.sure)) {
            wpbe_message(lang.saving, 'warning');
            //jQuery('.wpbe-modal-close8').trigger('click');
            var select = document.getElementById('wpbe_load_profile');
            select.removeChild(select.querySelector('option[value="' + profile_key + '"]'));
            jQuery('.current_profile_disclaimer').remove();
	    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_delete_profile',
                    profile_key: profile_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (key) {
                    wpbe_message(lang.saved, 'notice');
                }
            });
        }
        return false;
    });

}

function wpbe_disable_bind_editing() {
    if (wpbe_bind_editing) {
        jQuery("[data-numcheck='wpbe_bind_editing']").trigger('click');
        wpbe_bind_editing = 0;
    }
}

//service
function __trigger_resize() {

    setTimeout(function () {
        window.dispatchEvent(new Event('resize'));
    }, 10);

    //jQuery(window).trigger('resize');

    /*
     * for tests
     jQuery('.wpbe_tools_panel_full_width_btn').trigger('click', function () {
     jQuery('.wpbe_tools_panel_full_width_btn').trigger('click');
     });
     */

}

