"use strict";

var wpbe_sort_order = [];
var data_table = null;
var posts_types = null;//data got from server
var posts_titles = null;//data got from server
var wpbe_show_variations = 0;//show or hide variations of the variable posts
var autocomplete_request_delay = 999;
var autocomplete_curr_index = -1;//for selecting by Enter button

//***

jQuery(function ($) {
    "use strict";
    if (typeof jQuery.fn.DataTable !== 'undefined') {
        //wpbe_show_variations = wpbe_get_from_storage('wpbe_show_variations');// - disabled because not sure that it will be right for convinience

        //hiding not relevant filter and bulk operations
        if (wpbe_show_variations > 0) {
            jQuery('.not-for-variations').hide();
            jQuery('#wpbe_show_variations_mode').show();

            jQuery('#wpbe_select_all_vars').show();
        }



        //***

        init_data_tables();//data tables

        //***
        //fix to close opened textinputs in the data table
        jQuery('#tabs-posts *').mousedown(function (e) {
            if (typeof e.srcElement !== 'undefined' && !jQuery(e.srcElement).hasClass('editable')) {
                if (!jQuery(e.srcElement).parent().hasClass('editable')) {
                    wpbe_close_prev_textinput();
                }
            }
            return true;
        });

        //***

        jQuery('body').on('click', '.wpbe-id-permalink-var', function () {

            if (wpbe_show_variations) {
                jQuery(this).parents('tr').nextAll('tr').each(function (ii, tr) {
                    if (jQuery(tr).hasClass('post_type_variation')) {
                        jQuery(tr).find('.wpbe_post_check').prop('checked', true);
                        wpbe_checked_posts.push(parseInt(jQuery(tr).data('post-id'), 10));
                    } else {
                        return false;//terminate tr's selection
                    }
                });

                //remove duplicates if exists
                wpbe_checked_posts = Array.from(new Set(wpbe_checked_posts));
                __manipulate_by_depend_buttons();
                __wpbe_action_will_be_applied_to();
                return false;
            }

            return true;
        });

        //***

        jQuery('#wpbe_select_all_vars').on('click', function () {

            jQuery('tr.post_type_variation').each(function (ii, tr) {
                jQuery(tr).find('.wpbe_post_check').prop('checked', true);
                wpbe_checked_posts.push(parseInt(jQuery(tr).data('post-id'), 10));
            });

            //remove duplicates if exists
            wpbe_checked_posts = Array.from(new Set(wpbe_checked_posts));
            __manipulate_by_depend_buttons();
            __wpbe_action_will_be_applied_to();

            return false;
        });

        //***
        //fix for applying coloring css styles for stock status drop-downs and etc ...
        jQuery('body').on('change', 'td.editable .select-wrap select', function () {
            jQuery(this).attr('data-selected', jQuery(this).val());
            return true;
        });

    }
});



var do_data_tables_first = true;
function init_data_tables() {
    var oTable = jQuery('#advanced-table');

    var page_fields = oTable.data('fields');

    //if not permited any post type for edit
    if (typeof page_fields === 'undefined') {
        return;
    }

    //***

    var page_fields_array = page_fields.split(',');
    
    var edit_views = oTable.data('edit-views');
    var edit_views_array = edit_views.split(',');

    var edit_sanitize = oTable.data('edit-sanitize');
    var edit_sanitize_array = edit_sanitize.split(',');

    var start_page = oTable.data('start-page');
    //var ajax_additional = oTable.data('additional');
    var per_page = parseInt(oTable.data('per-page'), 10);
    //https://datatables.net/examples/advanced_init/dt_events.html
    var extend_per_page = oTable.data('extend_per-page');
    var lengt_menu = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100];

    if (extend_per_page.length > 0) {
        lengt_menu = extend_per_page.split(',');
    }

    data_table = oTable.on('order.dt', function () {
        jQuery('.wpbe_tools_panel_uncheck_all').trigger('click');
    }).DataTable({
        //dom: 'Bfrtip',
        //https://tunatore.wordpress.com/2012/02/11/datatables-jquert-pagination-on-both-top-and-bottom-solution-if-you-use-bjqueryui/
        //sDom: '<"H"Bflrp>t<"F"ip>',
        sDom: '<"H"Blpr>t<"F"ip>',
        orderClasses: false,
        scrollX: true,
        lengthMenu: lengt_menu,
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        //https://datatables.net/examples/basic_init/table_sorting.html
        order: [[oTable.data('default-sort-by'), oTable.data('sort')]],
        //https://stackoverflow.com/questions/12008545/disable-sorting-on-last-column-when-using-jquery-datatables/22714994#22714994
        aoColumnDefs: [{
                bSortable: false,
                //aTargets: [-1] /* 1st one, start by the right */
                aTargets: (oTable.data('no-order')).toString().split(',').map(function (num) {
                    return parseInt(num, 10);
                })
            }, {className: "editable", targets: (oTable.data('editable')).toString().split(',').map(function (num) {
                    return parseInt(num, 10);
                })}],
        createdRow: function (row, data, dataIndex) {
            var p_id = data[1];//data[1] is ID col
            p_id = jQuery(p_id).text();//!! important as we have link <a> in ID cell
            jQuery(row).attr('data-post-id', p_id);
            jQuery(row).attr('id', 'post_row_' + p_id);
            jQuery(row).attr('data-row-num', dataIndex);
            jQuery(row).addClass('post_type_' + posts_types[p_id]);

            //***

            jQuery.each(jQuery('td', row), function (colIndex) {
                jQuery(this).attr('onmouseover', 'wpbe_td_hover(' + p_id + ', "' + posts_titles[p_id] + '", ' + colIndex + ')');
                jQuery(this).attr('onmouseleave', 'wpbe_td_hover(0, "",0)');

                //***

                jQuery(this).attr('data-field', page_fields_array[colIndex]);
                jQuery(this).attr('data-editable-view', edit_views_array[colIndex]);
                jQuery(this).attr('data-sanitize', edit_sanitize_array[colIndex]);
                jQuery(this).attr('data-col-num', colIndex);
                if (edit_views_array[colIndex] == 'url') {
                    jQuery(this).addClass('textinput_url');
                }
                if (edit_views_array[colIndex] == 'textinput' || edit_views_array[colIndex] == 'url') {
                    jQuery(this).addClass('textinput_col');
                    jQuery(this).attr('onclick', 'wpbe_click_textinput(this, ' + colIndex + ')');
                    //jQuery(this).attr('title', 'test');
                }

                if (edit_sanitize_array[colIndex] == 'floatval' || edit_sanitize_array[colIndex] == 'intval') {
                    jQuery(this).attr('onmouseover', 'wpbe_td_hover(' + p_id + ', "' + posts_titles[p_id] + '", ' + colIndex + ');wpbe_onmouseover_num_textinput(this, ' + colIndex + ');');
                    jQuery(this).attr('data-post-id', p_id);
                } else {
                    jQuery(this).attr('onmouseout', 'wpbe_td_hover(0, "",0);wpbe_onmouseout_num_textinput();');
                }

                //***
                //remove class editable in cells which are not editable
                if (jQuery(this).find('.info_restricked').length > 0) {
                    jQuery(this).removeClass('editable');
                }
            });

        },
        processing: true,
        serverSide: true,
        bDeferRender: true,
        deferRender: true,
        //https://datatables.net/manual/server-side
        //https://datatables.net/examples/data_sources/server_side.html
        //ajax: ajaxurl + '?action=wpbe_get_posts',
        ajax: {
            url: ajaxurl,
            type: "POST",
            bDeferRender: true,
            deferRender: true,
            data: {
                action: 'wpbe_get_posts',
                wpbe_show_variations: function () {
                    return wpbe_show_variations;//we use function to return actual value for the current moment
                },
                filter_current_key: function () {
                    return wpbe_filter_current_key;//we use function to return actual value for the current moment
                },
                lang: wpbe_lang
            }
        },
        searchDelay: 100,
        pageLength: per_page,
        displayStart: start_page > 0 ? (start_page - 1) * per_page : 0,
        oLanguage: {
            sEmptyTable: lang.sEmptyTable,
            sInfo: lang.sInfo,
            sInfoEmpty: lang.sInfoEmpty,
            sInfoFiltered: lang.sInfoFiltered,
            sLoadingRecords: lang.sLoadingRecords,
            sProcessing: lang.sProcessing,
            sZeroRecords: lang.sZeroRecords,
            oPaginate: {
                sFirst: lang.sFirst,
                sLast: lang.sLast,
                sNext: lang.sNext,
                sPrevious: lang.sPrevious
            }
        },
        fnPreDrawCallback: function (a) {

            if (typeof a.json != 'undefined') {
                //console.log(a.json.query);
                posts_types = a.json.posts_types;
                posts_titles = a.json.posts_titles;
            }
            //console.log(posts_types);
            wpbe_message(lang.loading, '', 300000);
        },
        fnDrawCallback: function () {

            do_data_tables_first = false;

            init_data_tables_edit();
            jQuery('.all_posts_checker').prop('checked', false);
            jQuery('.wpbe_checked_info').remove();
            __manipulate_by_depend_buttons(false);
            wpbe_message('', 'clean');
            wpbe_init_special_variation();
            wpbe_init_scroll();


            jQuery('.wpbe_post_check').each(function (ii, ch) {
                if (jQuery.inArray(parseInt(jQuery(ch).data('post-id'), 10), wpbe_checked_posts) != -1) {
                    jQuery(ch).prop('checked', true);
                }
            });


            __manipulate_by_depend_buttons();
            jQuery(document).trigger("data_redraw_done");

            //***
            //page jumper is here
            start_page = (this.fnSettings()._iDisplayStart / this.fnSettings()._iDisplayLength) + 1;

            jQuery("#advanced-table_paginate .paginate_button.next").after('<input type="number" id="wpbe-page-jumper" min=1 class="" value="' + start_page + '" />');

            var _this = this;
            jQuery("#wpbe-page-jumper").off().on('keyup', function (e) {
                if (e.keyCode === 13) {
                    //document.location.href = '/wp-admin/admin.php?page=wpbe&start_page=' + jQuery(this).val();
                    var pp = jQuery(this).val() - 1;
                    if (pp < 0) {
                        pp = 0;
                        jQuery(this).val(1);
                    }
                    _this.fnPageChange(pp, true);
                }
            });

            jQuery("#wpbe-page-jumper").off().on('change', function (e) {
                var pp = jQuery(this).val() - 1;
                if (pp < 0) {
                    pp = 0;
                    jQuery(this).val(1);
                }
                _this.fnPageChange(pp, true);
            });

            //***

            __trigger_resize();
        }
    });

    //jQuery(data_table)

    jQuery("#advanced-table_paginate").on("click", "a", function () {
        //var info = table.page.info();
        //*** if remove next row - checked posts will be stay checked even after page changing
        wpbe_checked_posts = [];

    });


    //https://stackoverflow.com/questions/5548893/jquery-datatables-delay-search-until-3-characters-been-typed-or-a-button-clicke
    jQuery(".dataTables_filter input")
            .off()
            .on('keyup change', function (e) {
                if (e.keyCode == 13/* || this.value == ""*/) {
                    data_table.search(this.value).draw();
                }
            });

    //to left/right scroll buttons init


}


