jQuery(document).ready(function () {

    // Weight threshold for LTL freight
    en_weight_threshold_limit();

    jQuery("#hold_at_terminal_fee").keyup(function (e) {

        var val = jQuery("#hold_at_terminal_fee").val();

        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            jQuery("#hold_at_terminal_fee").val(newval);
        }

        if (val.split('%').length - 1 == 1) {
            e.preventDefault();
        }

    });

    jQuery("#hold_at_terminal_fee").keydown(function (e) {

        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (event.keyCode !== 8 && event.keyCode !== 46) { //exception
                event.preventDefault();
            }
        }

    });

    //          JS for edit product nested fields
    jQuery("._nestedMaterials").closest('p').addClass("_nestedMaterials_tr");
    jQuery("._nestedPercentage").closest('p').addClass("_nestedPercentage_tr");
    jQuery("._maxNestedItems").closest('p').addClass("_maxNestedItems_tr");
    jQuery("._nestedDimension").closest('p').addClass("_nestedDimension_tr");
    jQuery("._nestedStakingProperty").closest('p').addClass("_nestedStakingProperty_tr");

    if (!jQuery('._nestedMaterials').is(":checked")) {
        jQuery('._nestedPercentage_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._maxNestedItems_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._nestedStakingProperty_tr').hide();
    } else {
        jQuery('._nestedPercentage_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._maxNestedItems_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._nestedStakingProperty_tr').show();
    }

    jQuery("input[name=_nestedPercentage]").attr('min', '0');
    jQuery("input[name=_maxNestedItems]").attr('min', '0');
    jQuery("input[name=_nestedPercentage]").attr('max', '100');
    jQuery("input[name=_maxNestedItems]").attr('max', '100');
    jQuery("input[name=_nestedPercentage]").attr('maxlength', '3');
    jQuery("input[name=_maxNestedItems]").attr('maxlength', '3');

    if (jQuery("input[name=_nestedPercentage]").val() == '') {
        jQuery("input[name=_nestedPercentage]").val(0);
    }

    jQuery("._nestedPercentage").keydown(function (eve) {
        xpo_lfq_stop_special_characters(eve);
        var nestedPercentage = jQuery('._nestedPercentage').val();
        if (nestedPercentage.length == 2) {
            var newValue = nestedPercentage + '' + eve.key;
            if (newValue > 100) {
                return false;
            }
        }
    });

    jQuery("._nestedDimension").keydown(function (eve) {
        xpo_lfq_stop_special_characters(eve);
        var nestedDimension = jQuery('._nestedDimension').val();
        if (nestedDimension.length == 2) {
            var newValue1 = nestedDimension + '' + eve.key;
            if (newValue1 > 100) {
                return false;
            }
        }
    });

    jQuery("._maxNestedItems").keydown(function (eve) {
        xpo_lfq_stop_special_characters(eve);
    });

    jQuery("._nestedMaterials").change(function () {
        if (!jQuery('._nestedMaterials').is(":checked")) {
            jQuery('._nestedPercentage_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._maxNestedItems_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._nestedStakingProperty_tr').hide();
        } else {
            jQuery('._nestedPercentage_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._maxNestedItems_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._nestedStakingProperty_tr').show();
        }
    });

    jQuery("#wc_settings_xpo_residential").closest('tr').addClass("wc_settings_xpo_residential");
    jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");
    jQuery("#avaibility_lift_gate").closest('tr').addClass("avaibility_lift_gate");
    jQuery("#wc_settings_xpo_liftgate").closest('tr').addClass("wc_settings_xpo_liftgate");
    jQuery("#xpo_quotes_liftgate_delivery_as_option").closest('tr').addClass("xpo_quotes_liftgate_delivery_as_option");
    jQuery("#hold_at_terminal_checkbox_status").closest('tr').addClass("hold_at_terminal_checkbox_status");
    jQuery("#wc_settings_xpo_handling_fee").closest('tr').addClass("wc_settings_xpo_handling_fee_tr");
    jQuery("#wc_settings_xpo_handling_fee_2").closest('tr').addClass("wc_settings_xpo_handling_fee_2_tr");
    jQuery("#wc_settings_xpo_label_as").closest('tr').addClass("wc_settings_xpo_label_as_tr");
    jQuery("#hold_at_terminal_fee").closest('tr').addClass("xpo_hold_at_terminal_fee_tr");
    jQuery("#wc_settings_xpo_allow_other_plugins").closest('tr').addClass("wc_xpo_settings_xpo_allow_other_plugins_tr");
    jQuery("#xpo_freight_handling_weight").closest('tr').addClass("xpo_freight_handling_weight_tr");
    jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq_tr");
    jQuery('#en_weight_threshold_lfq').attr('maxlength', 4);
    jQuery("#order_shipping_line_items .shipping .display_meta").css('display', 'none');

    // Handling unit
    jQuery('#xpo_freight_handling_weight').attr('maxlength', 7);
    jQuery('#xpo_freight_maximum_handling_weight').attr('maxlength', 7);
    jQuery("#xpo_freight_handling_weight").closest('tr').addClass("xpo_freight_cutt_off_time_ship_date_offset");
    jQuery("#xpo_freight_maximum_handling_weight").closest('tr').addClass("xpo_freight_cutt_off_time_ship_date_offset");

    jQuery("#en_weight_threshold_lfq, #wc_settings_xpo_handling_fee, #wc_settings_xpo_handling_fee_2, #xpo_freight_handling_weight, #xpo_freight_maximum_handling_weight, #hold_at_terminal_fee").focus(function (e) {
        jQuery("#" + this.id).css({'border-color': '#ddd'});
    });
    // Cuttoff Time
    jQuery("#xpo_freight_shipment_offset_days").closest('tr').addClass("xpo_freight_shipment_offset_days_tr");
    jQuery("#xpo_freight_shipment_offset_days").attr('maxlength', 8);
    jQuery("#all_shipment_days_xpo").closest('tr').addClass("all_shipment_days_xpo_tr");
    jQuery(".xpo_shipment_day").closest('tr').addClass("xpo_shipment_day_tr");
    jQuery("#xpo_freight_order_cut_off_time").closest('tr').addClass("xpo_freight_cutt_off_time_ship_date_offset");
    var xpo_current_time = en_xpo_admin_script.xpo_freight_order_cutoff_time;
    if (xpo_current_time == '') {

        jQuery('#xpo_freight_order_cut_off_time').wickedpicker({
            now: '',
            title: 'Cut Off Time',
        });
    } else {
        jQuery('#xpo_freight_order_cut_off_time').wickedpicker({

            now: xpo_current_time,
            title: 'Cut Off Time'
        });
    }

    var delivery_estimate_val = jQuery('input[name=xpo_delivery_estimates]:checked').val();
    if (delivery_estimate_val == 'dont_show_estimates') {
        jQuery("#xpo_freight_order_cut_off_time").prop('disabled', true);
        jQuery("#xpo_freight_shipment_offset_days").prop('disabled', true);
        jQuery("#xpo_freight_shipment_offset_days").css("cursor", "not-allowed");
        jQuery("#xpo_freight_order_cut_off_time").css("cursor", "not-allowed");
        jQuery('.all_shipment_days_xpo, .xpo_shipment_day').prop('disabled', true);
        jQuery('.all_shipment_days_xpo, .xpo_shipment_day').css("cursor", "not-allowed");
    } else {
        jQuery("#xpo_freight_order_cut_off_time").prop('disabled', false);
        jQuery("#xpo_freight_shipment_offset_days").prop('disabled', false);
        // jQuery("#xpo_freight_order_cut_off_time").css("cursor", "auto");
        jQuery("#xpo_freight_order_cut_off_time").css("cursor", "");
        jQuery('.all_shipment_days_xpo, .xpo_shipment_day').prop('disabled', false);
        jQuery('.all_shipment_days_xpo, .xpo_shipment_day').css("cursor", "auto");
    }

    jQuery("input[name=xpo_delivery_estimates]").change(function () {
        var delivery_estimate_val = jQuery('input[name=xpo_delivery_estimates]:checked').val();
        if (delivery_estimate_val == 'dont_show_estimates') {
            jQuery("#xpo_freight_order_cut_off_time").prop('disabled', true);
            jQuery("#xpo_freight_shipment_offset_days").prop('disabled', true);
            jQuery("#xpo_freight_order_cut_off_time").css("cursor", "not-allowed");
            jQuery("#xpo_freight_shipment_offset_days").css("cursor", "not-allowed");
            jQuery('.all_shipment_days_xpo, .xpo_shipment_day').prop('disabled', true);
            jQuery('.all_shipment_days_xpo, .xpo_shipment_day').css("cursor", "not-allowed");
        } else {
            jQuery("#xpo_freight_order_cut_off_time").prop('disabled', false);
            jQuery("#xpo_freight_shipment_offset_days").prop('disabled', false);
            jQuery("#xpo_freight_order_cut_off_time").css("cursor", "auto");
            jQuery("#xpo_freight_shipment_offset_days").css("cursor", "auto");
            jQuery('.all_shipment_days_xpo, .xpo_shipment_day').prop('disabled', false);
            jQuery('.all_shipment_days_xpo, .xpo_shipment_day').css("cursor", "auto");
        }
    });

    /*
     * Uncheck Week days Select All Checkbox
     */
    jQuery(".xpo_shipment_day").on('change load', function () {

        var checkboxes = jQuery('.xpo_shipment_day:checked').length;
        var un_checkboxes = jQuery('.xpo_shipment_day').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.all_shipment_days_xpo').prop('checked', true);
        } else {
            jQuery('.all_shipment_days_xpo').prop('checked', false);
        }
    });

    /*
     * Select All Shipment Week days
     */

    var all_int_checkboxes = jQuery('.all_shipment_days_xpo');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.all_shipment_days_xpo').prop('checked', true);
    }

    jQuery(".all_shipment_days_xpo").change(function () {
        if (this.checked) {
            jQuery(".xpo_shipment_day").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".xpo_shipment_day").each(function () {
                this.checked = false;
            });
        }
    });


    //** End: Order Cut Off Time

    /**
     * Offer lift gate delivery as an option and Always include residential delivery fee
     * @returns {undefined}
     */

    jQuery(".checkbox_fr_add").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "wc_settings_xpo_liftgate") {
            jQuery("#xpo_quotes_liftgate_delivery_as_option").prop({checked: false});
            jQuery("#en_woo_addons_liftgate_with_auto_residential").prop({checked: false});

        } else if (id == "xpo_quotes_liftgate_delivery_as_option" ||
            id == "en_woo_addons_liftgate_with_auto_residential") {
            jQuery("#wc_settings_xpo_liftgate").prop({checked: false});
        }
    });

    var url = getUrlVarsXpoFreight()["tab"];
    if (url === 'xpo_quotes') {
        jQuery('#footer-left').attr('id', 'wc-footer-left');
    }
    jQuery("#wc_settings_xpo_customer_number, #wc_settings_xpo_zipcode, #wc_settings_xpo_third_party_acc").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (e.keyCode != '67' && e.keyCode != '86' && e.keyCode != '88') {
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        }
    });

    jQuery('.connection_section_class_xpo input[type="text"]').each(function () {
        if (jQuery(this).parent().find('.err').length < 1) {
            jQuery(this).after('<span class="err"></span>');
        }
    });

    jQuery('.connection_section_class_xpo .form-table').before('<div class="xpo_warning_msg"><p><b>Note!</b> You must have an XPO account to use this application. If you do not have one, contact XPO at 800-755-2728, or <a href="https://ltl.xpo.com/webapp/membership_app/membershipSignupCompanySearch.do" target="_blank">Create an LTL.XPO.com Account</a>.</p>');

    /*
     * Add Title To Connection Setting Fields
     */

    jQuery('#wc_settings_xpo_username').attr('title', 'Username');
    jQuery('#wc_settings_xpo_password').attr('title', 'Password');
    jQuery('#wc_settings_xpo_customer_number').attr('title', 'Pickup/Delivery Account Number');
    jQuery('#wc_settings_xpo_zipcode').attr({'title': 'Pickup/Delivery Postal Code', 'maxlength': '7'});
    jQuery('#wc_settings_xpo_third_party_acc').attr('title', 'Bill To Account Number');
    jQuery('#wc_settings_xpo_basic_access_token').attr('title', 'Access Token');
    jQuery('#wc_settings_xpo_plugin_licence_key').attr('title', 'Eniture API Key');
    jQuery('#wc_settings_xpo_handling_fee').attr('title', 'Handling Fee / Markup');
    jQuery('#wc_settings_xpo_handling_fee_2').attr('title', 'Handling Fee / Markup 2');
    jQuery('#wc_settings_xpo_label_as').attr('title', 'Label As');
    jQuery('#wc_settings_xpo_label_as').attr('maxlength', '50');

    /*
         * Save Changes At Connection Section Action
         */
    jQuery(".connection_section_class_xpo .woocommerce-save-button, .connection_section_class_xpo .is-primary").click(function () {
        jQuery('#wc_settings_xpo_third_party_acc').data('optional', 1);
        jQuery('#wc_settings_xpo_basic_access_token').data('optional', 1);
        var has_err = true;
        jQuery(".connection_section_class_xpo tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined || optional === null) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }
    });

    /*
     * Test connection
     */

    jQuery(".connection_section_class_xpo .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary xpo_test_connection">Test connection</a>');
    jQuery('.xpo_test_connection').click(function (e) {
        if (jQuery("input[name='xpo_account_select_setting']:checked").val() === 'thirdParty') {
            jQuery('#wc_settings_xpo_third_party_acc').data('optional', null);
        } else {
            jQuery('#wc_settings_xpo_third_party_acc').data('optional', 1);
        }
        if (jQuery('#wc_settings_xpo_basic_access_token').val()) {
            jQuery('#wc_settings_xpo_basic_access_token').data('optional', null);
        } else {
            jQuery('#wc_settings_xpo_basic_access_token').data('optional', 1);
        }
        var has_err = true;
        jQuery(".connection_section_class_xpo tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined || optional === null) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }
        var postForm = {
            'action': 'xpo_action',
            'xpo_username': jQuery('#wc_settings_xpo_username').val(),
            'xpo_password': jQuery('#wc_settings_xpo_password').val(),
            'xpo_customer_number': jQuery('#wc_settings_xpo_customer_number').val(),
            'billing_postal_code': jQuery('#wc_settings_xpo_zipcode').val(),
            'third_party_acc_number': jQuery('#wc_settings_xpo_third_party_acc').val(),
            'account_check': jQuery("input[name='xpo_account_select_setting']:checked").val(),
            'xpo_plugin_license': jQuery('#wc_settings_xpo_plugin_licence_key').val(),
            'basic_access_token': jQuery('#wc_settings_xpo_basic_access_token').val(),
        };
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',
            beforeSend: function () {
                jQuery(".connection_save_button").remove();
                jQuery('#wc_settings_xpo_customer_number').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_username').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_password').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_zipcode').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_third_party_acc').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_basic_access_token').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_xpo_plugin_licence_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/asset/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data) {
                jQuery('#message').hide();
                jQuery(".xpo_error_message").remove();
                jQuery(".xpo_success_message").remove();

                jQuery('#wc_settings_xpo_customer_number').css('background', '#fff');
                jQuery('#wc_settings_xpo_third_party_acc').css('background', '#fff');
                jQuery('#wc_settings_xpo_basic_access_token').css('background', '#fff');
                jQuery('#wc_settings_xpo_username').css('background', '#fff');
                jQuery('#wc_settings_xpo_password').css('background', '#fff');
                jQuery('#wc_settings_xpo_zipcode').css('background', '#fff');
                jQuery('#wc_settings_xpo_plugin_licence_key').css('background', '#fff');
                jQuery(".xpo_success_message").remove();
                jQuery(".xpo_error_message").remove();

                if (data.Error) {
                    jQuery('.xpo_warning_msg').before('<div class="notice notice-error xpo_error_message"><p><strong>Error!</strong> ' + data.Error + '</p></div>');
                } else if (data.Success) {
                    jQuery('.xpo_warning_msg').before('<div class="notice notice-success xpo_success_message"><p><strong>Success!</strong> ' + data.Success + '</p></div>');
                } else {
                    jQuery('.xpo_warning_msg').before('<div class="notice notice-error xpo_error_message"><p><strong> Error!</strong> XPO Logistics is currently unable to pass connection please try again later.</p></div>');
                }

                jQuery('html, body').animate({
                    'scrollTop': jQuery('.notice').position().top
                });
            }
        });
        e.preventDefault();
    });
    // fdo va
    jQuery('#fd_online_id_xpo').click(function (e) {
        var postForm = {
            'action': 'xpo_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_xpo').attr("data")
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_xpo').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".xpo_error_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error xpo_error_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_xpo_admin_script.plugins_url + '/ltl-freight-quotes-xpo-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data_response) {
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".xpo_error_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error xpo_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success xpo_success_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error xpo_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error xpo_error_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error xpo_error_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_xpo" data="disconnect" class="button-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });

    jQuery("#hold_at_terminal_fee").keyup(function (e) {

        var val = jQuery("#hold_at_terminal_fee").val();

        if (val.split('.').length - 1 > 1) {

            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery("#hold_at_terminal_fee").val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery("#hold_at_terminal_fee").val(newval);
        }
        if (val.split('>').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
            newval = newval.substring(newval, newval.length - countGreaterThan - 1);
            jQuery("#hold_at_terminal_fee").val(newval);
        }
        if (val.split('_').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
            newval = newval.substring(newval, newval.length - countUnderScore - 1);
            jQuery("#hold_at_terminal_fee").val(newval);
        }
    });

    jQuery("#wc_settings_xpo_handling_fee").keyup(function (e) {

        var val = jQuery("#wc_settings_xpo_handling_fee").val();

        if (val.split('.').length - 1 > 1) {

            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
        if (val.split('>').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
            newval = newval.substring(newval, newval.length - countGreaterThan - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
        if (val.split('_').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
            newval = newval.substring(newval, newval.length - countUnderScore - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
    });

    jQuery("#wc_settings_xpo_handling_fee_2").keyup(function (e) {

        var val = jQuery("#wc_settings_xpo_handling_fee").val();

        if (val.split('.').length - 1 > 1) {

            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
        if (val.split('>').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
            newval = newval.substring(newval, newval.length - countGreaterThan - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
        if (val.split('_').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
            newval = newval.substring(newval, newval.length - countUnderScore - 1);
            jQuery("#wc_settings_xpo_handling_fee").val(newval);
        }
    });

    //**Start: Handling Fee field validation
    jQuery("#wc_settings_xpo_handling_fee").keydown(function (e) {

        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

    });

    //**Start: Handling Fee field validation
    jQuery("#wc_settings_xpo_handling_fee_2").keydown(function (e) {

        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

    });

    jQuery("#en_weight_threshold_lfq").keydown(function (e) {
        // Allow: backspace, delete, tab, escape and enter
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

    });

    jQuery("#xpo_freight_handling_weight, #xpo_freight_maximum_handling_weight").keydown(function (e) {
        // Allow: backspace, delete, tab, escape and enter
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 3)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

    });


    jQuery("#hold_at_terminal_fee").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .

        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

    });

    var prevent_text_box = jQuery('.prevent_text_box').length;
    if (!prevent_text_box > 0) {
        jQuery("input[name*='wc_pervent_proceed_checkout_eniture']").closest('tr').addClass('wc_pervent_proceed_checkout_eniture');
        jQuery(".wc_pervent_proceed_checkout_eniture input[value*='allow']").after('Allow user to continue to check out and display this message <br><textarea  name="allow_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250">' + en_xpo_admin_script.allow_proceed_checkout_eniture + '</textarea></br><span class="description"> Enter a maximum of 250 characters.</span>');
        jQuery(".wc_pervent_proceed_checkout_eniture input[value*='prevent']").after('Prevent user from checking out and display this message <br><textarea name="prevent_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250">' + en_xpo_admin_script.prevent_proceed_checkout_eniture + '</textarea></br><span class="description"> Enter a maximum of 250 characters.</span>');
    }

    jQuery('.quote_section_class_xpo .button-primary, .quote_section_class_xpo .is-primary').on('click', function () {
        var Error = true;
        jQuery(".updated").hide();
        jQuery('.error').remove();

        if (!xpo_palletshipclass()) {
            return false;
        }

        /*Custom Error Message Validation*/
        var checkedValCustomMsg = jQuery("input[name='wc_pervent_proceed_checkout_eniture']:checked").val();
        var allow_proceed_checkout_eniture = jQuery("textarea[name=allow_proceed_checkout_eniture]").val();
        var prevent_proceed_checkout_eniture = jQuery("textarea[name=prevent_proceed_checkout_eniture]").val();

        if (checkedValCustomMsg == 'allow' && allow_proceed_checkout_eniture == '') {
            jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_ltl_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.xpo_ltl_custom_error_message').position().top
            });
            return false;
        } else if (checkedValCustomMsg == 'prevent' && prevent_proceed_checkout_eniture == '') {
            jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_ltl_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.xpo_ltl_custom_error_message').position().top
            });
            return false;
        }
        if (!xpo_weight_of_handling_unit()) {
            return false;
        }
        if (!xpo_maximum_weight_of_handling_unit()) {
            return false;
        }
        if (!xpo_weight_of_threshold()) {
            return false;
        }

        var hold_at_terminal = jQuery('#hold_at_terminal_fee').val();
        if (hold_at_terminal != '') {
            var hold_at_terminal_array = hold_at_terminal.split('.');
            if (hold_at_terminal != '' && hold_at_terminal_array[1] == '') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_freight_hold_at_terminal_fee_error"><p><strong>Error! </strong>Hold at terminal fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_freight_hold_at_terminal_fee_error').position().top
                });
                jQuery("#hold_at_terminal_fee").css({'border-color': '#e81123'});
                return false;
            }
            if (hold_at_terminal != '' && hold_at_terminal_array[1] != undefined && hold_at_terminal_array[1].length > 2) {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_freight_hold_at_terminal_fee_error"><p><strong>Error! </strong>Hold at terminal fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_freight_hold_at_terminal_fee_error').position().top
                });
                jQuery("#hold_at_terminal_fee").css({'border-color': '#e81123'});
                return false;
            }
            if ((hold_at_terminal) == '.') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_freight_hold_at_terminal_fee_error"><p><strong>Error! </strong>Hold at terminal fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_freight_hold_at_terminal_fee_error').position().top
                });
                jQuery("#hold_at_terminal_fee").css({'border-color': '#e81123'});
                return false;
            }
            if ((hold_at_terminal) == '%') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_freight_hold_at_terminal_fee_error"><p><strong>Error! </strong>Hold at terminal fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_freight_hold_at_terminal_fee_error').position().top
                });
                jQuery("#hold_at_terminal_fee").css({'border-color': '#e81123'});
                return false;
            }
            var numberOnlyRegex = /^\d*\.?\d*%?$/;

            if (hold_at_terminal != "" && !numberOnlyRegex.test(hold_at_terminal)) {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_freight_hold_at_terminal_fee_error"><p><strong>Error! </strong>Hold at terminal fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_freight_hold_at_terminal_fee_error').position().top
                });
                jQuery("#hold_at_terminal_fee").css({'border-color': '#e81123'});
                return false;
            }
        }

        //          handling fee / mark up 1
        var handling_fee = jQuery('#wc_settings_xpo_handling_fee').val();
        if (handling_fee != '') {
            var handling_fee_array = handling_fee.split('.');
            if (handling_fee != '' && handling_fee_array[1] == '') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee").css({'border-color': '#e81123'});
                return false;
            }
            if (handling_fee.slice(handling_fee.length - 1) == '%') {
                handling_fee = handling_fee.slice(0, handling_fee.length - 1)
            }

            if (handling_fee !== '' && isValidNumber(handling_fee) === false) {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee").css({'border-color': '#e81123'});
                return false;
            } else if (handling_fee !== '' && isValidNumber(handling_fee) === 'decimal_point_err') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10% and only 2 digits are allowed after decimal.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee").css({'border-color': '#e81123'});
                return false;
            }
        }

        //              handling fee / mark up 2
        var handling_fee_2 = jQuery('#wc_settings_xpo_handling_fee_2').val();
        if (handling_fee_2 !== '') {
            var handling_fee_array_2 = handling_fee_2.split('.');
            if (handling_fee_2 != '' && handling_fee_array_2[1] == '') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error_2"><p><strong>Error! </strong>Handling fee 2 format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error_2').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee_2").css({'border-color': '#e81123'});
                return false;
            }
            if (handling_fee_2.slice(handling_fee_2.length - 1) == '%') {
                handling_fee_2 = handling_fee_2.slice(0, handling_fee_2.length - 1)
            }

            if (handling_fee_2 !== "" && isValidNumber(handling_fee_2) === false) {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error_2"><p><strong>Error! </strong>Handling fee 2 format should be 100.20 or 10%.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error_2').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee_2").css({'border-color': '#e81123'});
                return false;
            } else if (handling_fee_2 !== "" && isValidNumber(handling_fee_2) === 'decimal_point_err') {
                jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_handlng_fee_error_2"><p><strong>Error! </strong>Handling fee 2 format should be 100.20 or 10% and only 2 digits are allowed after decimal.</p></div>');
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.xpo_handlng_fee_error_2').position().top
                });
                jQuery("#wc_settings_xpo_handling_fee_2").css({'border-color': '#e81123'});
                return false;
            }
        }

        return Error;

    });
    
    // Product variants settings
    jQuery(document).on("click", '._nestedMaterials', function(e) {
        const checkbox_class = jQuery(e.target).attr("class");
        const name = jQuery(e.target).attr("name");
        const checked = jQuery(e.target).prop('checked');

        if (checkbox_class?.includes('_nestedMaterials')) {
            const id = name?.split('_nestedMaterials')[1];
            setNestMatDisplay(id, checked);
        }
    });
});

