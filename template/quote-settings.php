<?php

/**
 * XPO WooComerce XPO Quote Settings Page
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooComerce Quote Settings Tab Class
 */
class XPO_Quote_Settings
{

    /**
     * Quote Setting Fields
     * @return array
     */
    function xpo_quote_settings_tab()
    {
        // Cuttoff Time
        $xpo_disable_cutt_off_time_ship_date_offset = "";
        $xpo_cutt_off_time_package_required = "";

        //  Check the cutt of time & offset days plans for disable input fields
        $xpo_action_cutOffTime_shipDateOffset = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'xpo_cutt_off_time');
        if (is_array($xpo_action_cutOffTime_shipDateOffset)) {
            $xpo_disable_cutt_off_time_ship_date_offset = "disabled_me";
            $xpo_cutt_off_time_package_required = apply_filters('xpo_quotes_plans_notification_link', $xpo_action_cutOffTime_shipDateOffset);
        }

        $disable_hold_at_terminal = "";
        $hold_at_terminal_package_required = "";

        $action_hold_at_terminal = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hold_at_terminal');
        if (is_array($action_hold_at_terminal)) {
            $disable_hold_at_terminal = "disabled_me";
            $hold_at_terminal_package_required = apply_filters('xpo_quotes_plans_notification_link', $action_hold_at_terminal);
        }

        $ltl_enable = get_option('en_plugins_return_LTL_quotes');
        $weight_threshold_class = $ltl_enable == 'yes' ? 'show_en_weight_threshold_lfq' : 'hide_en_weight_threshold_lfq';
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