function init_data_tables_edit(post_id = 0) {

    if (post_id === 0) {
        //for multi-select drop-downs - disabled as take a lot of resources while loading page
        //replaced to init by wpbe_multi_select_onmouseover(this)
        if (jQuery('.wpbe_data_select').length) {
            if (jQuery("#advanced-table .chosen-select").length) {
                //jQuery("#advanced-table .chosen-select").chosen(/*{disable_search_threshold: 10}*/);
            }
        }

    }

    //***

    if (wpbe_settings.load_switchers) {
        wpbe_init_switchery(true, post_id);
    }

    __manipulate_by_depend_buttons();
    __wpbe_action_will_be_applied_to();
}

var wpbe_clicked_textinput_prev = [];//flag to track opened textinputs and close them
function wpbe_click_textinput(_this, colIndex) {

    if (jQuery(_this).find('.editable_data').length > 0) {
        return false;
    }

    if (!jQuery(_this).hasClass('editable')) {
        return false;
    }

    //***
    //lest close previous opened any textinput/area
    wpbe_close_prev_textinput();
    wpbe_clicked_textinput_prev = [_this, colIndex];

    //***
    /*
     if (jQuery(_this).hasClass('textinput_url')) {
     var content = jQuery(_this).html();
     } else {
     var content = jQuery(_this).find('a').html();
     }
     */
    var content = jQuery(_this).html();

    //***

    var post_id = jQuery(_this).parents('tr').data('post-id');
    //var edit_view = jQuery(_this).data('editable-view');


    if (jQuery(_this).find('.info_restricked').length > 0) {
        return;
    }

    //***
    //fix to avoid editing titles of variable posts
    if (jQuery(_this).data('editable-view') == 'textinput' && jQuery(_this).data('field') == 'post_title') {
        if (jQuery(_this).parents('tr').hasClass('post_type_variation')) {
            return;
        }
    }

    //***

    var input_type = 'text';

    if (jQuery(_this).data('sanitize') == 'intval' || jQuery(_this).data('sanitize') == 'floatval') {
        //console.log(content = content.replace(/\,/g, ""))
        input_type = 'number';
    }

    //inserting input into td cell
    if (input_type == 'text') {
        jQuery(_this).html('<textarea class="form-control input-sm editable_data">' + content + '</textarea>');
    } else {
        jQuery(_this).html('<input type="' + input_type + '" value="' + content + '" class="form-control input-sm editable_data" />');
    }

    var v = jQuery(_this).find('.editable_data').val();//set focus to the end
    jQuery(_this).find('.editable_data').focus().val("").val(v).select();

    wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());

    //***

    jQuery(_this).find('.editable_data').on('keydown', function (e) {

        var input = this;
        //38 - up, 40 - down, 13 - enter, 18 - ALT
        if (jQuery.inArray(e.keyCode, [13/*, 18*/, 38, 40]) > -1) { // keyboard keys
            e.preventDefault();

            if (content !== jQuery(input).val()) {
                wpbe_message(lang.saving, '');
                jQuery(_this).html(jQuery(input).val());
                jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'wpbe_update_page_field',
			wpbe_nonce: wpbe_field_update_nonce,
                        post_id: post_id,
                        field: jQuery(_this).data('field'),
                        value: jQuery(input).val()
                    },
                    success: function (answer) {
                        //console.log(answer);
                        /*
                         if (jQuery(_this).hasClass('textinput_url')) {
                         answer = '<a href="' + answer + '" title="' + answer + '" class="zebra_tips1" target="_blank">' + answer + '</a>';
                         wpbe_init_tips(jQuery(_this).find('.zebra_tips1'));
                         }
                         */
                        //***

                        jQuery(_this).html(answer);
                        wpbe_message(lang.saved, 'notice');
                        wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());

                        //fix for stock_quantity + manage_stock
                        if (jQuery(_this).data('field') == 'stock_quantity') {
                            wpbe_redraw_table_row(jQuery('#post_row_' + post_id));
                        }

                        jQuery('.wpbe_num_rounding').val(0);
                        jQuery(document).trigger('wpbe_page_field_updated', [post_id, jQuery(_this).data('field'), jQuery(input).val()]);
                    }
                });
            } else {
                jQuery(_this).html(content);
                wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());
            }

            //***
            //lets set focus to textinput under if its exists
            var col = jQuery(_this).data('col-num');
            switch (e.keyCode) {
                case 38:
                    //case 18://alt
                    //keys alt or up
                    if (jQuery(_this).closest('tr').prev('tr').length > 0) {
                        var prev_tr = jQuery(_this).closest('tr').prev('tr');
                    } else {
                        var prev_tr = jQuery(_this).closest('tbody').find('tr:last-child');
                    }
                    var c = jQuery(_this).closest('tbody').find('tr').length;
                    while (true) {
                        if (c < 0) {
                            break;
                        }
                        if (jQuery(prev_tr).find("td.editable[data-col-num='" + col + "']").length > 0) {
                            jQuery(prev_tr).find("td.editable[data-col-num='" + col + "']").trigger('click');
                            break;
                        }

                        if (jQuery(prev_tr).prev('tr').length) {
                            prev_tr = jQuery(prev_tr).prev('tr');
                        } else {
                            prev_tr = jQuery(_this).closest('tbody').find('tr:last-child');
                        }

                        c--;
                    }
                    wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());
                    break;

                default:
                    //13,40
                    //keys ENTER or down
                    if (jQuery(_this).closest('tr').next('tr').length > 0) {
                        var next_tr = jQuery(_this).closest('tr').next('tr');
                    } else {
                        var next_tr = jQuery(_this).closest('tbody').find('tr:first-child');
                    }
                    var c = jQuery(_this).closest('tbody').find('tr').length;
                    while (true) {
                        if (c < 0) {
                            break;
                        }
                        if (jQuery(next_tr).find("td.editable[data-col-num='" + col + "']").length > 0) {
                            jQuery(next_tr).find("td.editable[data-col-num='" + col + "']").trigger('click');
                            break;
                        }

                        if (jQuery(next_tr).next('tr').length) {
                            next_tr = jQuery(next_tr).next('tr');
                        } else {
                            next_tr = jQuery(_this).closest('tbody').find('tr:first-child');
                        }

                        c--;
                    }
                    wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());
                    break;
            }


            //***

            return false;
        }
        if (e.keyCode === 27) { // esc
            jQuery(_this).html(content);
            wpbe_th_width_synhronizer(colIndex, jQuery(_this).width());
        }

    });

}