// Weight threshold for LTL freight
if (typeof en_weight_threshold_limit != 'function') {
    function en_weight_threshold_limit() {
        // Weight threshold for LTL freight
        jQuery("#en_weight_threshold_lfq").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g) || !jQuery("#en_weight_threshold_lfq").val().match(/^\d{0,3}$/)) return false;
        });

        jQuery('#en_plugins_return_LTL_quotes').on('change', function () {
            if (jQuery('#en_plugins_return_LTL_quotes').prop("checked")) {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'contents');
            } else {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'none');
            }
        });

        jQuery("#en_plugins_return_LTL_quotes").closest('tr').addClass("en_plugins_return_LTL_quotes_tr");
        // Weight threshold for LTL freight
        var weight_threshold_class = jQuery("#en_weight_threshold_lfq").attr("class");
        jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq " + weight_threshold_class);

        // Weight threshold for LTL freight is empty
        if (jQuery('#en_weight_threshold_lfq').length && !jQuery('#en_weight_threshold_lfq').val().length > 0) {
            jQuery('#en_weight_threshold_lfq').val(150);
        }
    }
}

/**
 * Check is valid number
 * @param num
 * @param selector
 * @param limit | LTL weight limit 20K
 * @returns {boolean}
 */
function isValidDecimal(num, selector, limit = 20000) {
    // validate the number:
    // positive and negative numbers allowed
    // just - sign is not allowed,
    // -0 is also not allowed.
    if (parseFloat(num) === 0) {
        // Change the value to zero
        return false;
    }

    const reg = /^(-?[0-9]{1,5}(\.\d{1,4})?|[0-9]{1,5}(\.\d{1,4})?)$/;
    let isValid = false;
    if (reg.test(num)) {
        isValid = inRange(parseFloat(num), -limit, limit);
    }
    if (isValid === true) {
        return true;
    }
    return isValid;
}