        echo '<div class="quote_section_class_xpo">';
        $settings = array(
            'section_title_quote' => array(
                'title' => __('Quote Settings ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_settings_xpo_section_title_quote'
            ),
            'label_as_xpo' => array(
                'name' => __('Label As ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => 'Identify how you want the quote labeled in the checkout process. A common choice for this field is “LTL Freight” or “XPO Logistics”. If left blank quotes will be labeled “Freight”.',
                'id' => 'wc_settings_xpo_label_as'
            ),
            'price_sort_xpo' => array(
                'name' => __("Don’t sort shipping methods by price  ", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
                'id' => 'shipping_methods_do_not_sort_by_price'
            ),

            //** Start Delivery Estimate Options - Cuttoff Time
            'service_xpo_estimates_title' => array(
                'name' => __('Delivery Estimate Options ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'service_xpo_estimates_title'
            ),
            'xpo_show_delivery_estimates_options_radio' => array(
                'name' => __("", 'woocommerce-settings-xpo'),
                'type' => 'radio',
                'default' => 'dont_show_estimates',
                'options' => array(
                    'dont_show_estimates' => __("Don't display delivery estimates.", 'woocommerce'),
                    'delivery_days' => __("Display estimated number of days until delivery.", 'woocommerce'),
                    'delivery_date' => __("Display estimated delivery date.", 'woocommerce'),
                ),
                'id' => 'xpo_delivery_estimates',
                'class' => 'xpo_dont_show_estimate_option',
            ),
            //** End Delivery Estimate Options
            //**Start: Cut Off Time & Ship Date Offset
            'cutOffTime_shipDateOffset_xpo_freight' => array(
                'name' => __('Cut Off Time & Ship Date Offset ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $xpo_cutt_off_time_package_required,
                'id' => 'xpo_freight_cutt_off_time_ship_date_offset'
            ),
            'orderCutoffTime_xpo_freight' => array(
                'name' => __('Order Cut Off Time ', 'woocommerce-settings-xpo_freight_freight_orderCutoffTime'),
                'type' => 'text',
                'placeholder' => '-- : -- --',
                'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                'id' => 'xpo_freight_order_cut_off_time',
                'class' => $xpo_disable_cutt_off_time_ship_date_offset,
            ),
            'shipmentOffsetDays_xpo_freight' => array(
                'name' => __('Fullfillment Offset Days ', 'woocommerce-settings-xpo_freight_shipment_offset_days'),
                'type' => 'text',
                'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                'placeholder' => 'Fullfillment Offset Days, e.g. 2',
                'id' => 'xpo_freight_shipment_offset_days',
                'class' => $xpo_disable_cutt_off_time_ship_date_offset,
            ),
            'all_shipment_days_xpo' => array(
                'name' => __("What days do you ship orders?", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Select All',
                'class' => "all_shipment_days_xpo $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'all_shipment_days_xpo'
            ),
            'monday_shipment_day_xpo' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Monday',
                'class' => "xpo_shipment_day $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'monday_shipment_day_xpo'
            ),
            'tuesday_shipment_day_xpo' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Tuesday',
                'class' => "xpo_shipment_day $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'tuesday_shipment_day_xpo'
            ),
            'wednesday_shipment_day_xpo' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Wednesday',
                'class' => "xpo_shipment_day $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'wednesday_shipment_day_xpo'
            ),
            'thursday_shipment_day_xpo' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Thursday',
                'class' => "xpo_shipment_day $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'thursday_shipment_day_xpo'
            ),
            'friday_shipment_day_xpo' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => 'Friday',
                'class' => "xpo_shipment_day $xpo_disable_cutt_off_time_ship_date_offset",
                'id' => 'friday_shipment_day_xpo'
            ),
            'xpo_show_delivery_estimates' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce-settings-xpo_quotes'),
                'desc' => '',
                'id' => 'xpo_show_delivery_estimates',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            //**End: Cut Off Time & Ship Date Offset
            'accessorial_quoted_xpo' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce-settings-xpo_quotes'),
                'desc' => '',
                'id' => 'woocommerce_accessorial_quoted_xpo',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            'residential_delivery_options_label' => array(
                'name' => __('Residential Delivery', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'residential_delivery_options_label'
            ),
            'accessorial_residential_delivery_xpo' => array(
                'name' => __('Always quote as residential delivery ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-xpo'),
                'id' => 'wc_settings_xpo_residential',
                'class' => 'accessorial_service',
            ),
//          Auto-detect residential addresses notification
            'avaibility_auto_residential' => array(
                'name' => __('Auto-detect residential addresses', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_auto_residential',
            ),
            'liftgate_delivery_options_label' => array(
                'name' => __('Lift Gate Delivery ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'liftgate_delivery_options_label'
            ),
            'accessorial_liftgate_delivery_xpo' => array(
                'name' => __('Always quote lift gate delivery ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-xpo'),
                'id' => 'wc_settings_xpo_liftgate',
                'class' => 'accessorial_service checkbox_fr_add',
            ),
            'xpo_quotes_liftgate_delivery_as_option' => array(
                'name' => __('Offer lift gate delivery as an option ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-xpo'),
                'id' => 'xpo_quotes_liftgate_delivery_as_option',
                'class' => 'accessorial_service checkbox_fr_add',
            ),
//          Use my liftgate notification
            'avaibility_lift_gate' => array(
                'name' => __('Always include lift gate delivery when a residential address is detected', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_lift_gate'
            ),
            'hold_at_terminal_checkbox_status' => array(
                'name' => __('Hold At Terminal', 'woocommerce-settings-fedex_small'),
                'type' => 'checkbox',
                'desc' => 'Offer Hold At Terminal as an option ' . $hold_at_terminal_package_required,
                'class' => $disable_hold_at_terminal,
                'id' => 'hold_at_terminal_checkbox_status',
            ),
            'hold_at_terminal_fee' => array(
                'name' => __('', 'ground-transit-settings-ground_transit'),
                'type' => 'text',
                'desc' => 'Adjust the price of the Hold At Terminal option. Enter an amount, e.g. 3.75, or a percentage, e.g. 5%.  Leave blank to use the price returned by the carrier.',
                'class' => $disable_hold_at_terminal,
                'id' => 'hold_at_terminal_fee'
            ),
            // Handling Weight
            'xpo_label_handling_unit' => array(
                'name' => __('Handling Unit ', 'estes_freight_wc_settings'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'xpo_label_handling_unit'
            ),
            // weight of handling unit
            'xpo_freight_handling_weight' => array(
                'name' => __('Weight of Handling Unit', 'xpo_wc_settings'),
                'type' => 'text',
                'desc' => 'Enter in pounds the weight of your pallet, skid, crate or other type of handling unit.',
                'id' => 'xpo_freight_handling_weight'
            ),
            // max Handling Weight
            'xpo_freight_maximum_handling_weight' => array(
                'name' => __('Maximum Weight per Handling Unit  ', 'estes_freight_wc_settings'),
                'type' => 'text',
                'desc' => 'Enter in pounds the maximum weight that can be placed on the handling unit.',
                'id' => 'xpo_freight_maximum_handling_weight'
            ),
            // End Hot At Terminal
            'handing_fee_markup_xpo' => array(
                'name' => __('Handling Fee / Markup ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => 'Increases the amount of the returned quote by a specified amount prior to displaying it in the shopping cart. The number entered will be interpreted as dollars and cents unless it is followed by a % sign. For example, entering 5.00 will cause $5.00 to be added to the quotes. Entering 5.00% will cause each quote to be multiplied by 1.05 (= 1 + 5%).',
                'id' => 'wc_settings_xpo_handling_fee'
            ),
            'handing_fee_markup_xpo_2' => array(
                'name' => __('Handling Fee / Markup 2', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => 'Applied after the first handling fee / markup. It further increases the amount of the returned quote by a specified amount prior to displaying it in the shopping cart. The number entered will be interpreted as dollars and cents unless it is followed by a % sign. For example, entering 5.00 will cause $5.00 to be added to the quotes. Entering 5.00% will cause each quote to be multiplied by 1.05 (= 1 + 5%).',
                'id' => 'wc_settings_xpo_handling_fee_2'
            ),
            'en_xpo_enable_logs' => array(
                'name' => __("Enable Logs  ", 'woocommerce-settings-fedex_ltl_quotes'),
                'type' => 'checkbox',
                'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                'id' => 'en_xpo_enable_logs'
            ),
            // Ignore items with the following Shipping Class(es) By (K)
            'en_ignore_items_through_freight_classification' => array(
                'name' => __('Ignore items with the following Shipping Class(es)', 'woocommerce-settings-wwe_quetes'),
                'type' => 'text',
                'desc' => "Enter the <a target='_blank' href = '" . get_admin_url() . "admin.php?page=wc-settings&tab=shipping&section=classes'>Shipping Slug</a> you'd like the plugin to ignore. Use commas to separate multiple Shipping Slug.",
                'id' => 'en_ignore_items_through_freight_classification'
            ),
            'allow_other_plugins_xpo' => array(
                'name' => __('Show WooCommerce Shipping Options ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'select',
                'default' => '3',
                'desc' => __('Permit or prevent the display of shipping rates from other plugins.', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_allow_other_plugins',
                'options' => array(
                    'yes' => __('YES', 'YES'),
                    'no' => __('NO', 'NO'),
                ),
            ),
            'return_XPO_quotes' => array(
                'name' => __('Return LTL freight quotes when an order’s shipment weight exceeds the weight threshold ', 'woocommerce-settings-xpo_quetes'),
                'type' => 'checkbox',
                'desc' => '<span class="description" >When checked, the LTL Freight Quote will return quotes when an order’s total weight exceeds the weight threshold (the maximum permitted by WWE and UPS), even if none of the products have settings to indicate that it will ship LTL Freight. To increase the accuracy of the returned quote(s), all products should have accurate weights and dimensions. </span>',
                'id' => 'en_plugins_return_LTL_quotes',
            ),
            // Cart weight threshold
            'en_weight_threshold_lfq' => array(
                'name' => __('Weight threshold for LTL Freight Quotes ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'default' => $weight_threshold,
                'class' => $weight_threshold_class,
                'desc' => __("", 'woocommerce-settings-xpo_quotes'),
                'id' => 'en_weight_threshold_lfq',
            ),
            'en_suppress_parcel_rates' => array(
                'name' => __("", 'woocommerce-settings-xpo_quotes'),
                'type' => 'radio',
                'default' => 'display_parcel_rates',
                'options' => array(
                    'display_parcel_rates' => __("Continue to display parcel rates when the weight threshold is met.", 'woocommerce-settings-xpo_quotes'),
                    'suppress_parcel_rates' => __("Suppress parcel rates when the weight threshold is met.", 'woocommerce-settings-xpo_quotes'),
                ),
                'class' => 'en_suppress_parcel_rates',
                'id' => 'en_suppress_parcel_rates',
            ),
            'unable_retrieve_shipping_clear_xpo' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce-settings-xpo-quotes'),
                'desc' => '',
                'id' => 'unable_retrieve_shipping_clear_xpo',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            'unable_retrieve_shipping_xpo' => array(
                'name' => __('Checkout options if the plugin fails to return a rate ', 'woocommerce-settings-xpo_quetes'),
                'type' => 'title',
                'desc' => 'Choose what you want to happen if the plugin fails to return a rate and no other shipping estimates are provided by an alternate source. You can choose to allow the shopper to complete the checkout, or prevent the shopper from doing so. Be sure to identify the message you want presented to the shopper.',
                'id' => 'wc_settings_unable_retrieve_shipping_xpo',
            ),
            'pervent_checkout_proceed_xpo' => array(
                'name' => __('', 'woocommerce-settings-xpo_quetes'),
                'type' => 'radio',
                'id' => 'pervent_checkout_proceed_xpo_packages',
                'options' => array(
                    'allow' => __('', 'woocommerce'),
                    'prevent' => __('', 'woocommerce'),
                ),
                'id' => 'wc_pervent_proceed_checkout_eniture',
            ),
            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_quote_section_end'
            )
        );
        return $settings;
    }

}