//if we have opened textinput and clcked another cell - previous textinput should be closed!!
function wpbe_close_prev_textinput() {

    if (wpbe_clicked_textinput_prev.length) {
        var prev = wpbe_clicked_textinput_prev[0];

        if (jQuery(prev).find('input').length) {
            //jQuery(prev).html(jQuery(prev).find('input').val());
            jQuery(prev).find('input').trigger(jQuery.Event('keydown', {keyCode: 27}));
        } else {
            //jQuery(prev).html(jQuery(prev).find('textarea').val());
            jQuery(prev).find('textarea').trigger(jQuery.Event('keydown', {keyCode: 27}));
        }

        wpbe_th_width_synhronizer(wpbe_clicked_textinput_prev[1], jQuery(prev).width());
    }

    return true;
}


function wpbe_click_checkbox(_this, numcheck) {

    var post_id = parseInt(numcheck, 10);
    var field = numcheck.replace(post_id + '_', '');
    var value = jQuery(_this).data('val-false');
    var label = jQuery(_this).data('false');

    var is = jQuery(_this).is(':checked');
    if (is) {
        value = jQuery(_this).data('val-true');
        label = jQuery(_this).data('true');
    }

    //***

    jQuery(_this).parent().find('label').text(label);

    //***

    wpbe_message(lang.saving, 'warning');
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_update_page_field',
	    wpbe_nonce: wpbe_field_update_nonce,
            post_id: post_id,
            field: field,
            value: value
        },
        success: function () {
            jQuery(document).trigger('wpbe_page_field_updated', [post_id, field, is]);
            jQuery(this).trigger("check_changed", [_this, field, is, value, numcheck]);
            wpbe_message(lang.saved, 'notice');
        }
    });

    return true;
}

//when appearing dynamic textinput in the table cell - column head <th> should has the same width!!
function wpbe_th_width_synhronizer(colIndex, width) {
    //jQuery('#advanced-table_wrapper thead').find('th').eq(colIndex).width(width);
    //jQuery('#advanced-table_wrapper tfoot').find('th').eq(colIndex).width(width);
    //__trigger_resize();//conflict with calculator
}



