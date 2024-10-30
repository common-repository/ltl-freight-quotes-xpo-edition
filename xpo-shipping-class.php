<?php

/**
 * XPO WooComerce XPO Shipping Calculation Method
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooComerce Shipping Calculation Method
 */
function xpo_logistics_init()
{
    if (!class_exists('XPO_Logistics_Shipping')) {

        /**
         * XPO WooComerce Shipping Calculation Class | Shipping Rates
         */
        class XPO_Logistics_Shipping extends WC_Shipping_Method
        {

            public $forceAllowShipMethodXpo = array();
            public $getPkgObjXpo;
            public $Xpo_Quotes_Liftgate_As_Option;
            public $smpkgFoundErr = array();
            public $smpkgQuoteErr = array();
            public $shipment_type;
            public $FedExSmallRate = array();
            public $instore_pickup_and_local_delivery;
            public $web_service_inst;
            public $package_plugin;
            public $InstorPickupLocalDelivery;
            public $woocommerce_package_rates;
            public $quote_settings;
            public $minPrices;
            public $accessorials;

            // FDO
            public $en_fdo_meta_data = [];
            public $en_fdo_meta_data_third_party = [];

            /**
             * Woocommerce Shipping Field Attributes
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                error_reporting(0);
                $this->Xpo_Quotes_Liftgate_As_Option = new Xpo_Quotes_Liftgate_As_Option();
                $this->id = 'xpo';
                $this->instance_id = absint($instance_id);
                $this->method_title = __('XPO Logistics');
                $this->method_description = __('Shipping rates from XPO Logistics.');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = 'LTL Freight Quotes - XPO Edition';
                $this->init();
            }

            /**
             * Woocommerce Shipping Field Attributes init
             */
            function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Enable Woo-commerce Shipping For XPO
             */
            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'xpo'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'xpo'),
                        'default' => 'no',
                        'id' => 'xpo_enable_disable_shipping'
                    )
                );
            }

            /**
             * quote settings array
             * @global $wpdb $wpdb
             */
            function xpo_quote_settings()
            {
                $this->web_service_inst->quote_settings['label'] = get_option('wc_settings_xpo_label_as');
                $this->web_service_inst->quote_settings['handling_fee'] = get_option('wc_settings_xpo_handling_fee');
                $this->web_service_inst->quote_settings['handling_fee_2'] = get_option('wc_settings_xpo_handling_fee_2');
                $this->web_service_inst->quote_settings['liftgate_delivery'] = get_option('wc_settings_xpo_liftgate');
                $this->web_service_inst->quote_settings['liftgate_delivery_option'] = get_option('xpo_quotes_liftgate_delivery_as_option');
                $this->web_service_inst->quote_settings['residential_delivery'] = get_option('wc_settings_xpo_residential');
                $this->web_service_inst->quote_settings['liftgate_resid_delivery'] = get_option('en_woo_addons_liftgate_with_auto_residential');
                $this->web_service_inst->quote_settings['transit_time'] = get_option('wc_settings_xpo_delivey_estimate');
                $this->web_service_inst->quote_settings['HAT_status'] = get_option('hold_at_terminal_checkbox_status');
                $this->web_service_inst->quote_settings['HAT_fee'] = get_option('hold_at_terminal_fee');
                $this->web_service_inst->quote_settings['dont_sort'] = get_option('shipping_methods_do_not_sort_by_price');
                // Cuttoff Time
                $this->web_service_inst->quote_settings['delivery_estimates'] = get_option('xpo_delivery_estimates');
                $this->web_service_inst->quote_settings['orderCutoffTime'] = get_option('xpo_freight_order_cut_off_time');
                $this->web_service_inst->quote_settings['shipmentOffsetDays'] = get_option('xpo_freight_shipment_offset_days');
                $this->web_service_inst->quote_settings['handling_weight'] = get_option('xpo_freight_handling_weight');
                $this->web_service_inst->quote_settings['maximum_handling_weight'] = get_option('xpo_freight_maximum_handling_weight');

            }

            /**
             * forceAllowShipMethodXpo for third party should be shown or not
             * @param type $forceShowMethods
             * @return type
             */
            public function forceAllowShipMethodXpo($forceShowMethods)
            {
                if (!empty($this->getPkgObjXpo->ValidShipmentsArr) && (!in_array("ltl_freight", $this->getPkgObjXpo->ValidShipmentsArr))) {
                    $this->forceAllowShipMethodXpo[] = "free_shipping";
                    $this->forceAllowShipMethodXpo[] = "valid_third_party";
                } else {
                    $this->forceAllowShipMethodXpo[] = "ltl_shipment";
                }

                $forceShowMethods = array_merge($forceShowMethods, $this->forceAllowShipMethodXpo);
                return $forceShowMethods;
            }

            /**
             * Virtual Products
             */
            public function en_virtual_products()
            {
                global $woocommerce;
                $products = $woocommerce->cart->get_cart();
                $items = $product_name = [];
                foreach ($products as $key => $product_obj) {
                    $product = $product_obj['data'];
                    $is_virtual = $product->get_virtual();

                    if ($is_virtual == 'yes') {
                        $attributes = $product->get_attributes();
                        $product_qty = $product_obj['quantity'];
                        $product_title = str_replace(array("'", '"'), '', $product->get_title());
                        $product_name[] = $product_qty . " x " . $product_title;

                        $meta_data = [];
                        if (!empty($attributes)) {
                            foreach ($attributes as $attr_key => $attr_value) {
                                $meta_data[] = [
                                    'key' => $attr_key,
                                    'value' => $attr_value,
                                ];
                            }
                        }

                        $items[] = [
                            'id' => $product_obj['product_id'],
                            'name' => $product_title,
                            'quantity' => $product_qty,
                            'price' => $product->get_price(),
                            'weight' => 0,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'type' => 'virtual',
                            'product' => 'virtual',
                            'sku' => $product->get_sku(),
                            'attributes' => $attributes,
                            'variant_id' => 0,
                            'meta_data' => $meta_data,
                        ];
                    }
                }

                $virtual_rate = [];

                if (!empty($items)) {
                    $virtual_rate = [
                        'id' => 'en_virtual_rate',
                        'label' => 'Virtual Quote',
                        'cost' => 0,
                    ];

                    $virtual_fdo = [
                        'plugin_type' => 'ltl',
                        'plugin_name' => 'wwe_quests',
                        'accessorials' => '',
                        'items' => $items,
                        'address' => '',
                        'handling_unit_details' => '',
                        'rate' => $virtual_rate,
                    ];

                    $meta_data = [
                        'sender_origin' => 'Virtual Product',
                        'product_name' => wp_json_encode($product_name),
                        'en_fdo_meta_data' => $virtual_fdo,
                    ];

                    $virtual_rate['meta_data'] = $meta_data;

                }

                return $virtual_rate;
            }

            /**
             * Calculate Shipping Rates For XPO
             * @param string $package
             * @return boolean|string
             */
            public function calculate_shipping($package = array(), $eniture_admin_order_action = false)
            {
                if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                    return [];
                }

                $this->package_plugin = get_option('xpo_freight_package');
                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $free_shipping = $this->xpo_shipping_coupon_rate($coupn);
                    if ($free_shipping == 'y')
                        return FALSE;
                }
                $sandbox = "";
                $obj = new Xpo_shipping_get_package();
                $this->getPkgObjXpo = $obj;
                $xpo_res_inst = new xpo_get_shipping_quotes();

                $country = $state = '';
                $destinationAddressXpo = $xpo_res_inst->destinationAddressXpo();
                extract($destinationAddressXpo);
                if (!strlen($country) > 0 || !strlen($state) > 0 || !strlen($city) > 0 || !strlen($zip) > 0) {
                    return [];
                }

                $this->web_service_inst = $xpo_res_inst;

                $this->xpo_quote_settings();

                // Eniture debug mood
                do_action("eniture_debug_mood", "Quote Settings (XPO)", $this->web_service_inst->quote_settings);

                $xpo_package = $obj->group_xpo_shipment($package, $xpo_res_inst);

                add_filter('force_show_methods', array($this, 'forceAllowShipMethodXpo'));

                $this->instore_pickup_and_local_delivery = FALSE;

                $handlng_fee = get_option('wc_settings_xpo_handling_fee');
                $quotes = array();
                $smallQuotes = array();
                $rate = array();
                $smallPluginExist = false;
                $calledMethod = array();
                if (isset($xpo_package['error'])) {
                    return 'error';
                }

                $small_products = [];
                $ltl_products = [];
                $eniturePluigns = json_decode(get_option('EN_Plugins'));
                if (isset($xpo_package) && !empty($xpo_package)) {

                    if ($this->web_service_inst->quote_settings['handling_fee'] == '-100%' || $this->web_service_inst->quote_settings['handling_fee_2'] == '-100%')
                    {
                        $rates = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'xpoLogistics',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        
                        return [];
                    }

                    // Apply Hide Methods Shipping Rules
                    $shipping_rule_obj = new EnXpoShippingRulesAjaxReq();
                    $shipping_rules_applied = $shipping_rule_obj->apply_shipping_rules($xpo_package);
                    if ($shipping_rules_applied) {
                        return [];
                    }

                    $physicalZipCode = get_option('wc_settings_xpo_zipcode');
                    foreach ($xpo_package as $locId => $sPackage) {
                        if (array_key_exists('xpo', $sPackage)) {
                            $origin_zip = isset($sPackage['origin']['zip']) ? $sPackage['origin']['zip'] : '';
                            $origin_specific_account = isset($sPackage['origin']['xpo_account']) ? $sPackage['origin']['xpo_account'] : '';

                            if (!strlen($origin_specific_account) > 0) {
                                $origin_specific_account = get_option('wc_settings_xpo_customer_number');
                                if (strlen($origin_specific_account) > 0) {
                                    $sPackage['origin']['xpo_account'] = $origin_specific_account;
                                    $sPackage['request_type'] = 'thirdParty';
                                }
                            }

                            if ($physicalZipCode != $origin_zip && !strlen($origin_specific_account) > 0) {
                                return [];
                            }

                            $ltl_products[] = $sPackage;
                            $web_service_arr = $xpo_res_inst->xpo_shipping_array($sPackage, $this->package_plugin);
                            $response = $xpo_res_inst->xpo_get_web_quotes($web_service_arr, $xpo_package, $locId);
                            if (empty($response)) {
                                return [];
                            }
                            $quotes[] = $response;
                            $this->InstorPickupLocalDelivery = $xpo_res_inst->return_xpo_localdelivery_array();
                            continue;
                        } elseif (array_key_exists('small', $sPackage)) {
                            $small_products[] = $sPackage;
                        }
                    }

                    if (isset($small_products) && !empty($small_products) && !empty($ltl_products)) {
                        foreach ($eniturePluigns as $enIndex => $enPlugin) {
                            $freightSmallClassName = 'WC_' . $enPlugin;
                            if (!in_array($freightSmallClassName, $calledMethod)) {

                                if (class_exists($freightSmallClassName)) {
                                    $smallPluginExist = true;
                                    $SmallClassNameObj = new $freightSmallClassName();

                                    $package['itemType'] = 'ltl';
                                    $package['sPackage'] = $small_products;
                                    $smallQuotesResponse = $SmallClassNameObj->calculate_shipping($package, true);

                                    (isset($smallQuotesResponse['error'])) ? $this->smpkgFoundErr = 'Small package error' : "";
                                    $smallQuotes[] = $smallQuotesResponse;
                                }
                                $calledMethod[] = $freightSmallClassName;
                            }
                        }
                    }
                }
                if (count($quotes) < 1) {
                    return 'error';
                }

                $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
                if (!$en_check_action_warehouse_appliance && in_array("error", $quotes)) {
                    return 'error';
                }

                $smallQuotes = (is_array($smallQuotes) && (!empty($smallQuotes))) ? reset($smallQuotes) : $smallQuotes;
                $smallMinRate = (is_array($smallQuotes) && (!empty($smallQuotes))) ? current($smallQuotes) : $smallQuotes;

                // Virtual products
                $virtual_rate = $this->en_virtual_products();

                // FDO
                if (isset($smallMinRate['meta_data']['en_fdo_meta_data'])) {

                    if (!empty($smallMinRate['meta_data']['en_fdo_meta_data']) && !is_array($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                        $en_third_party_fdo_meta_data = json_decode($smallMinRate['meta_data']['en_fdo_meta_data'], true);
                        isset($en_third_party_fdo_meta_data['data']) ? $smallMinRate['meta_data']['en_fdo_meta_data'] = $en_third_party_fdo_meta_data['data'] : '';
                    }
                    $this->en_fdo_meta_data_third_party = (isset($smallMinRate['meta_data']['en_fdo_meta_data']['address'])) ? [$smallMinRate['meta_data']['en_fdo_meta_data']] : $smallMinRate['meta_data']['en_fdo_meta_data'];
                }

                $smpkgCost = (isset($smallMinRate['cost'])) ? $smallMinRate['cost'] : 0;

                if (isset($smallMinRate) && (!empty($smallMinRate))) {
                    switch (TRUE) {
                        case (isset($smallMinRate['minPrices'])):
                            $small_quotes = $smallMinRate['minPrices'];
                            break;
                        default :
                            $shipment_zipcode = key($smallQuotes);
                            $small_quotes = array($shipment_zipcode => $smallMinRate);
                            break;
                    }
                }

                $this->quote_settings = $this->web_service_inst->quote_settings;
                $handling_fee = $this->quote_settings['handling_fee'];
                $handling_fee_2 = $this->quote_settings['handling_fee_2'];
                $this->accessorials = array();

                ($this->quote_settings['liftgate_delivery'] == "yes") ? $this->accessorials[] = "L" : "";
                ($this->quote_settings['residential_delivery'] == "yes") ? $this->accessorials[] = "R" : "";

                $rates = [];
                // Virtual products
                if (count($quotes) > 1 || $smpkgCost > 0 || !empty($virtual_rate)) {

                    $multi_cost = 0;
                    $s_multi_cost = 0;
                    $hold_at_terminal_fee = 0;
                    $_label = "";
                    $this->minPrices = array();

                    $this->quote_settings['shipment'] = "multi_shipment";
                    $shipment_numbers = 0;

                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['XPO_LIFT'] = $small_quotes : "";
                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['XPO_NOTLIFT'] = $small_quotes : "";
                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['XPO_HAT'] = $small_quotes : "";

                    // Virtual products
                    if (!empty($virtual_rate)) {
                        $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                        $virtual_meta_rate['virtual_rate'] = $virtual_rate;
                        $this->minPrices['XPO_LIFT'] = isset($this->minPrices['XPO_LIFT']) && !empty($this->minPrices['XPO_LIFT']) ? array_merge($this->minPrices['XPO_LIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                        $this->minPrices['XPO_NOTLIFT'] = isset($this->minPrices['XPO_NOTLIFT']) && !empty($this->minPrices['XPO_NOTLIFT']) ? array_merge($this->minPrices['XPO_NOTLIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                        $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                        if ($this->quote_settings['HAT_status'] == 'yes') {
                            $this->minPrices['XPO_HAT'] = isset($this->minPrices['XPO_HAT']) && !empty($this->minPrices['XPO_HAT']) ? array_merge($this->minPrices['XPO_HAT'], $virtual_meta_rate) : $virtual_meta_rate;
                        }
                    }

                    $min_quotes = [];
                    foreach ($quotes as $key => $quote) {
                        if (!empty($quote) && is_array($quote)) {
                            $key = "LTL_" . $key;
                            if (isset($quote['hold_at_terminal_quotes'])) {
                                $hold_at_terminal_quotes = $quote['hold_at_terminal_quotes'];
                                $this->minPrices['XPO_HAT'][$key] = $hold_at_terminal_quotes;

                                // FDO
                                $this->en_fdo_meta_data['XPO_HAT'][$key] = (isset($hold_at_terminal_quotes['meta_data']['en_fdo_meta_data'])) ? $hold_at_terminal_quotes['meta_data']['en_fdo_meta_data'] : [];

                                $hold_at_terminal_fee += $hold_at_terminal_quotes['cost'];
                                unset($quote['hold_at_terminal_quotes']);
                                $append_hat_label = (isset($hold_at_terminal_quotes['hat_append_label'])) ? $hold_at_terminal_quotes['hat_append_label'] : "";
                                $append_hat_label = (isset($hold_at_terminal_quotes['_hat_append_label']) && (strlen($append_hat_label) > 0)) ? $append_hat_label . $hold_at_terminal_quotes['_hat_append_label'] : $append_hat_label;
                                $hat_label = array();
                            }

                            $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                            $quote = $this->remove_array($quote, 'simple_quotes');

                            $rates = (is_array($quote) && (!empty($quote))) ? $quote : array();

                            (isset($rates['meta_data']['min_quotes'])) ? $min_quotes = array_merge($min_quotes, $rates['meta_data']['min_quotes']) : '';
                            $this->minPrices['XPO_LIFT'][$key] = $rates;

                            // FDO
                            $this->en_fdo_meta_data['XPO_LIFT'][$key] = (isset($rates['meta_data']['en_fdo_meta_data'])) ? $rates['meta_data']['en_fdo_meta_data'] : [];

                            $_cost = (isset($rates['cost'])) ? $rates['cost'] : 0;

                            $_label = (isset($rates['label_sufex'])) ? $rates['label_sufex'] : array();
                            $append_label = (isset($rates['append_label'])) ? $rates['append_label'] : "";
//                          Offer lift gate delivery as an option is enabled
                            if (isset($this->quote_settings['liftgate_delivery_option']) &&
                                ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                                (!empty($simple_quotes))) {
                                $s_rates = $simple_quotes;
                                $this->minPrices['XPO_NOTLIFT'][$key] = $s_rates;

                                // FDO
                                $this->en_fdo_meta_data['XPO_NOTLIFT'][$key] = (isset($s_rates['meta_data']['en_fdo_meta_data'])) ? $s_rates['meta_data']['en_fdo_meta_data'] : [];

                                $s_cost = (isset($s_rates['cost'])) ? $s_rates['cost'] : 0;
                                $s_label = (isset($s_rates['label_sufex'])) ? $s_rates['label_sufex'] : array();
                                $s_append_label = (isset($s_rates['append_label'])) ? $s_rates['append_label'] : "";
                                $s_cost = $this->add_handling_fee($s_cost, $handling_fee);
                                $s_cost = $this->add_handling_fee($s_cost, $handling_fee_2);
                                $s_multi_cost += $s_cost;
                                $this->minPrices['XPO_NOTLIFT'][$key]['cost'] = $s_cost;
                                $this->en_fdo_meta_data['XPO_NOTLIFT'][$key]['rate']['cost'] = $s_cost;
                            }

                            $_cost = $this->add_handling_fee($_cost, $handling_fee);
                            $_cost = $this->add_handling_fee($_cost, $handling_fee_2);
                            $multi_cost += $_cost;
                            $this->minPrices['XPO_LIFT'][$key]['cost'] = $_cost;
                            $this->en_fdo_meta_data['XPO_LIFT'][$key]['rate']['cost'] = $_cost;
                            $shipment_numbers++;
                        }
                    }

                    $this->quote_settings['shipment_numbers'] = $shipment_numbers;
                    // Excluded accessorials
                    $en_accessorial_excluded = apply_filters('en_xpo_accessorial_excluded', []);
                    if ($s_multi_cost > 0 && !empty($en_accessorial_excluded) && in_array('liftgateResidentialExcluded', $en_accessorial_excluded)) {
                        $multi_cost = 0;
                    }

                    ($s_multi_cost > 0) ? $rate[] = $this->arrange_multiship_freight(($s_multi_cost + $smpkgCost), 'XPO_NOTLIFT', $s_label, $s_append_label) : "";
                    ($multi_cost > 0 || $smpkgCost > 0) ? $rate[] = $this->arrange_multiship_freight(($multi_cost + $smpkgCost), 'XPO_LIFT', $_label, $append_label, $min_quotes) : "";
                    ($hold_at_terminal_fee > 0) ? $rate[] = $this->arrange_multiship_freight(($hold_at_terminal_fee + $smpkgCost), 'XPO_HAT', $hat_label, $append_hat_label) : "";

                    $rates = $rate;

                    $this->shipment_type = 'multiple';
                } else {

                    $quote = (is_array($quotes) && (!empty($quotes))) ? reset($quotes) : array();

                    if (!empty($quote) && is_array($quote)) {
                        if (isset($quote['hold_at_terminal_quotes'])) {
                            $rates[] = $quote['hold_at_terminal_quotes'];
                            unset($quote['hold_at_terminal_quotes']);
                        }

                        $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                        $rates[] = $this->remove_array($quote, 'simple_quotes');

//                      Offer lift gate delivery as an option is enabled
                        if (isset($this->quote_settings['liftgate_delivery_option']) &&
                            ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                            (!empty($simple_quotes))) {
                            $rates[] = $simple_quotes;
                        }

                        $cost_sorted_key = array();

                        $this->quote_settings['shipment'] = "single_shipment";
                        $this->quote_settings['shipment_numbers'] = "1";

                        if (is_array($rates) && (!empty($rates))) {

                            foreach ($rates as $key => $quote) {
                                $_cost = (isset($quote['cost'])) ? $quote['cost'] : 0;

                                if (!isset($quote['hat_append_label'])) {
                                    $_cost = $this->add_handling_fee($_cost, $handling_fee);
                                    $_cost = $this->add_handling_fee($_cost, $handling_fee_2);
                                    (isset($rates[$key]['cost'])) ? $rates[$key]['cost'] = $_cost : "";
                                }

                                (isset($rates[$key]['meta_data']['en_fdo_meta_data']['rate']['cost'])) ? $rates[$key]['meta_data']['en_fdo_meta_data']['rate']['cost'] = $_cost : "";
                                $cost_sorted_key[$key] = (isset($quote['cost'])) ? $quote['cost'] : 0;
                                (isset($rates[$key]['shipment'])) ? $rates[$key]['shipment'] = "single_shipment" : "";

                                if (isset($rates[$key]['meta_data'])) {
                                    $rates[$key]['meta_data']['min_prices'] = $rates[$key];
                                }
                            }

//                      array_multisort 
                            array_multisort($cost_sorted_key, SORT_ASC, $rates);
                        }
                    }

                    $this->shipment_type = 'single';
                }

                $rates = $this->sort_asec_order_arr($rates);
                $rates = $this->xpo_add_rate_arr($rates);
                // Origin terminal address
                if ($this->shipment_type == 'single') {
                    (isset($this->InstorPickupLocalDelivery->localDelivery) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->web_service_inst->en_wd_origin_array['fee_local_delivery'], $this->web_service_inst->en_wd_origin_array['checkout_desc_local_delivery'], $this->web_service_inst->en_wd_origin_array) : "";
                    (isset($this->InstorPickupLocalDelivery->inStorePickup, $this->InstorPickupLocalDelivery->totalDistance) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->web_service_inst->en_wd_origin_array['checkout_desc_store_pickup'], $this->web_service_inst->en_wd_origin_array, $this->InstorPickupLocalDelivery->totalDistance) : "";
                }

                return $rates;
            }

            /**
             * Multi-shipment
             * @return array
             */
            function arrange_multiship_freight($cost, $id, $label_sufex, $append_label, $min_quotes = [])
            {

                $multiship = array(
                    // warehouse appliance
                    'plugin_name' => 'xpo',
                    'shipment_type' => 'multiple',
                    'id' => $id,
                    'label' => "Freight",
                    'cost' => $cost,
                    'label_sufex' => $label_sufex,
                    'plugin_name' => 'xpoLogistics',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
                if ($en_check_action_warehouse_appliance) {
                    $multiship['id'] = $this->id;
                }

                ($id == 'XPO_HAT') ? $multiship['hat_append_label'] = $append_label : $multiship['append_label'] = $append_label;

                (isset($min_quotes) && is_array($min_quotes) && !empty($min_quotes)) ? $multiship['meta_data']['min_quotes'] = $min_quotes : '';

                return $multiship;
            }

            /**
             * Remove array
             * @return array
             */
            public function remove_array($quote, $remove_index)
            {
                unset($quote[$remove_index]);

                return $quote;
            }

            /**
             * This function adds handling to the price and returns price back
             * @param string type $price
             * @param string type $handling_fee
             * @return float type
             */
            function add_handling_fee($price, $handling_fee)
            {
                $handling_fee = $price > 0 ? $handling_fee : 0;
                $handelingFee = 0;
                if ($handling_fee != '' && $handling_fee != 0) {
                    if (strrchr($handling_fee, "%")) {

                        $prcnt = (float)$handling_fee;
                        $handelingFee = (float)$price / 100 * $prcnt;
                    } else {
                        $handelingFee = (float)$handling_fee;
                    }
                }

                $handelingFee = $this->smooth_round($handelingFee);
                $price = (float)$price + $handelingFee;
                return $price;
            }

            /**
             *
             * @param string type $price
             * @param string type $handling_fee
             * @return float type
             */
            function add_hold_at_terminal_fee($price, $handling_fee)
            {
                $handelingFee = 0;
                if ($handling_fee != '' && $handling_fee != 0) {
                    if (strrchr($handling_fee, "%")) {

                        $prcnt = (float)$handling_fee;
                        $handelingFee = (float)$price / 100 * $prcnt;
                    } else {
                        $handelingFee = (float)$handling_fee;
                    }
                }

                $handelingFee = $this->smooth_round($handelingFee);
                $price = (float)$price + $handelingFee;
                return $price;
            }

            /**
             *
             * @param float type $val
             * @param int type $min
             * @param int type $max
             * @return float type
             */
            function smooth_round($val, $min = 2, $max = 4)
            {
                $result = round($val, $min);

                if ($result == 0 && $min < $max) {
                    return $this->smooth_round($val, ++$min, $max);
                } else {
                    return $result;
                }
            }

            /**
             * sort array
             * @param array type $rate
             * @return array type
             */
            public function sort_asec_order_arr($rate)
            {
                $price_sorted_key = array();
                foreach ($rate as $key => $cost_carrier) {
                    $price_sorted_key[$key] = (isset($cost_carrier['cost'])) ? $cost_carrier['cost'] : 0;
                }
                array_multisort($price_sorted_key, SORT_ASC, $rate);

                return $rate;
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label, $en_wd_origin_array, $total_distance)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';
                // Origin terminal address
                $address = (isset($en_wd_origin_array['address'])) ? $en_wd_origin_array['address'] : '';
                $city = (isset($en_wd_origin_array['city'])) ? $en_wd_origin_array['city'] : '';
                $state = (isset($en_wd_origin_array['state'])) ? $en_wd_origin_array['state'] : '';
                $zip = (isset($en_wd_origin_array['zip'])) ? $en_wd_origin_array['zip'] : '';
                $phone_instore = (isset($en_wd_origin_array['phone_instore'])) ? $en_wd_origin_array['phone_instore'] : '';
                strlen($total_distance) > 0 ? $label .= ': Free | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                strlen($address) > 0 ? $label .= ' | ' . $address : '';
                strlen($city) > 0 ? $label .= ', ' . $city : '';
                strlen($state) > 0 ? $label .= ' ' . $state : '';
                strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';

                $pickup_delivery = array(
                    'id' => $this->id . ':' . 'in-store-pick-up',
                    'cost' => 0,
                    'label' => $label,
                    'plugin_name' => 'xpoLogistics',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label, $en_wd_origin_array)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';

                $local_delivery = array(
                    'id' => $this->id . ':' . 'local-delivery',
                    'cost' => !empty($cost) ? $cost : 0,
                    'label' => $label,
                    'plugin_name' => 'xpoLogistics',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * filter label new update
             * @param type $label_sufex
             * @return string
             */
            public function filter_from_label_sufex($label_sufex)
            {
                $append_label = "";
                $rad_status = true;
                $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                    if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                        $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                    }
                }
                switch (TRUE) {
                    case(count($label_sufex) == 1):
                        (in_array('L', $label_sufex)) ? $append_label = " with lift gate delivery " : "";
                        (in_array('R', $label_sufex) && $rad_status == true) ? $append_label = " with residential delivery " : "";
                        break;
                    case(count($label_sufex) == 2):
                        (in_array('L', $label_sufex)) ? $append_label = " with lift gate delivery " : "";
                        (in_array('R', $label_sufex) && $rad_status == true) ? $append_label .= (strlen($append_label) > 0) ? " and residential delivery " : " with residential delivery " : "";
                        break;
                }

                return $append_label;
            }

            /**
             * Append label in quote
             * @param array type $rate
             * @return string type
             */
            public function set_label_in_quote($rate)
            {
                $rate_label = "";
                $label_sufex = (isset($rate['label_sufex']) && (!empty($rate['label_sufex']))) ? array_unique($rate['label_sufex']) : array();
                $rate_label = (isset($rate['label'])) ? $rate['label'] : "Freight";
                $rate_label .= $this->filter_from_label_sufex($label_sufex);
                $rate_label .= (isset($rate['hat_append_label'])) ? $rate['hat_append_label'] : "";
                $rate_label .= (isset($rate['_hat_append_label'])) ? $rate['_hat_append_label'] : "";
                $rate_label .= ($this->quote_settings['transit_time'] == "yes" && isset($rate['transit_time'])) ? ' ( Estimated transit time of ' . $rate['transit_time'] . ' business days. )' : "";
                $delivery_estimate_xpo = isset($this->quote_settings['delivery_estimates']) ? $this->quote_settings['delivery_estimates'] : '';
                // Cuttoff Time
                $shipment_type = isset($this->quote_settings['shipment']) && !empty($this->quote_settings['shipment']) ? $this->quote_settings['shipment'] : '';
                if (isset($this->quote_settings['delivery_estimates']) && !empty($this->quote_settings['delivery_estimates'])
                    && $this->quote_settings['delivery_estimates'] != 'dont_show_estimates' && $shipment_type != 'multi_shipment') {
                    if ($this->quote_settings['delivery_estimates'] == 'delivery_date') {
                        isset($rate['delivery_time_stamp']) && is_string($rate['delivery_time_stamp']) && strlen($rate['delivery_time_stamp']) > 0 ? $rate_label .= ' (Expected delivery by ' . date('m-d-Y', strtotime($rate['delivery_time_stamp'])) . ')' : '';
                    } else if ($delivery_estimate_xpo == 'delivery_days') {
                        $correct_word = (isset($rate['delivery_estimates']) && $rate['delivery_estimates'] == 1) ? 'is' : 'are';
                        isset($rate['delivery_estimates']) && is_string($rate['delivery_estimates']) && strlen($rate['delivery_estimates']) > 0 ? $rate_label .= ' (Intransit days: ' . $rate['delivery_estimates'] . ')' : '';
                    }
                }
                return $rate_label;
            }

            /**
             * rates to add_rate woo-commerce
             * @param array type $add_rate_arr
             */
            public function xpo_add_rate_arr($add_rate_arr)
            {
                do_action("eniture_debug_mood", "Add Rate (XPO)", $add_rate_arr);

                if (isset($add_rate_arr) && (!empty($add_rate_arr)) && (is_array($add_rate_arr))) {
                    // Images for FDO
                    $image_urls = apply_filters('en_fdo_image_urls_merge', []);

                    $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
                    add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);

//                  In-store pickup and local delivery
                    $instore_pickup_local_devlivery_action = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                    foreach ($add_rate_arr as $key => $rate) {

                        if (isset($rate['cost']) && $rate['cost'] > 0) {

                            $rate['label'] = $this->set_label_in_quote($rate);

                            if (isset($rate['meta_data'])) {
                                $rate['meta_data']['label_sufex'] = (isset($rate['label_sufex'])) ? json_encode($rate['label_sufex']) : array();
                            }

                            if (isset($this->minPrices[$rate['id']])) {
                                $rate['meta_data']['min_prices'] = json_encode($this->minPrices[$rate['id']]);
                                $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data[$rate['id']]);
                                (!empty($this->en_fdo_meta_data_third_party)) ? $rate['meta_data']['en_fdo_meta_data']['data'] = array_merge($rate['meta_data']['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                                $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                            } else {
                                $en_set_fdo_meta_data['data'] = [$rate['meta_data']['en_fdo_meta_data']];
                                $en_set_fdo_meta_data['shipment'] = 'sinlge';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
                            }

                            // Images for FDO
                            $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                            $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';

                            if (!$en_check_action_warehouse_appliance && $this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action)) && $this->shipment_type != "multiple") {
                                $rate = apply_filters('suppress_local_delivery', $rate, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);
                                if (!empty($rate)) {
                                    $this->add_rate($rate);
                                    $this->woocommerce_package_rates = 1;
                                    $add_rate_arr[$key] = $rate;
                                }
                            } else {
                                $this->add_rate($rate);
                                $add_rate_arr[$key] = $rate;
                            }
                        }
                    }
                }
                return $add_rate_arr;
            }

            /**
             * final rates sorting
             * @param array type $rates
             * @param array type $package
             * @return array type
             */
            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
//              if there are no rates don't do anything
                if (!$rates) {
                    return [];
                }
//              check the option to sort shipping methods by price on quote settings 
                if (get_option('shipping_methods_do_not_sort_by_price') != 'yes') {

//                  get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }
//                  use the prices to sort the rates
                    array_multisort($prices, $rates);
                }
//              return the rates
                return $rates;
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function xpo_shipping_coupon_rate($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $rates = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0
                        );
                        $this->add_rate($rates);
                        return 'y';
                    }
                }
                return 'n';
            }

        }

    }
}