/**
 * Check is the number is in given range
 *
 * @param num
 * @param min
 * @param max
 * @returns {boolean}
 */
function inRange(num, min, max) {
    return ((num - min) * (num - max) <= 0);
}

function xpo_weight_of_threshold() {
    var weight_of_threshold = jQuery('#en_weight_threshold_lfq').val();
    var weight_of_threshold_regex = /^[0-9]*$/;
    if (weight_of_threshold != '' && !weight_of_threshold_regex.test(weight_of_threshold)) {
        jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_wieght_of_threshold_error"><p><strong>Error! </strong>Cart weight threshold format should be like 150.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.xpo_wieght_of_threshold_error').position().top
        });
        jQuery("#en_weight_threshold_lfq").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function xpo_weight_of_handling_unit() {
    var weight_of_handling_unit = jQuery('#xpo_freight_handling_weight').val();
    if (weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(weight_of_handling_unit, 'xpo_freight_handling_weight');
    } else {
        validResponse = true;
    }
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_wieght_of_handling_unit_error"><p><strong>Error! </strong>Weight of Handling Unit format should be like, e.g. 48.5 and only 2 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.xpo_wieght_of_handling_unit_error').position().top
        });
        jQuery("#xpo_freight_handling_weight").css({'border-color': '#e81123'});
        return false;
    }

}