function wpbe_act_tax_popup(_this) {

    jQuery('#taxonomies_popup .wpbe-modal-title').html(jQuery(_this).data('name') + ' [' + jQuery(_this).data('key') + ']');
    //fix to avoid not popup opening after taxonomies button clicking
    wpbe_popup_clicked = jQuery(_this);

    //***

    var post_id = jQuery(_this).data('post-id');
    var key = jQuery(_this).data('key');//tax key
    var checked_terms_ids = [];

    if (jQuery(_this).data('terms-ids').toString().length > 0) {

        checked_terms_ids = jQuery(_this).data('terms-ids').toString().split(',');

        checked_terms_ids = checked_terms_ids.map(function (x) {
            return parseInt(x, 10);
        });
    }

    //lets build terms tree
    jQuery('#taxonomies_popup_list').html('');
    if (Object.keys(taxonomies_terms[key]).length > 0) {
        __wpbe_fill_terms_tree(checked_terms_ids, taxonomies_terms[key]);
    }

    jQuery('.quick_search_element').show();
    jQuery('.quick_search_element_container').show();
    jQuery('#taxonomies_popup').show();

    //***

    jQuery('.wpbe-modal-save1').off('click');
    jQuery('.wpbe-modal-save1').on('click', function () {
        jQuery('#taxonomies_popup').hide();
        var checked_ch = jQuery('#taxonomies_popup_list').find('input:checked');
        var checked_terms = [];

        jQuery(_this).find('ul').html('');

        if (checked_ch.length) {
            jQuery(checked_ch).each(function (i, ch) {
                checked_terms.push(jQuery(ch).val());
                jQuery(_this).find('ul').append('<li class="wpbe_li_tag">' + jQuery(ch).parent().find('label.wpbe_term_label').text() + '</li>');
            });
        } else {
            jQuery(_this).find('ul').append('<li class="wpbe_li_tag">' + lang.no_items + '</li>');
        }

        //***

        jQuery(_this).data('terms-ids', checked_terms.join());

        //***

        wpbe_message(lang.saving, 'warning');
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_update_page_field',
		wpbe_nonce: wpbe_field_update_nonce,
                post_id: post_id,
                field: key,
                value: checked_terms
            },
            success: function () {
                jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, checked_terms]);
                wpbe_message(lang.saved, 'notice');
            }
        });
    });

    jQuery('.wpbe-modal-close1').off('click');
    jQuery('.wpbe-modal-close1').on('click', function () {
        jQuery('#taxonomies_popup').hide();
    });


    //***
    //terms quick search
    jQuery('#term_quick_search').off('keyup');
    jQuery('#term_quick_search').val('');
    jQuery('#term_quick_search').focus();
    jQuery('#term_quick_search').on('keyup', function () {
        var val = jQuery(this).val();
        if (val.length > 0) {
            setTimeout(function () {
                jQuery('.quick_search_element_container').show();

                jQuery('.quick_search_element_container').each(function (i, item) {
                    if (!(jQuery(item).parent().data('search-value').toString().indexOf(val.toLowerCase()) + 1)) {
                        jQuery(item).hide();
                    } else {
                        jQuery(item).show();
                    }
                });


                jQuery('.quick_search_element_container:not(:hidden)').each(function (i, item) {
                    jQuery(item).parents('li').children('.quick_search_element_container').show();
                });


            }, 250);
        } else {
            jQuery('.quick_search_element_container').show();
        }

        return true;
    });

    //***
    jQuery('#taxonomies_popup_list_checked_only').off('click');
    jQuery('#taxonomies_popup_list_checked_only').prop('checked', false);
    jQuery('#taxonomies_popup_list_checked_only').on('click', function () {
        check_popup_list_checked_only(this);
    });

    function check_popup_list_checked_only(_this) {
        if (jQuery(_this).is(':checked')) {

            jQuery('#taxonomies_popup_list li.top_quick_search_element').each(function (i, item) {
                if (!jQuery(item).find('input:checked').length) {
                    jQuery(item).hide();
                } else {
                    jQuery(item).show();
                    jQuery(item).find('li').each(function (ii, it) {
                        if (!jQuery(it).find('ul.wpbe_child_taxes').length && !jQuery(it).find('input:checked').length) {
                            jQuery(it).hide();
                        }
                    });
                }
            });

        } else {
            jQuery('#taxonomies_popup_list li').show();
        }

        return true;
    }

    //***


    jQuery('#taxonomies_popup_select_all_terms').off('click');
    jQuery('#taxonomies_popup_select_all_terms').prop('checked', false);
    jQuery('#taxonomies_popup_select_all_terms').on('click', function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#taxonomies_popup_list li input[type="checkbox"]').prop('checked', true);
        } else {
            jQuery('#taxonomies_popup_list li input[type="checkbox"]').prop('checked', false);
        }
        check_popup_list_checked_only(jQuery('#taxonomies_popup_list_checked_only'));
    });

    //***

    jQuery('.wpbe_create_new_term').off('click');
    jQuery('.wpbe_create_new_term').on('click', function () {
        __wpbe_create_new_term(key, true, '', _this);
        return false;
    });
    //update terms
    jQuery('.edit_tax_terms').off('click');
    jQuery('.edit_tax_terms').on('click', function(e){
	var term_id = jQuery(this).data('term_id');
	if (!term_id ) {
	    return false;
	}
	__wpbe_update_tax_term(key, term_id, _this);
    }); 
    //delete terms
    jQuery('.delete_tax_terms').off('click');
    jQuery('.delete_tax_terms').on('click', function(e){
	var term_id = jQuery(this).data('term_id');
	if (!term_id ) {
	    return false;
	}
	__wpbe_delete_tax_term(key, term_id);
    });     
        
    return true;
}
function __wpbe_delete_tax_term(tax_key, term_id) {
    if (typeof taxonomies_terms[tax_key] == 'undefined') {
	return false;
    }
    if (!confirm(lang.sure)) {
	return false;
    }

    wpbe_message(lang.delete, 'warning', 99999);
    jQuery.ajax({
	method: "POST",
	url: ajaxurl,
	data: {
	    action: 'wpbe_delete_tax_term',
	    term_id: term_id,
	    tax_key: tax_key,
	    wpbe_nonce: wpbe_field_update_nonce,
	},
	success: function (response) {

	    response = JSON.parse(response);
	    
	    jQuery('input#term_' + term_id).parent('.quick_search_element_container').parent('li.quick_search_element').remove();

	    if (response.length > 0) {
		wpbe_message(lang.deleted, 'notice');
		taxonomies_terms[tax_key] = response;

		jQuery(document).trigger("taxonomy_data_redrawn", [tax_key, response.term_id]);
	    } else {
		wpbe_message(lang.error + ' ' + lang.term_maybe_exist, 'error');
	    }
	}
    });
    //***
    jQuery('.wpbe-modal-close9').trigger('click');
}
function __wpbe_update_tax_term(tax_key, term_id, popup) {
    if (typeof taxonomies_terms[tax_key] == 'undefined') {
	return false;
    }
    var show_parent = true;
    var current_term = {};
    var current_index = -1;

    current_term = __wpbe_recursive_search(taxonomies_terms[tax_key], term_id);

    if (!Object.keys(current_term).length) {
	return false;
    }

    jQuery('#wpbe_new_term_popup .wpbe-modal-title span').html(tax_key);
    jQuery('#wpbe_new_term_title').val(current_term.name);
    jQuery('#wpbe_new_term_slug').val(current_term.slug);
    jQuery('#wpbe_new_term_description').val(current_term.desc);   
    if (show_parent ) {
        jQuery('#wpbe_new_term_parent').parents('.wpbe-form-element-container').show();

        jQuery('#wpbe_new_term_parent').val('');
        jQuery('#wpbe_new_term_parent').html('');

        if (Object.keys(taxonomies_terms[tax_key]).length > 0) {
            jQuery('#wpbe_new_term_parent').append('<option value="-1">' + lang.none + '</option>');
            __wpbe_fill_select('wpbe_new_term_parent', taxonomies_terms[tax_key],[current_term.parent]);
        }

        //***

        jQuery('#wpbe_new_term_parent').chosen({
            //disable_search_threshold: 10,
            width: '100%'
        }).trigger("chosen:updated");
    } else {
        jQuery('#wpbe_new_term_parent').parents('.wpbe-form-element-container').hide();
    }
    
    jQuery('#wpbe_new_term_popup').show();

    jQuery('.wpbe-modal-close9').on('click', function () {
        jQuery('#wpbe_new_term_popup').hide();
    });   
    //***
    jQuery('#wpbe_new_term_create').off('click');
    jQuery('#wpbe_new_term_create').on('click', function () {
        var title = jQuery('#wpbe_new_term_title').val();
        var slug = jQuery('#wpbe_new_term_slug').val();
        var parent = jQuery('#wpbe_new_term_parent').val();
	var description = jQuery('#wpbe_new_term_description').val();
	
        if (title.length > 0) {
            wpbe_message(lang.creating, 'warning', 99999);
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_tax_term',
		    term_id: term_id,
                    tax_key: tax_key,
                    title: title,
                    slug: slug,
		    description: description,
                    parent: parent,
		    wpbe_nonce: wpbe_field_update_nonce,
                },
                success: function (response) {

                    response = JSON.parse(response);
		    

                    if (response.length > 0) {
                        wpbe_message(lang.created, 'notice');
                        taxonomies_terms[tax_key] = response;
			//redraw popup
			jQuery('.wpbe-modal-close1').trigger('click');
			jQuery(popup).trigger('click');
			

                        jQuery(document).trigger("taxonomy_data_redrawn", [tax_key, response.term_id]);
			

                    } else {
                        wpbe_message(lang.error + ' ' + lang.term_maybe_exist, 'error');
                    }

                }
            });

            //***

            jQuery('.wpbe-modal-close9').trigger('click');
        }

        return false;
    });   
}
function __wpbe_recursive_search(terms, term_id){
    var current_val = {};
    jQuery(terms).each(function (i, d) {
	if (d.term_id == term_id) {
	    current_val = d;
	    return false;
	}
	if(d.childs.length) {
	    current_val = __woobe_recursive_search(d.childs, term_id);
	    if (Object.keys(current_val).length) {
		return false;
	    }
	    
	}
    });
    return current_val;
}
function __wpbe_create_new_term(tax_key, show_parent = true, select_id = '', popup = null) {
    jQuery('#wpbe_new_term_popup .wpbe-modal-title span').html(tax_key);

    jQuery('#wpbe_new_term_title').val('');
    jQuery('#wpbe_new_term_slug').val('');


    if (show_parent) {
        jQuery('#wpbe_new_term_parent').parents('.wpbe-form-element-container').show();

        jQuery('#wpbe_new_term_parent').val('');
        jQuery('#wpbe_new_term_parent').html('');

        if (Object.keys(taxonomies_terms[tax_key]).length > 0) {
            jQuery('#wpbe_new_term_parent').append('<option value="-1">' + lang.none + '</option>');
            __wpbe_fill_select('wpbe_new_term_parent', taxonomies_terms[tax_key]);
        }

        //***

        jQuery('#wpbe_new_term_parent').chosen({
            //disable_search_threshold: 10,
            width: '100%'
        }).trigger("chosen:updated");
    } else {
        jQuery('#wpbe_new_term_parent').parents('.wpbe-form-element-container').hide();
    }


    jQuery('#wpbe_new_term_popup').show();

    jQuery('.wpbe-modal-close9').on('click', function () {
        jQuery('#wpbe_new_term_popup').hide();
    });

    //***
    jQuery('#wpbe_new_term_create').off('click');
    jQuery('#wpbe_new_term_create').on('click', function () {
        var title = jQuery('#wpbe_new_term_title').val();
        var slug = jQuery('#wpbe_new_term_slug').val();
        var parent = jQuery('#wpbe_new_term_parent').val();

        if (title.length > 0) {
            wpbe_message(lang.creating, 'warning', 99999);
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_create_new_term',
                    tax_key: tax_key,
                    titles: title,
                    slugs: slug,
                    parent: parent,
		    wpbe_nonce: wpbe_field_update_nonce,
                },
                success: function (response) {
                    response = JSON.parse(response);

                    if (response.terms_ids.length > 0) {
                        wpbe_message(lang.created, 'notice');
                        taxonomies_terms[tax_key] = response.terms;

                        for (var i = 0; i < response.terms_ids.length; i++) {

                            var li = jQuery('#taxonomies_popup_list_li_tpl').html();
                            li = li.replace(/__TERM_ID__/gi, response.terms_ids[i]);
                            li = li.replace(/__LABEL__/gi, response.titles[i]);
                            li = li.replace(/__SEARCH_TXT__/gi, response.titles[i].toLowerCase());
                            li = li.replace(/__CHECK__/gi, 'checked');
                            if (parent == 0) {
                                li = li.replace(/__TOP_LI__/gi, 'top_quick_search_element');
                            } else {
                                li = li.replace(/__TOP_LI__/gi, '');
                            }
                            li = li.replace(/__CHILDS__/gi, '');
                            jQuery('#taxonomies_popup_list').prepend(li);
			    if (popup) {
				jQuery('#taxonomies_popup_list').find('.edit_tax_terms[data-term_id='+ response.terms_ids[i] +']').on('click', function(){
				    var term_id = jQuery(this).data('term_id');
				    if (!term_id ) {
					return false;
				    }
				    __wpbe_update_tax_term(tax_key, term_id, popup);
				});
				jQuery('#taxonomies_popup_list').find('.delete_tax_terms[data-term_id='+ response.terms_ids[i] +']').on('click', function(){
				    var term_id = jQuery(this).data('term_id');
				    if (!term_id ) {
					return false;
				    }
				    __wpbe_delete_tax_term(tax_key, term_id);
				});				
				
			    }
                        }

                        //***
                        //if we working with any drop-down
                        if (select_id.length > 0) {
                            for (var i = 0; i < response.terms_ids.length; i++) {
                                jQuery('#' + select_id).prepend('<option selected value="' + response.terms_ids[i] + '">' + response.titles[i] + '</option>');
                            }

                            //***

                            jQuery(jQuery('#' + select_id)).chosen({
                                width: '100%'
                            }).trigger("chosen:updated");
                        }

                        //***
                        //lets all WOLF extensions knows about this event
                        jQuery(document).trigger("taxonomy_data_redrawn", [tax_key, response.term_id]);
                    } else {
                        wpbe_message(lang.error + ' ' + lang.term_maybe_exist, 'error');
                    }

                }
            });

            //***

            jQuery('.wpbe-modal-close9').trigger('click');
        }

        return false;
    });

}


