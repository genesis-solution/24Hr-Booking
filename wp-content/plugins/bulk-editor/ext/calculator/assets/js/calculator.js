"use strict";

var wpbe_calculator_current_cell = null;
var wpbe_calculator_is_drawned = false;


jQuery(function ($) {
    
    "use strict";

    jQuery('.wpbe_calculator_operation').val(wpbe_get_from_storage('wpbe_calculator_operation'));
    jQuery('.wpbe_calculator_how').val(wpbe_get_from_storage('wpbe_calculator_how'));

    //***

    jQuery('.wpbe_calculator_close').on('click', function () {
        jQuery('#wpbe_calculator').hide(99);
        wpbe_calculator_is_drawned = false;
        return false;
    });

    //***

    jQuery(document).on("tab_switched", {}, function (event) {
        jQuery('.wpbe_calculator_btn').hide();
        return true;
    });

    jQuery(document).on("data_redraw_done", {}, function (event) {
        jQuery('.wpbe_calculator_btn').hide();
        return true;
    });

    jQuery(document).on("wpbe_top_panel_clicked", {}, function (event) {
        jQuery('.wpbe_calculator_btn').hide();
        return true;
    });

    //***

    jQuery(document).on("wpbe_onmouseover_num_textinput", {}, function (event, o, colIndex) {
        wpbe_calc_onmouseover_num_textinput(o, colIndex);
        return true;
    });

    jQuery(document).on("wpbe_onmouseout_num_textinput", {}, function (event, o, colIndex) {
        wpbe_calc_onmouseout_num_textinput(o, colIndex);
        return true;
    });

    //***

    jQuery('.wpbe_calculator_set').on('click', function () {

        var val = parseFloat(jQuery('.wpbe_calculator_value').val());

        if (isNaN(val)) {
            jQuery('.wpbe_calculator_close').trigger('click');
            return;
        }

        var operation = jQuery('.wpbe_calculator_operation').val();
        var how = jQuery('.wpbe_calculator_how').val();



        //***

        var cell = wpbe_calculator_current_cell;//to avoid mouse over set of another cell whicle ajaxing
        var post_id = jQuery(cell).data('post-id');

        //***

        //fix
        if (jQuery(cell).data('field') !== 'sale_price' && operation == 'rp-') {
            operation = '+';
        }

        if (jQuery(cell).data('field') !== 'regular_price' && operation == 'sp+') {
            operation = '+';
        }

        //***

        var cell_value = parseFloat(jQuery(cell).html().replace(/\,/g, ""));

        var bulk_operation = 'invalue';

        //***

        switch (operation) {
            case '+':
                if (how == 'value') {
                    cell_value += val;
                } else {
                    //%
                    cell_value = cell_value + cell_value * val / 100;
                    bulk_operation = 'inpercent';
                }
                break;

            case '-':
                if (how == 'value') {
                    cell_value -= val;
                    bulk_operation = 'devalue';
                } else {
                    //%
                    cell_value = cell_value - cell_value * val / 100;
                    bulk_operation = 'depercent';
                }
                break;

            case 'rp-':

                cell_value = parseFloat(jQuery('#post_row_' + post_id).find("[data-field='regular_price']").html().replace(/\,/g, ""));

                if (how == 'value') {
                    cell_value = cell_value - val;
                    bulk_operation = 'devalue_regular_price';
                } else {
                    //%
                    cell_value = cell_value - cell_value * val / 100;
                    bulk_operation = 'depercent_regular_price';
                }
                break;

            case 'sp+':

                cell_value = parseFloat(jQuery('#post_row_' + post_id).find("[data-field='sale_price']").html().replace(/\,/g, ""));

                if (how == 'value') {
                    cell_value = cell_value + val;
                    bulk_operation = 'invalue_sale_price';
                } else {
                    //%
                    cell_value = cell_value + cell_value * val / 100;
                    bulk_operation = 'inpercent_sale_price';
                }
                break;
        }

        //***

        wpbe_message(lang.saving, '');


        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_update_page_field',
		wpbe_nonce: wpbe_field_update_nonce,
                post_id: post_id,
                field: jQuery(cell).data('field'),
                value: cell_value,
                num_rounding: jQuery('.wpbe_num_rounding').eq(0).val()
            },
            success: function (answer) {
                jQuery(cell).html(answer);
                wpbe_message(lang.saved, 'notice');


                //fix for stock_quantity + manage_stock
                if (!wpbe_bind_editing) {
                    if (jQuery(cell).data('field') == 'stock_quantity') {
                        wpbe_redraw_table_row(jQuery('#post_row_' + jQuery(cell).data('post-id')));
                    }
                }

                jQuery(document).trigger('wpbe_page_field_updated', [jQuery(cell).data('post-id'), jQuery(cell).data('field'), val, bulk_operation]);

                //jQuery('.wpbe_num_rounding').val(0);

                //wpbe_calculator_current_cell = null;
            }
        });


        jQuery('.wpbe_calculator_close').trigger('click');
        return false;
    });

    //***

    jQuery(".wpbe_calculator_value").on('keydown',function (e) {
        if (e.keyCode == 13)
        {
            jQuery('.wpbe_calculator_set').trigger('click');
        }

        if (e.keyCode == 27)
        {
            jQuery('.wpbe_calculator_close').trigger('click');
        }
    });

    jQuery("#wpbe_calculator").on('keydown',function (e) {
        if (e.keyCode == 27)
        {
            jQuery('.wpbe_calculator_close').trigger('click');
        }
    });

    //***

    jQuery('.wpbe_calculator_operation').on('change',function () {
        wpbe_set_to_storage('wpbe_calculator_operation', jQuery(this).val());
        return true;
    });

    jQuery('.wpbe_calculator_how').on('change',function () {
        wpbe_set_to_storage('wpbe_calculator_how', jQuery(this).val());
        return true;
    });

    //***
    jQuery('div.dataTables_scrollBody').scroll(function () {
        jQuery('.wpbe_calculator_btn').hide();
    });

});

