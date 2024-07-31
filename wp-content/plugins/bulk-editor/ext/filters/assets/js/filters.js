"use strict";

var wpbe_filtering_is_going = false;//marker about that posts are filtered
var wpbe_filter_chosen_inited = false;//just fix to init chosen
var wpbe_filter_current_key = null;//unique id of current filter operation, which allow make bulk by filter in different browser tabs!

jQuery(function ($) {
    
    "use strict";

    //init chosen by first click because chosen init doesn work for hidden containers
    jQuery(document).on("do_tabs-filters", {}, function () {
        //if (!wpbe_filter_chosen_inited) {
        setTimeout(function () {
            //set chosen
            jQuery('#tabs-filters .chosen-select').chosen();
            wpbe_filter_chosen_inited = true;
        }, 150);
        //}

        return true;
    });

    //set chosen to filters tab only
    jQuery('a[href="#tabs-filters"]').trigger('click');

    jQuery('.wpbe_filter_select').on('change',function () {
        if (jQuery(this).val() == -1 || jQuery(this).val() == 0) {
            jQuery(this).removeClass('wpbe_set_attention');
        } else {
            jQuery(this).addClass('wpbe_set_attention');
        }
        return true;
    });

    //***

    //placeholder label
    jQuery('#wpbe_filter_form input[placeholder]:not(.wpbe_calendar)').placeholderLabel();

    //***

    //Filter button
    jQuery('#wpbe_filter_posts_btn').on('click', function () {

        //jQuery('.wpbe_txt_search').val('');
       // console.log( jQuery('#wpbe_filter_form').serializeArray())
        wpbe_message(lang.filters.filtering, 'warning');
        wpbe_filter_current_key = (wpbe_get_random_string(16)).toLowerCase();
        jQuery('.wpbe_tools_panel_newprod_btn').hide();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_filter_posts',
                filter_data: jQuery('#wpbe_filter_form').serialize(),
                filter_current_key: wpbe_filter_current_key
            },
            success: function () {
                wpbe_message(lang.filters.filtered, 'notice', 30000);
                data_table.clear().draw();

                jQuery('.wpbe_filter_reset_btn1').show();
                jQuery('.wpbe_filter_reset_btn2').show();
                wpbe_filtering_is_going = true;
                __wpbe_action_will_be_applied_to();
            },
            error: function () {
                alert(lang.error);
            }
        });

        return false;
    });


    //Reset Filter button
    jQuery('.wpbe_filter_reset_btn1, .wpbe_filter_reset_btn2').on('click', function () {

        var _this = this;
        wpbe_message(lang.reseting, 'warning', 99999);
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'wpbe_reset_filter',
                filter_current_key: wpbe_filter_current_key
            },
            success: function () {

                if (!jQuery(_this).hasClass('wpbe_filter_reset_btn2')) {
                    //jQuery('.wpbe_top_panel_btn').trigger('click');
                }

                wpbe_filter_current_key = null;
                jQuery('.wpbe_tools_panel_newprod_btn').show();

                data_table.clear().draw();
                wpbe_message(lang.reseted, 'notice');
                jQuery('.wpbe_filter_reset_btn1').hide();
                jQuery('.wpbe_filter_reset_btn2').hide();
                //clear all filter drop-downs and inputs
                __wpbe_clean_filter_form();

                wpbe_filtering_is_going = false;
                __wpbe_action_will_be_applied_to();
            },
            error: function () {
                alert(lang.error);
            }
        });

        return false;
    });

    //***

    jQuery('#wpbe_filter_form input').on('keydown',function (e) {
        if (e.keyCode == 13) {
            jQuery('#wpbe_filter_posts_btn').trigger('click');
        }
    });

    //***

    jQuery(document).on("taxonomy_data_redrawn", {}, function (event, tax_key, term_id) {

        var select_id = 'wpbe_filter_taxonomies_' + tax_key;
        var select = jQuery('#' + select_id);
        jQuery(select).empty();
        __wpbe_fill_select(select_id, taxonomies_terms[tax_key]);
        jQuery(jQuery('#' + select_id)).chosen({
            width: '100%'
        }).trigger("chosen:updated");

        return true;
    });


    jQuery('.wpbe_filter_tools_select[name="wpbe_filter_tools_options"]').on('change', function(){
        var val=jQuery(this).val();
        var select_beh=jQuery('select[name="wpbe_filter_tools_behavior"]');
        var val_beh=select_beh.val();
        
        var options=jQuery('select[name="wpbe_filter['+val+'][behavior]"]').find("option").clone();
        
        select_beh.html(options);
        
        var selected=select_beh.find('option[value="'+val_beh+'"]');
        if(selected){
            selected.attr('selected','selected');
        }               
    });
    
    jQuery('#wpbe_filter_btn_tools_panel').click(function () {
        var text=jQuery('input[name="wpbe_filter_form_tools_value"]').val();
        var option = jQuery('select[name="wpbe_filter_tools_options"]').val();
        var behavior="exact";
        if(option!='post__in'){
            behavior=jQuery('select[name="wpbe_filter_tools_behavior"]').val();
        }
        var text_input=jQuery('input[name="wpbe_filter['+option+'][value]"]');
        if(jQuery(text_input).length){
            __wpbe_clean_filter_form();
            jQuery('input[name="wpbe_filter_form_tools_value"]').val(text);
            jQuery(text_input).val(text);
            setTimeout(function(){
                jQuery(text_input).siblings('label').css("margin-top", "-11px"); 
            }, 2000);
            
            jQuery('select[name="wpbe_filter['+option+'][behavior]"]').val(behavior);
        }
        jQuery('#wpbe_filter_posts_btn').trigger( "click" );

        jQuery('html, body').animate({
                scrollTop: jQuery("#wpbe_tools_panel").offset().top
        }, 777);
    }); 
    
    jQuery("input[name='wpbe_filter_form_tools_value']").off().on('keyup change', function (e) {
        if (e.keyCode === 13) {
            jQuery('#wpbe_filter_btn_tools_panel').trigger("click");
        }
    });

});


function __wpbe_clean_filter_form() {
    jQuery('#wpbe_filter_form input[type=text]').val('');
    jQuery('#wpbe_filter_form input[type=number]').val('');
    jQuery('#wpbe_filter_form .wpbe_calendar').val('').trigger('change');
    jQuery('#wpbe_filter_form select.chosen-select').val('').trigger("chosen:updated");
    jQuery('#wpbe_filter_form select:not(.chosen-select)').each(function (i, s) {
        jQuery(s).val(jQuery(s).find('option:first').val());
    });
    jQuery('#wpbe_filter_form select').removeClass('wpbe_set_attention');
        //tool panel filter
    jQuery('input[name="wpbe_filter_form_tools_value"]').val('');
}