//service function to create terms tree in taxonomies popup
function __wpbe_fill_terms_tree(checked_terms_ids, data, parent_term_id = 0) {

    var li_tpl = jQuery('#taxonomies_popup_list_li_tpl').html();

    //***

    jQuery(data).each(function (i, d) {
        var li = li_tpl;
        li = li.replace(/__TERM_ID__/gi, d.term_id);
        li = li.replace(/__LABEL__/gi, d.name);
        li = li.replace(/__SEARCH_TXT__/gi, d.name.toLowerCase());
        li = li.replace(/__DESC__/gi, d.desc);
        if (jQuery.inArray(d.term_id, checked_terms_ids) > -1) {
            li = li.replace(/__CHECK__/gi, 'checked');
        } else {
            li = li.replace(/__CHECK__/gi, '');
        }

        if (parent_term_id == 0) {
            li = li.replace(/__TOP_LI__/gi, 'top_quick_search_element');
        } else {
            li = li.replace(/__TOP_LI__/gi, '');
        }

        //***

        if (Object.keys(d.childs).length > 0) {
            li = li.replace(/__CHILDS__/gi, '<ul class="wpbe_child_taxes wpbe_child_taxes_' + d.term_id + '"></ul>');
        } else {
            li = li.replace(/__CHILDS__/gi, '');
        }

        //***

        if (parent_term_id == 0) {
            jQuery('#taxonomies_popup_list').append(li);
        } else {
            jQuery('#taxonomies_popup_list .wpbe_child_taxes_' + parent_term_id).append(li);
        }


        if (d.childs) {
            __wpbe_fill_terms_tree(checked_terms_ids, d.childs, d.term_id);
        }
    });

}

//use direct call only instead of attaching event to each element after page loading
//to up performance when a lot of post per page
function wpbe_act_popupeditor(_this, post_parent) {

    jQuery('#popupeditor_popup .wpbe-modal-title').text(jQuery(_this).data('name') + ' [' + jQuery(_this).data('key') + ']');
    wpbe_popup_clicked = jQuery(_this);
    var post_id = jQuery(_this).data('post_id');
    var key = jQuery(_this).data('key');
    var use_wp_editor = jQuery(_this).data('wp_editor');
    var edit_link = jQuery(_this).data('post_edit_link');
    //***
    if (use_wp_editor) {
	jQuery('#popupeditor_gutemberg_popup .wpbe-form-element-container').html('<iframe class="" src="'+edit_link+'&wpbe_popup_editor=1" style="width: 100%; height: 700px;"></iframe>');
	jQuery('#popupeditor_gutemberg_popup').show();
	jQuery('.wpbe-modal-close2').off('click');
	jQuery('.wpbe-modal-close2').on('click', function () {
	    jQuery('#popupeditor_gutemberg_popup').hide();
	});

	jQuery('.wpbe-modal-save3').off('click');
	jQuery('.wpbe-modal-save3').on('click', function () {
	    if (typeof document.querySelector("#popupeditor_gutemberg_popup iframe").contentWindow.wpbe_popup_editor_get_content == 'undefined') {
		return false;
	    }
	    var content = document.querySelector("#popupeditor_gutemberg_popup iframe").contentWindow.wpbe_popup_editor_get_content();
	    console.log(content);
	    jQuery('#popupeditor_gutemberg_popup').hide();
	    wpbe_message(lang.saving, 'warning');
	    jQuery.ajax({
		method: "POST",
		url: ajaxurl,
		data: {
		    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
		    post_id: post_id,
		    field: key,
		    value: content
		},
		success: function (content) {
		    jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, content]);

		    if (jQuery(_this).data('text-title')) {
			let this_row = jQuery(_this).parents('tr');
			wpbe_redraw_table_row(this_row);
		    }

		    wpbe_message(lang.saved, 'notice');
		}
	    });	
	});   
	return false;
    }
    

    wpbe_message(lang.loading, 'warning');
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_get_post_field',
            post_id: post_id,
            field: key,
            post_parent: post_parent
        },
        success: function (content) {

            wpbe_message('', 'clean');

            jQuery('#popupeditor_popup').show();

            if (typeof tinyMCE != 'undefined') {
                try {
                    tinyMCE.get('popupeditor').setContent(content);
                    jQuery('.wp-editor-area').val(content);
                } catch (e) {
                    //fix if editor loaded not in rich mode
                    jQuery('.wp-editor-area').val(content);
                }
            }

            wpbe_message(lang.loaded, 'notice');
        }
    });

    //***

    jQuery('.wpbe-modal-save2').off('click');
    jQuery('.wpbe-modal-save2').on('click', function () {

        var post_id = wpbe_popup_clicked.data('post_id');
        var key = wpbe_popup_clicked.data('key');

        jQuery('#popupeditor_popup').hide();
        wpbe_message(lang.saving, 'warning');

        var content = '';

        //fix if editor loaded not in rich mode
        if (jQuery('.wp-editor-area').css('display') === 'none') {
            try {
                content = tinyMCE.get('popupeditor').getContent();
            } catch (e) {
                content = jQuery('.wp-editor-area').val();
            }
        } else {
            content = jQuery('.wp-editor-area').val();
        }

        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_update_page_field',
		wpbe_nonce: wpbe_field_update_nonce,
                post_id: post_id,
                field: key,
                value: content
            },
            success: function (content) {
                jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, content]);

                if (jQuery(_this).data('text-title')) {
                    let this_row = jQuery(_this).parents('tr');
                    wpbe_redraw_table_row(this_row);
                }

                wpbe_message(lang.saved, 'notice');
            }
        });
    });

    jQuery('.wpbe-modal-close2').off('click');
    jQuery('.wpbe-modal-close2').on('click', function () {
        jQuery('#popupeditor_popup').hide();
    });


}