function wpbe_calc_onmouseover_num_textinput(_this, colIndex) {

    if (wpbe_calculator_is_drawned) {
        return;
    }

    if (jQuery(_this).find('.info_restricked').length > 0 || jQuery(_this).data('editable-view') !== 'textinput') {
        jQuery('.wpbe_calculator_btn').hide();
        return;
    }

    //***

    wpbe_calculator_current_cell = _this;
    jQuery('.wpbe_calculator_btn').show();
    var rt = (jQuery(window).width() - (jQuery(_this).offset().left + jQuery(_this).outerWidth()));
    var tt = jQuery(_this).offset().top/* - jQuery(_this).outerHeight() / 2.3*/;
    jQuery('.wpbe_calculator_btn').css({top: tt, right: rt});

    return true;
}

function wpbe_draw_calculator() {
    jQuery('#wpbe_calculator').show();
    jQuery('#wpbe_calculator').css({top: jQuery('.wpbe_calculator_btn').css('top'), right: jQuery('.wpbe_calculator_btn').css('right')});
    jQuery(".wpbe_calculator_value").focus();

    //if input activated and visible in the cell
    if (jQuery(wpbe_calculator_current_cell).find('input')) {
        jQuery(wpbe_calculator_current_cell).html(jQuery(wpbe_calculator_current_cell).find('input').val());

        //***
        //as an example for future improvements
        if (jQuery(wpbe_calculator_current_cell).data('field') == 'sale_price') {
            var post_id = jQuery(wpbe_calculator_current_cell).data('post-id');
            //reqular_price column is enabled
            if (jQuery('#post_row_' + post_id).find("[data-field='regular_price']").length > 0) {
                jQuery('.wpbe_calc_rp').show();
            } else {
                jQuery('.wpbe_calc_rp').hide();
                jQuery('.wpbe_calculator_operation').val('+');
            }

        } else {
            jQuery('.wpbe_calc_rp').hide();
            if (jQuery('.wpbe_calculator_operation').val() == 'rp-') {
                jQuery('.wpbe_calculator_operation').val('+');
            }
        }

        //***

        if (jQuery(wpbe_calculator_current_cell).data('field') == 'regular_price') {
            var post_id = jQuery(wpbe_calculator_current_cell).data('post-id');
            //reqular_price column is enabled
            if (jQuery('#post_row_' + post_id).find("[data-field='sale_price']").length > 0) {
                jQuery('.wpbe_calc_sp').show();
            } else {
                jQuery('.wpbe_calc_sp').hide();
                jQuery('.wpbe_calculator_operation').val('+');
            }

        } else {
            jQuery('.wpbe_calc_sp').hide();
            if (jQuery('.wpbe_calculator_operation').val() == 'sp+') {
                jQuery('.wpbe_calculator_operation').val('+');
            }
        }
    }

    wpbe_calculator_is_drawned = true;
    
    return true;
}

function wpbe_calc_onmouseout_num_textinput() {
    if (wpbe_calculator_is_drawned) {
        //jQuery('.wpbe_calculator_btn').hide();
    }
    return true;
}