function xpo_maximum_weight_of_handling_unit() {
    var weight_of_handling_unit = jQuery('#xpo_freight_maximum_handling_weight').val();
    if (weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(weight_of_handling_unit, 'xpo_freight_maximum_handling_weight');
    } else {
        validResponse = true;
    }
    
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_wieght_of_handling_unit_error"><p><strong>Error! </strong>Maximum Weight per Handling Unit format should be like, e.g. 48.5 and only 2 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.xpo_wieght_of_handling_unit_error').position().top
        });
        jQuery("#xpo_freight_maximum_handling_weight").css({'border-color': '#e81123'});
    
        return false;
    }

}

// Update plan
if (typeof en_update_plan != 'function') {
    function en_update_plan(input) {
        let action = jQuery(input).attr('data-action');
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: action},
            success: function (data_response) {
                window.location.reload(true);
            }
        });
    }
}

/*
         * Validate Input If Empty or Invalid
         */
function validateInput(form_id) {
    var has_err = true;
    jQuery(form_id + " input[type='text']").each(function () {
        var input = jQuery(this).val();
        var response = validateString(input);

        var errorElement = jQuery(this).parent().find('.err');
        jQuery(errorElement).html('');
        var errorText = jQuery(this).attr('title');
        var optional = jQuery(this).data('optional');
        optional = (optional === undefined || optional === null) ? 0 : 1;
        errorText = (errorText != undefined) ? errorText : '';
        if ((optional == 0) && (response == false || response == 'empty')) {
            errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
            jQuery(errorElement).html(errorText);
        }
        has_err = (response != true && optional == 0) ? false : has_err;
    });
    return has_err;
}