function wpbe_act_gallery_editor(_this) {
    var button = _this;

    jQuery('#gallery_popup_editor .wpbe-modal-title').html(jQuery(_this).data('name') + ' [' + jQuery(_this).data('key') + ']');
    wpbe_popup_clicked = jQuery(_this);
    var post_id = parseInt(jQuery(_this).data('post_id'), 10);
    var key = jQuery(_this).data('key');

    //***

    if (jQuery(_this).data('count') > 0) {
        if (post_id > 0) {

            var html = '';
            jQuery(jQuery(_this).data('images')).each(function (i, a) {
                var li_html = jQuery('#wpbe_gallery_li_tpl').html();
                li_html = li_html.replace(/__IMG_URL__/gi, a.url);
                li_html = li_html.replace(/__ATTACHMENT_ID__/gi, a.id);
                li_html = li_html.replace(/__KEY__/gi, key);
                html += li_html;
            });

            jQuery('#gallery_popup_editor form').html('<ul class="wpbe_fields_tmp">' + html + '</ul>');
            jQuery('#gallery_popup_editor').show();
            jQuery('#wpbe_gallery_bulk_operations').hide();
            __wpbe_init_gallery(key);
        } else {
            //we can use such button for any another extensions
            jQuery('#gallery_popup_editor').show();
            jQuery('#wpbe_gallery_bulk_operations').show();
            __wpbe_init_gallery(key);
        }

    } else {
        if (post_id > 0) {
            jQuery('#gallery_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
            jQuery('#wpbe_gallery_bulk_operations').hide();
        } else {
            //this we need do for another applications, for example bulk editor
            if (jQuery('#gallery_popup_editor form .wpbe_fields_tmp').length == 0) {
                jQuery('#gallery_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
            }
            jQuery('#wpbe_gallery_bulk_operations').show();
        }


        jQuery('#gallery_popup_editor').show();
        __wpbe_init_gallery(key);
    }


    //***


    jQuery('.wpbe-modal-save4').off('click');
    jQuery('.wpbe-modal-save4').on('click', function () {

        var post_id = wpbe_popup_clicked.data('post_id');
        var key = wpbe_popup_clicked.data('key');

        if (post_id > 0) {
            jQuery('#gallery_popup_editor').hide();
            wpbe_message(lang.saving, 'warning');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
                    post_id: post_id,
                    field: key,
                    field_type: 'gallery_popup_editor',
                    value: jQuery('#posts_gallery_form').serialize()
                },
                success: function (html) {

                    wpbe_message(lang.saved, 'notice');
                    //jQuery('#gallery_popup_editor form').html('');
                    jQuery(button).parent().html(html);

                    jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, jQuery('#posts_gallery_form').serialize()]);
                }
            });
        } else {
            //for gallery buttons in any extensions
            jQuery(document).trigger('wpbe_act_gallery_editor_saved', [post_id, key, jQuery('#posts_gallery_form').serialize()]);
        }


    });

    jQuery('.wpbe-modal-close4').off('click');
    jQuery('.wpbe-modal-close4').on('click', function () {
        //jQuery('#gallery_popup_editor form').html(''); - do not do this, as it make incompatibility with another extensions
        jQuery('#gallery_popup_editor').hide();
    });

    return false;
}


function wpbe_act_upsells_editor(_this) {
    var button = _this;

    jQuery('#upsells_popup_editor .wpbe-modal-title').html(jQuery(_this).data('name') + ' [' + jQuery(_this).data('key') + ']');
    wpbe_popup_clicked = jQuery(_this);
    var post_id = parseInt(jQuery(_this).data('post_id'), 10);
    var key = jQuery(_this).data('key');

    //***

    var button_data = [];

    if (jQuery('#upsell_ids_upsell_ids_' + post_id + ' li').length > 0) {
        jQuery('#upsell_ids_upsell_ids_' + post_id + ' li').each(function (i, li) {
            button_data.push(jQuery(li).data(wpbe_current_post_type));
        });
    }

    //***

    if (jQuery(_this).data('count') > 0 && post_id > 0) {

        var html = '';
        jQuery(button_data).each(function (i, li) {
            var li_html = jQuery('#wpbe_post_li_tpl').html();
            li_html = li_html.replace(/__ID__/gi, li.id);
            li_html = li_html.replace(/__TITLE__/gi, li.title + ' (#' + li.id + ')');
            li_html = li_html.replace(/__PERMALINK__/gi, li.link);
            li_html = li_html.replace(/__IMG_URL__/gi, li.thumb);
            html += li_html;
        });

        jQuery('#upsells_popup_editor form').html('<ul class="wpbe_fields_tmp">' + html + '</ul>');
        jQuery("#upsells_posts_search").val('');
        jQuery('#upsells_popup_editor').show();
        jQuery('#wpbe_upsells_bulk_operations').hide();
        __wpbe_init_upsells();

    } else {
        jQuery("#upsells_posts_search").val('');
        if (post_id > 0) {
            jQuery('#upsells_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
            jQuery('#wpbe_upsells_bulk_operations').hide();
        } else {
            //this we need do for another applications, for example bulk editor
            if (jQuery('#upsells_popup_editor form .wpbe_fields_tmp').length == 0) {
                jQuery('#upsells_popup_editor form').html('<ul class="wpbe_fields_tmp"></ul>');
            }
            jQuery('#wpbe_upsells_bulk_operations').show();
        }

        jQuery('#upsells_popup_editor').show();
        __wpbe_init_upsells();
    }

    //***


    jQuery('.wpbe-modal-save5').off('click');
    jQuery('.wpbe-modal-save5').on('click', function () {

        var post_id = wpbe_popup_clicked.data('post_id');
        var key = wpbe_popup_clicked.data('key');

        if (post_id > 0) {
            jQuery('#upsells_popup_editor').hide();
            wpbe_message(lang.saving, 'warning');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
                    post_id: post_id,
                    field: key,
                    value: jQuery('#posts_upsells_form').serialize()
                },
                success: function (html) {

                    wpbe_message(lang.saved, 'notice');
                    //jQuery('#upsells_popup_editor form').html('');
                    jQuery(button).parent().html(html);

                    jQuery(document).trigger('wpbe_page_field_updated', [post_id, key, jQuery('#posts_upsells_form').serialize()]);
                }
            });
        } else {
            //for buttons in any extensions
            jQuery(document).trigger('wpbe_act_upsells_editor_saved', [post_id, key, jQuery('#posts_upsells_form').serialize()]);
        }

        return false;
    });

    jQuery('.wpbe-modal-close5').off('click');
    jQuery('.wpbe-modal-close5').on('click', function () {
        //jQuery('#upsells_popup_editor form').html(''); - do not do this, as it make incompatibility with another extensions
        jQuery("#upsells_posts_search").val('');
        jQuery('#upsells_popup_editor').hide();
        return false;
    });

}


function wpbe_act_select(_this) {
    wpbe_message(lang.saving, '');
    var post_id = parseInt(jQuery(_this).data('post-id'), 10);

    if (jQuery(_this).data('field') == 'post_type') {
        //redraw table row
        wpbe_redraw_table_row(_this);
    } else {
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_update_page_field',
		wpbe_nonce: wpbe_field_update_nonce,
                post_id: post_id,
                field: jQuery(_this).data('field'),
                value: jQuery(_this).val()
            },
            success: function () {
                jQuery(document).trigger('wpbe_page_field_updated', [post_id, jQuery(_this).data('field'), jQuery(_this).val()]);
                wpbe_message(lang.saved, 'notice');
            }
        });
    }

    return false;

}

