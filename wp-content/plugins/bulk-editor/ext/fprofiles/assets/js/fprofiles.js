"use strict";

var wpbe_filter_profile_data = null;
jQuery(function ($) {
    
    "use strict";

    jQuery('.wpbe_tools_panel_fprofile_btn').on('click', function () {
        if (wpbe_filter_current_key) {
            jQuery('.wpbe-new-fprofile-inputs').show();
            jQuery('.wpbe-new-fprofile-attention').hide();
            jQuery('#wpbe_new_fprofile_btn').show();
        } else {
            jQuery('.wpbe-new-fprofile-inputs').hide();
            jQuery('.wpbe-new-fprofile-attention').show();
            jQuery('#wpbe_new_fprofile_btn').hide();
        }

        //***
        //hide input for new profile if loaded one of the profiles
        jQuery("#wpbe_load_fprofile option").each(function (i, o)
        {
            if (jQuery(o).val() != 0 && jQuery(o).val() == wpbe_filter_current_key) {
                jQuery('.wpbe-new-fprofile-inputs').hide();
                jQuery('.wpbe-new-fprofile-attention').show();
                jQuery('#wpbe_new_fprofile_btn').hide();
                return false;
            }
        });

        //***

        jQuery('#wpbe_fprofile_popup').show();
        jQuery('#wpbe_new_fprofile').focus();
        return false;
    });
    jQuery('.wpbe-modal-close-fprofile').on('click', function () {
        jQuery('#wpbe_fprofile_popup').hide();
    });
    //***

    jQuery('#wpbe_load_fprofile').on('change',function () {

        var profile_key = jQuery(this).val();
        if (profile_key != 0) {
            jQuery('#wpbe_load_fprofile_actions').show();
        } else {
            jQuery('#wpbe_load_fprofile_actions').hide();
        }

        //***

        if (profile_key != 0) {

            jQuery('#wpbe_load_fprofile_actions').hide();
            jQuery('#wpbe_loaded_fprofile_data_info').html(lang.loading);
	    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_get_filter_profile_data',
                    profile_key: profile_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (answer) {
                    answer = JSON.parse(answer);
                    jQuery('#wpbe_loaded_fprofile_data_info').html(answer.html);
                    wpbe_filter_profile_data = answer.res;
                    jQuery('#wpbe_load_fprofile_actions').show();
                }
            });
        }

    });
    //***

    jQuery('#wpbe_load_fprofile_btn').on('click', function () {

        var profile_key = jQuery('#wpbe_load_fprofile').val();
	var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
        jQuery('.wpbe-modal-close-fprofile').trigger('click');
        if (profile_key != 0) {
            wpbe_message(lang.loading, 'warning');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_load_filter_profile',
                    profile_key: profile_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (answer) {
                    answer = parseInt(answer, 10);
                    if (answer === 1) {
                        wpbe_filter_current_key = profile_key;
                        wpbe_message(lang.filters.filtered, 'notice', 30000);
                        data_table.clear().draw();
                        jQuery('.wpbe_tools_panel_newprod_btn').hide();
                        jQuery('.wpbe_filter_reset_btn1').show();
                        jQuery('.wpbe_filter_reset_btn2').show();
                        //clear all filter drop-downs and inputs
                        __wpbe_clean_filter_form();

                        wpbe_filtering_is_going = true;
                        __wpbe_action_will_be_applied_to();
                        //lets fill filter form by data from the loaded profile
                        if (Object.keys(wpbe_filter_profile_data).length !== 0) {
                            //console.log(wpbe_filter_profile_data);

                            Object.keys(wpbe_filter_profile_data).forEach(function (key, index) {

                                if (key == 'taxonomies_terms_titles') {
                                    return true;//we not need it here at all
                                }

                                if (key == 'taxonomies_operators') {
                                    if (Object.keys(wpbe_filter_profile_data[key]).length !== 0) {
                                        Object.keys(wpbe_filter_profile_data[key]).forEach(function (k, i) {
                                            jQuery('[name="wpbe_filter[taxonomies_operators][' + k + ']"]').val(wpbe_filter_profile_data[key][k]);
                                        });
                                    }

                                    return true;
                                }


                                if (key == 'taxonomies') {

                                    if (Object.keys(wpbe_filter_profile_data[key]).length !== 0) {
                                        Object.keys(wpbe_filter_profile_data[key]).forEach(function (k, i) {
                                            jQuery('[name="wpbe_filter[taxonomies][' + k + '][]"]').val(wpbe_filter_profile_data[key][k]);
                                        });

                                        jQuery('#wpbe_filter_form select.chosen-select').trigger("chosen:updated");
                                    }

                                    return true;
                                }

                                //console.log(wpbe_filter_profile_data[key]);
                                if (typeof wpbe_filter_profile_data[key] == 'object') {
                                    if ("value" in wpbe_filter_profile_data[key]) {
                                        jQuery('[name="wpbe_filter[' + key + '][value]"]').prev('label').css('margin-top', -11 + 'px');//fix fo jquery.placeholder.label.min
                                        jQuery('[name="wpbe_filter[' + key + '][value]"]').val(wpbe_filter_profile_data[key]['value']);
                                        jQuery('[name="wpbe_filter[' + key + '][behavior]"]').val(wpbe_filter_profile_data[key]['behavior']);
                                    }

                                    if ("from" in wpbe_filter_profile_data[key]) {
                                        jQuery('[name="wpbe_filter[' + key + '][from]"]').prev('label').css('margin-top', -11 + 'px');//fix fo jquery.placeholder.label.min
                                        jQuery('[name="wpbe_filter[' + key + '][from]"]').val(wpbe_filter_profile_data[key]['from']);
                                    }

                                    if ("to" in wpbe_filter_profile_data[key]) {
                                        jQuery('[name="wpbe_filter[' + key + '][to]"]').prev('label').css('margin-top', -11 + 'px');//fix fo jquery.placeholder.label.min
                                        jQuery('[name="wpbe_filter[' + key + '][to]"]').val(wpbe_filter_profile_data[key]['to']);
                                    }
                                } else {
                                    jQuery('[name="wpbe_filter[' + key + ']"]').val(wpbe_filter_profile_data[key]);
                                    jQuery('[name="wpbe_filter[' + key + ']"]').addClass('wpbe_set_attention');
                                }

                                //console.log(key);
                                //console.log(answer[key]);
                            });

                            //***
                            jQuery('#wpbe_filter_form .wpbe_calendar').trigger('change');
                        }
                    } else {
                        alert(lang.error);
                    }
                }
            });
        }

    });
    //***

    jQuery('#wpbe_new_fprofile_btn').on('click', function () {
        var profile_title = jQuery('#wpbe_new_fprofile').val();
	var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
        if (profile_title.length) {
            wpbe_message(lang.saving, 'warning');
            jQuery('#wpbe_new_fprofile').val('');
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_create_filter_profile',
                    profile_title: profile_title,
                    filter_current_key: wpbe_filter_current_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function (key) {
                    if (parseInt(key, 10) !== -2) {
                        jQuery('#wpbe_load_fprofile').append('<option selected value="' + key + '">' + profile_title + '</option>');
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
    jQuery('#wpbe_new_fprofile').on('keydown',function (e) {
        if (e.keyCode == 13) {
            jQuery('#wpbe_new_fprofile_btn').trigger('click');
        }
    });
    //***

    jQuery('.wpbe_delete_fprofile').on('click', function () {

        var profile_key = jQuery(this).attr('href');
        if (profile_key === '#') {
            profile_key = jQuery('#wpbe_load_fprofile').val();
        }

        if (profile_key == 'default') {
            wpbe_message(lang.no_deletable, 'warning');
            return false;
        }

        //***

        if (confirm(lang.sure)) {
            wpbe_message(lang.saving, 'warning');
            var select = document.getElementById('wpbe_load_fprofile');
            select.removeChild(select.querySelector('option[value="' + profile_key + '"]'));
	    var  wpbe_nonce = jQuery('#wpbe_tools_panel_nonce').val();
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'wpbe_delete_filter_profile',
                    profile_key: profile_key,
		    wpbe_nonce: wpbe_nonce
                },
                success: function () {
                    wpbe_message(lang.deleted, 'notice');
                }
            });
        }
        return false;
    });
});