function isValidNumber(value, noNegative) {
    if (typeof (noNegative) === 'undefined')
        noNegative = false;
    var isValidNumber = false;
    var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
    if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
        if (value.indexOf(".") >= 0) {
            var n = value.split(".");
            if (n[n.length - 1].length <= 4) {
                isValidNumber = true;
            } else {
                isValidNumber = 'decimal_point_err';
            }
        } else {
            isValidNumber = true;
        }
    }
    return isValidNumber;
}

/*
 * Check Input Value Is Not String
 */
function validateString(string) {
    if (string == '') {
        return 'empty';
    } else {
        return true;
    }
}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function getUrlVarsXpoFreight() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function xpo_palletshipclass() {
    var en_ship_class = jQuery('#en_ignore_items_through_freight_classification').val();
    var en_ship_class_arr = en_ship_class.split(',');
    var en_ship_class_trim_arr = en_ship_class_arr.map(Function.prototype.call, String.prototype.trim);
    if (en_ship_class_trim_arr.indexOf('ltl_freight') != -1) {
        jQuery("#mainform .quote_section_class_xpo").prepend('<div id="message" class="error inline xpo_pallet_weight_error"><p><strong>Error! </strong>Shipping Slug of <b>ltl_freight</b> can not be ignored.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.xpo_pallet_weight_error').position().top
        });
        jQuery("#en_ignore_items_through_freight_classification").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function xpo_lfq_stop_special_characters(e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 9, 27, 13, 110, 190, 189]) !== -1 ||
        // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
        // let it happen, don't do anything
        e.preventDefault();
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 90)) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 186 && e.keyCode != 8) {
        e.preventDefault();
    }
    if (e.keyCode == 186 || e.keyCode == 190 || e.keyCode == 189 || (e.keyCode > 64 && e.keyCode < 91)) {
        e.preventDefault();
        return;
    }
}