function wpbe_redraw_table_row(row, do_trigger = true) {
    var post_id = parseInt(jQuery(row).data('post-id'), 10);

    if (!post_id) {
        return;
    }

    //***

    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'wpbe_redraw_table_row',
            post_id: post_id,
            field: jQuery(row).data('field'),
            value: jQuery(row).val()
        },
        success: function (row_data) {


            wpbe_message(lang.saved, 'notice');
            var tr_index = jQuery('#post_row_' + post_id).data('row-num');
            if (row_data) {
                data_table.row(tr_index).data(JSON.parse(row_data));
            }


            jQuery.each(jQuery('td', jQuery('#post_row_' + post_id)), function (colIndex) {
                if (jQuery(this).find('.info_restricked').length > 0 || jQuery(this).data('field') === 'ID') {
                    jQuery(this).removeClass('editable');
                } else {
                    jQuery(this).addClass('editable');
                }
            });

            //***
            if (do_trigger) {
                jQuery(document).trigger('wpbe_page_field_updated', [post_id, jQuery(row).data('field'), jQuery(row).val()]);
            }
            //wpbe_checked_posts.splice(wpbe_checked_posts.indexOf(post_id), 1);
            /*
             wpbe_checked_posts = jQuery.grep(wpbe_checked_posts, function (value) {
             return value != post_id;
             });
             */

            if (jQuery.inArray(post_id, wpbe_checked_posts) > -1) {
                jQuery('#post_row_' + post_id).find('.wpbe_post_check').prop('checked', true);
            }

            init_data_tables_edit(post_id);
            if (!row_data) {
                jQuery('#post_row_' + post_id).remove();
            }

        }
    });
}

function wpbe_init_calendar(calendar) {

    if (typeof jQuery(calendar).attr('data-dtp') !== typeof undefined && jQuery(calendar).attr('data-dtp') !== false) {
        return;
    }

    //***

    jQuery(calendar).bootstrapMaterialDatePicker({
        weekStart: 1,
        time: true,
        clearButton: false,
        //minDate: new Date(),
        format: 'DD/MM/YYYY HH:mm',
        autoclose: true,
        lang: 'en',
        title: jQuery(calendar).data('title'),
        icons: {
            time: "icofont icofont-clock-time",
            date: "icofont icofont-ui-calendar",
            up: "icofont icofont-rounded-up",
            down: "icofont icofont-rounded-down",
            next: "icofont icofont-rounded-right",
            previous: "icofont icofont-rounded-left"
        }
    }).on('change', function (e, date)
    {
        var hidden = jQuery('#' + jQuery(this).data('val-id'));
        if (typeof date != 'undefined') {
            var d = new Date(date);
            //hidden.val(parseInt(d.getTime() / 1000, 10));
            hidden.val(d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + '-' + d.getHours() + '-' + d.getMinutes());
        } else {
            //clear
            hidden.val(0);
        }

        //***
        var post_id = parseInt(hidden.data('post-id'), 10);
        if (post_id > 0) {
            wpbe_message(lang.saving, '');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_update_page_field',
		    wpbe_nonce: wpbe_field_update_nonce,
                    post_id: post_id,
                    field: hidden.data('key'),
                    value: hidden.val()
                },
                success: function () {
                    jQuery(document).trigger('wpbe_page_field_updated', [post_id, hidden.data('key'), hidden.val()]);
                    wpbe_message(lang.saved, 'notice');
                }
            });
        }

    });



    //***

    jQuery(calendar).parents('td').find('.wpbe_calendar_cell_clear').on('click', function () {
        jQuery(this).parent().find('.wpbe_calendar').val('').trigger('change');
        return false;
    });


}

//redrawing of checkbox to switcher on onmouseover
//was in cycle but its make time of page redrawing longer, so been remade for individual initializating
function wpbe_set_switchery(_this) {

    //http://abpetkov.github.io/switchery/
    if (typeof Switchery !== 'undefined') {
        new Switchery(_this);
        //while reinit allows more html switchers
        jQuery(_this).parent().find('span.switchery:not(:first)').remove();
    }

    //***

    jQuery(_this).off('change');
    jQuery(_this).on('change', function () {
        var state = _this.checked.toString();
        var numcheck = jQuery(_this).data('numcheck');
        var trigger_target = jQuery(_this).data('trigger-target');
        var label = jQuery("*[data-label-numcheck='" + numcheck + "']");
        var hidden = jQuery("*[data-hidden-numcheck='" + numcheck + "']");
        label.html(jQuery(_this).data(state));
        jQuery(label).removeClass(jQuery(_this).data('class-' + (!(_this.checked)).toString()));
        jQuery(label).addClass(jQuery(_this).data('class-' + state));
        var val = jQuery(_this).data('val-' + state);
        var field_name = jQuery(hidden).attr('name');
        jQuery(hidden).val(val);

        if (trigger_target.length) {
            jQuery(this).trigger("check_changed", [trigger_target, field_name, _this.checked, val, numcheck]);
        }
    });

    //***

    jQuery(_this).off('check_changed');
    jQuery(_this).on("check_changed", function (event, trigger_target, field_name, is_checked, val, post_id) {
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

function wpbe_act_thumbnail(_this) {
    var post_id = jQuery(_this).parents('tr').data('post-id');
    var field = jQuery(_this).parents('td').data('field');

    var image = wp.media({
        title: lang.upload_image,
        multiple: false,
        library: {
            type: ['image']
        }
    }).open()
            .on('select', function (e) {
                var uploaded_image = image.state().get('selection').first();
                // We convert uploaded_image to a JSON object to make accessing it easier
                uploaded_image = uploaded_image.toJSON();
                var uploaded_to = 0;
                if (uploaded_image.uploading != undefined || uploaded_image.uploading == false) {
                    uploaded_to = 1;
                }
                if (typeof uploaded_image.url != 'undefined') {
                    jQuery(_this).find('img').attr('src', uploaded_image.url);
                    //jQuery(_this).removeAttr('srcset');

                    wpbe_message(lang.saving, '');
                    jQuery.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {
                            action: 'wpbe_update_page_field',
			    wpbe_nonce: wpbe_field_update_nonce,
                            post_id: post_id,
                            field: field,
                            value: uploaded_image.id,
                            uploaded_to: uploaded_to
                        },
                        success: function () {
                            jQuery(document).trigger('wpbe_page_field_updated', [post_id, field, uploaded_image.id]);
                            wpbe_message(lang.saved, 'notice');
                        }
                    });
                }
            });


    return false;

}

//service
function __wpbe_init_downloads() {
    return false;
}

//service
function __wpbe_init_gallery(key) {

    jQuery('.wpbe_insert_gall_file').off('click');
    jQuery('.wpbe_insert_gall_file').on('click', function (e)
    {
        e.preventDefault();

        var image = wp.media({
            title: lang.upload_images,
            multiple: true,
            //cache: 'refresh',
            library: {
                type: ['image'],
                //cache: false
            }
        }).open()
                .on('select', function (e) {
                    //var uploaded_images = image.state().get('selection').first();
                    var uploaded_images = image.state().get('selection');
                    // We convert uploaded_image to a JSON object to make accessing it easier
                    uploaded_images = uploaded_images.toJSON();
                    if (uploaded_images.length) {
                        for (var i = 0; i < uploaded_images.length; i++) {
                            var html = jQuery('#wpbe_gallery_li_tpl').html();
                            html = html.replace(/__IMG_URL__/gi, uploaded_images[i]['sizes']['thumbnail']['url']);
                            html = html.replace(/__ATTACHMENT_ID__/gi, uploaded_images[i]['id']);
                            html = html.replace(/__KEY__/gi, key);
                            jQuery('#gallery_popup_editor form .wpbe_fields_tmp').prepend(html);
                        }

                        __wpbe_init_gallery(key);
                        //jQuery('#media-attachment-date-filters').trigger('change');
                    }
                });

        return false;
    });

    //***

    jQuery("#gallery_popup_editor form .wpbe_fields_tmp").sortable({
        update: function (event, ui) {
            //***
        },
        opacity: 0.8,
        cursor: "crosshair",
        //handle: '.wpbe_drag_and_drope',
        placeholder: 'wpbe-options-highlight'
    });


    //***

    jQuery('.wpbe_gall_file_delete').off('click');
    jQuery('.wpbe_gall_file_delete').on('click', function () {
        jQuery(this).parents('li').remove();
        return false;
    });


    jQuery('.wpbe_gall_file_delete_all').off('click');
    jQuery('.wpbe_gall_file_delete_all').on('click', function () {
        jQuery('#gallery_popup_editor form .wpbe_fields_tmp').html('');
        return false;
    });


}

//service

function __wpbe_init_upsells() {
    return false;
}

//service
function __wpbe_init_cross_sells() {
    return false;
}

//service
function __wpbe_init_grouped() {
    return false;
}



function wpbe_message(text, type, duration = 0) {
    jQuery('.growl').hide();
    if (duration > 0) {
        Growl.settings.duration = duration;
    } else {
        Growl.settings.duration = 1777;
    }
    switch (type) {
        case 'notice':
            jQuery.growl.notice({message: text});
            break;

        case 'warning':
            jQuery.growl.warning({message: text});
            break;

        case 'error':
            jQuery.growl.error({message: text});
            break;

        case 'clean':
            //clean
            break;

        default:
            jQuery.growl({title: '', message: text});
            break;
}

}

function wpbe_init_scroll() {
    setTimeout(function () {
        if (jQuery('#advanced-table').width() > jQuery('#tabs-posts').width() + 50) {
            jQuery('#wpbe_scroll_left').show();
            jQuery('#wpbe_scroll_right').show();

            var anchor1 = jQuery('.dataTables_scrollBody');
            var corrective = 30;
            var animate_time = 300;
            var leftPos = null;

            jQuery('#wpbe_scroll_left').on('click', function () {
                leftPos = anchor1.scrollLeft();
                jQuery('div.dataTables_scrollBody').animate({scrollLeft: leftPos + jQuery('#tabs-posts').width() - corrective}, animate_time);
                return false;
            });


            jQuery('#wpbe_scroll_right').on('click', function () {
                leftPos = anchor1.scrollLeft();
                jQuery('div.dataTables_scrollBody').animate({scrollLeft: leftPos - jQuery('#tabs-posts').width() + corrective}, animate_time);
                return false;
            });
        }

    }, 1000);
}

function wpbe_multi_select_cell_attr_visible(_this) {
    var cell_dropdown = jQuery(_this).parents('.wpbe_multi_select_cell').find('.wpbe_multi_select_cell_dropdown');
    var cell_list = jQuery(_this).parents('.wpbe_multi_select_cell').find('.wpbe_multi_select_cell_list');
    var ul = jQuery(cell_list).find('ul');
    var select = jQuery(cell_dropdown).find('select');
    var tax_key = jQuery(select).data('field');
    var post_id = jQuery(select).data('post-id');
    var selected = (jQuery(select).data('selected') + '').split(',').map(function (num) {
        return parseInt(num, 10);
    });

    var select_id = 'mselect_' + tax_key + '_' + post_id;

    jQuery(_this).hide();


    jQuery(select).chosen({
        //disable_search_threshold: 10,
        //max_shown_results: 5,
        width: '100%'
    }).trigger("chosen:updated");

    jQuery(cell_dropdown).show();

    //***

    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_cancel').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_cancel').on('click', function () {
        jQuery(select).chosen('destroy');
        jQuery(cell_dropdown).hide();
        jQuery(_this).show();
        return false;
    });

    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_select').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_select').on('click', function () {
        jQuery(select).find('option').prop('selected', true);
        jQuery(select).trigger('chosen:updated');
        return false;
    });
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_deselect').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_deselect').on('click', function () {
        jQuery(select).find('option').removeAttr('selected');
        jQuery(select).trigger('chosen:updated');
        return false;
    });


    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_save').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_save').on('click', function () {
        jQuery(select).chosen('destroy');
        wpbe_act_select(select);
        jQuery(cell_dropdown).hide();
        jQuery(_this).show();

        //***

        var sel = [];
        jQuery(ul).html('');
        if (jQuery(select).find(":selected").length) {
            jQuery(select).find(":selected").each(function (ii, option) {
                sel[ii] = option.value;
                jQuery(ul).append('<li>' + option.label + '</li>');
            });
        } else {
            jQuery(ul).append('<li>' + lang.no_items + '</li>');
        }

        jQuery(select).data('selected', sel.join(','));

        return false;
    });


    return false;
}