if (typeof setNestedMaterialsUI != 'function') {
    function setNestedMaterialsUI() {
        const nestedMaterials = jQuery('._nestedMaterials');
        const productMarkups = jQuery('._en_product_markup');
        
        if (productMarkups?.length) {
            for (const markup of productMarkups) {
                jQuery(markup).attr('maxlength', '7');

                jQuery(markup).keypress(function (e) {
                    if (!String.fromCharCode(e.keyCode).match(/^[0-9.%-]+$/))
                        return false;
                });
            }
        }

        if (nestedMaterials?.length) {
            for (let elem of nestedMaterials) {
                const className = elem.className;

                if (className?.includes('_nestedMaterials')) {
                    const checked = jQuery(elem).prop('checked'),
                        name = jQuery(elem).attr('name'),
                        id = name?.split('_nestedMaterials')[1];
                    setNestMatDisplay(id, checked);
                }
            }
        }
    }
}

if (typeof setNestMatDisplay != 'function') {
    function setNestMatDisplay (id, checked) {
        
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('min', '0');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('max', '100');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('maxlength', '3');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('min', '0');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('max', '100');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('maxlength', '3');

        jQuery(`input[name="_nestedPercentage${id}"], input[name="_maxNestedItems${id}"]`).keypress(function (e) {
            if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/))
                return false;
        });

        jQuery(`input[name="_nestedPercentage${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedDimension${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`input[name="_maxNestedItems${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedStakingProperty${id}"]`).closest('p').css('display', checked ? '' : 'none');
    }
}