function wpbe_multi_select_cell(_this) {

    var cell_dropdown = jQuery(_this).parents('.wpbe_multi_select_cell').find('.wpbe_multi_select_cell_dropdown');
    var cell_list = jQuery(_this).parents('.wpbe_multi_select_cell').find('.wpbe_multi_select_cell_list');
    var ul = jQuery(cell_list).find('ul');
    var select = jQuery(cell_dropdown).find('select');
    var tax_key = jQuery(select).data('field');
    var post_id = jQuery(select).data('post-id');
    var selected = (jQuery(select).data('selected') + '').split(',').map(function (num) {
        return parseInt(num, 10);
    });

    var select_id = 'mselect_' + tax_key + '_' + post_id;

    jQuery(_this).hide();

    //***

    jQuery(select).empty();
    console.log(taxonomies_terms);
    __wpbe_fill_select(select_id, taxonomies_terms[tax_key], selected);

    //***

    jQuery(select).chosen({
        //disable_search_threshold: 10,
        //max_shown_results: 5,
        width: '100%'
    }).trigger("chosen:updated");

    jQuery(cell_dropdown).show();

    //***

    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_cancel').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_cancel').on('click', function () {
        jQuery(select).chosen('destroy');
        jQuery(cell_dropdown).hide();
        jQuery(_this).show();
        return false;
    });

    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_select').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_select').on('click', function () {
        jQuery(select).find('option').prop('selected', true);
        jQuery(select).trigger('chosen:updated');
        return false;
    });
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_deselect').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_deselect').on('click', function () {
        jQuery(select).find('option').removeAttr('selected');
        jQuery(select).trigger('chosen:updated');
        return false;
    });


    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_save').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_save').on('click', function () {
        jQuery(select).chosen('destroy');
        wpbe_act_select(select);
        jQuery(cell_dropdown).hide();
        jQuery(_this).show();

        //***

        var sel = [];
        jQuery(ul).html('');
        if (jQuery(select).find(":selected").length) {
            jQuery(select).find(":selected").each(function (ii, option) {
                sel[ii] = option.value;
                jQuery(ul).append('<li>' + option.label + '</li>');
            });
        } else {
            jQuery(ul).append('<li>' + lang.no_items + '</li>');
        }

        jQuery(select).data('selected', sel.join(','));

        return false;
    });


    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_new').off('click');
    jQuery(cell_dropdown).find('.wpbe_multi_select_cell_new').on('click', function () {

        __wpbe_create_new_term(tax_key, false, select_id);

        return false;
    });


    return false;
}

//make images bigger on their event onmouseover
function wpbe_init_image_preview(_this) {
    var xOffset = 150;
    var yOffset = 30;

    _this.t = _this.title;
    //_this.title = "";
    var c = (_this.t != "") ? "<br/>" + _this.t : "";
    jQuery("body").append("<p id='wpbe_img_preview'><img src='" + _this.href + "' alt='" + lang.loading + "' width='300' />" + c + "</p>");
    jQuery("#wpbe_img_preview")
            .css("top", (_this.pageY - xOffset) + "px")
            .css("left", (_this.pageX + yOffset) + "px")
            .fadeIn("fast");

    jQuery(_this).mousemove(function (e) {
        jQuery("#wpbe_img_preview")
                .css("top", (e.pageY - xOffset) + "px")
                .css("left", (e.pageX + yOffset) + "px");
    });

    jQuery(_this).mouseleave(function (e) {
        jQuery("#wpbe_img_preview").remove();
    });
}

//to display current post in the top wordpress admin bar
function wpbe_td_hover(id, title, col_num) {
    if (!jQuery('#wp-admin-bar-root-default li.wpbe_current_cell_view').length) {
        jQuery('#wp-admin-bar-root-default').append('<li class="wpbe_current_cell_view">');
    }

    //***

    if (id > 0) {
        var content = '#' + id + '. ' + title + ' [<i>' + jQuery('#wpbe_col_' + col_num).text() + '</i>]';
    } else {
        var content = '';
    }

    jQuery('#wp-admin-bar-root-default li.wpbe_current_cell_view').html(content);

    return true;
}


function wpbe_onmouseover_num_textinput(_this, colIndex) {
    jQuery(document).trigger("wpbe_onmouseover_num_textinput", [_this, colIndex]);
    return true;
}

function wpbe_onmouseout_num_textinput(_this, colIndex) {
    jQuery(document).trigger("wpbe_onmouseout_num_textinput", [_this, colIndex]);
    return true;
}

