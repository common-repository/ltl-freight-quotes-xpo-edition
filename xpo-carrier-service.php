<?php

/**
 * XPO WooComerce Getting Shipping Quotes | Getting Carriers
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooComerce Shipping Quotes Class | Getting Shipping Carriers
 */
class xpo_get_shipping_quotes extends Xpo_Quotes_Liftgate_As_Option
{

    /**
     * End Point URL
     * @var string
     */
    private $EndPointURL = XPO_FREIGHT_DOMAIN_HITTING_URL . "/index.php";
    public $localdeliver;
    public $en_wd_origin_array;
    public $quote_settings;
    public $en_accessorial_excluded;

    function __construct()
    {
        $this->quote_settings = array();
    }

    /**
     * Create Shipping Package
     * @param $packages
     * @return array/string
     */
    function xpo_shipping_array($packages, $package_plugin = "")
    {
        // FDO
        $EnXpoFdo = new EnXpoFdo();
        $en_fdo_meta_data = array();

        $wc_change_class = new Woo_Update_Changes_Xpo();
        $destinationAddressXpo = $this->destinationAddressXpo();
        $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");

        $accessorial = array();
        $this->en_wd_origin_array = (isset($packages['origin'])) ? $packages['origin'] : array();

        $wc_residential = get_option('wc_settings_xpo_residential');
        $wc_liftgate = get_option('wc_settings_xpo_liftgate');

        ($wc_residential == 'yes') ? $accessorial['RSD'] = 'RSD' : "";
        ($wc_liftgate == 'yes') ? $accessorial['DLG'] = 'DLG' : "";
        ($this->quote_settings['liftgate_delivery_option'] == "yes") ? $accessorial['DLG'] = 'DLG' : "";

        $origin = ($packages['origin']);
        // Cuttoff Time
        $shipment_week_days = "";
        $order_cut_off_time = "";
        $shipment_off_set_days = "";
        $modify_shipment_date_time = "";
        $store_date_time = "";
        $xpo_delivery_estimates = get_option('xpo_delivery_estimates');
        $shipment_week_days = $this->xpo_shipment_week_days();
        if ($xpo_delivery_estimates == 'delivery_days' || $xpo_delivery_estimates == 'delivery_date') {
            $order_cut_off_time = $this->quote_settings['orderCutoffTime'];
            $shipment_off_set_days = $this->quote_settings['shipmentOffsetDays'];
            $modify_shipment_date_time = ($order_cut_off_time != '' || $shipment_off_set_days != '' || (is_array($shipment_week_days) && count($shipment_week_days) > 0)) ? 1 : 0;
            $store_date_time = $today = date('Y-m-d H:i:s', current_time('timestamp'));
        }
        $domain = xpo_freight_get_domain();
        $aPluginVersions = $this->xpo_wc_version_number();

        $postal_code = get_option('wc_settings_xpo_zipcode');
        $sender_zip = $origin['zip'];
        $third_party_no = get_option('wc_settings_xpo_third_party_acc');

        // check if ODFL Account number is given on warehouse/dropship against each warehouse/dropship
        $account_number = get_option('wc_settings_xpo_customer_number');
        $test_connection_zipcode = get_option('wc_settings_xpo_zipcode');
        $origin_specific_account = isset($packages['origin']['xpo_account']) ? $packages['origin']['xpo_account'] : '';
        $physicalZipCode = get_option('wc_settings_xpo_zipcode');
        // Start: check billing zip code is same with sender zip and then select request_type
        if ($postal_code == $sender_zip || (strlen($third_party_no) > 0 && strlen($origin_specific_account) > 0 && $origin_specific_account != $third_party_no)) {
            $request_type = "shipper";
        } else {
            $request_type = (strlen($third_party_no) > 0) ? "thirdParty" : "shipper";
        }
        //End: check
        // check if ODFL Account number is given on warehouse/dropship against each warehouse/dropship
        $origin_zip = isset($packages['origin']['zip']) ? $packages['origin']['zip'] : '';
        if ($test_connection_zipcode != $origin_zip && !empty($origin_specific_account)) {
            (isset($packages['request_type'])) ? $request_type = $packages['request_type'] : '';
            $request_type == 'shipper' ? $physicalZipCode = $origin_zip : '';
            $account_number = $origin_specific_account;
        }

        $hazardous = $lineItem = $product_name = $warehouse_appliance_handling_fee = array();

        // check plan for nested material
        $nested_plan = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'nested_material');
        $nestingPercentage = $nestedDimension = $nestedItems = $stakingProperty = [];
        $doNesting = false;
        $product_markup_shipment = 0;

        foreach ($packages['items'] as $item) {
            // Standard Packaging
            $ship_as_own_pallet = isset($item['ship_as_own_pallet']) && $item['ship_as_own_pallet'] == 'yes' ? 1 : 0;
            $vertical_rotation_for_pallet = isset($item['vertical_rotation_for_pallet']) && $item['vertical_rotation_for_pallet'] == 'yes' ? 1 : 0;
            $counter = (isset($item['variantId']) && $item['variantId'] > 0) ? $item['variantId'] : $item['productId'];
            $nmfc_num = (isset($item['nmfc_number'])) ? $item['nmfc_number'] : '';
            $lineItem[$counter] = array(
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemHeight' => $item['productHeight'],
                'lineItemDescription' => $item['productName'],
                'lineItemQuantity' => $item['productQty'],
                'lineItemNMFC' => $nmfc_num,
                // Nested indexes
                'nestingPercentage' => $item['nestedPercentage'],
                'nestingDimension' => $item['nestedDimension'],
                'nestedLimit' => $item['nestedItems'],
                'nestedStackProperty' => $item['stakingProperty'],

                // Shippable handling units
                'lineItemPalletFlag' => $item['lineItemPalletFlag'],
                'lineItemPackageType' => $item['lineItemPackageType'],

                // Standard Packaging
                'shipPalletAlone' => $ship_as_own_pallet,
                'vertical_rotation' => $vertical_rotation_for_pallet
            );
            $lineItem[$counter] = apply_filters('en_fdo_carrier_service', $lineItem[$counter], $item);
            $product_name[] = $item['product_name'];

            isset($item['nestedMaterial']) && !empty($item['nestedMaterial']) &&
            $item['nestedMaterial'] == 'yes' && !is_array($nested_plan) ? $doNesting = 1 : "";

            $lineItem[$counter] = apply_filters('set_warehouse_appliance_handling_fee', $lineItem[$counter], $item);

            if (isset($lineItem[$counter]['en_warehouse_appliance_handling_fee'])) {
                $warehouse_appliance_handling_fee[] = (float)$lineItem[$counter]['en_warehouse_appliance_handling_fee'] * (float)$lineItem[$counter]['lineItemQuantity'];
            }

            if(!empty($item['markup']) && is_numeric($item['markup'])) {
                $product_markup_shipment += $item['markup'];
            }
        }

        // FDO
        $en_fdo_meta_data = $EnXpoFdo->en_cart_package($packages);

        $post_data = array(
            'plateform' => 'WordPress',
            'plugin_version' => $aPluginVersions["xpo_freight_plugin_version"],
            'wordpress_version' => get_bloginfo('version'),
            'woocommerce_version' => $aPluginVersions["woocommerce_plugin_version"],
            'platform' => 'wordpress',
            'requestKey' => md5(microtime() . rand()),
            'licence_key' => get_option('wc_settings_xpo_plugin_licence_key'),
            'sever_name' => $this->xpo_parse_url($domain),
            'carrierName' => 'xpoLogistics',
            'carrier_mode' => 'pro',
            'suspend_residential' => get_option('suspend_automatic_detection_of_residential_addresses'),
            'residential_detecion_flag' => $residential_detecion_flag,
            'UserName' => get_option('wc_settings_xpo_username'),
            'Password' => get_option('wc_settings_xpo_password'),
            'CUSTNMBR' => $account_number,
            'physicalZipCode' => $physicalZipCode,
            'thirdPartyAccountNumber' => get_option('wc_settings_xpo_third_party_acc'),
            'requestType' => $request_type,
            'senderCity' => $origin['city'],
            'senderState' => $origin['state'],
            'senderZip' => $origin['zip'],
            'senderCountryCode' => $this->getCountryCode($origin['country']),
            'receiverCity' => $destinationAddressXpo['city'],
            'receiverState' => $destinationAddressXpo['state'],
            'receiverZip' => $destinationAddressXpo['zip'],
            'receiverCountryCode' => $this->getCountryCode($destinationAddressXpo['country']),
            // warehouse appliance
            'specific_account_enabled' => FALSE,
            'warehouse_appliance_handling_fee' => $warehouse_appliance_handling_fee,
            'sender_origin' => $origin['location'] . ": " . $origin['city'] . ", " . $origin['state'] . " " . $origin['zip'],
            'product_name' => $product_name,
            'sender_location' => $origin['location'],
            'sender_location' => $packages['origin']['location'],
            'sender_zip' => $packages['origin']['zip'],
            'Accessorial' => $accessorial,
            'commdityDetails' => array(
                'handlingUnitDetails' => $lineItem
            ),
            'handlingUnitWeight' => get_option('xpo_freight_handling_weight'),
            'maxWeightPerHandlingUnit' => get_option('xpo_freight_maximum_handling_weight'),

            // FDO
            'en_fdo_meta_data' => $en_fdo_meta_data,
            'doNesting' => $doNesting,
            // Cuttoff Time
            'modifyShipmentDateTime' => $modify_shipment_date_time,
            'OrderCutoffTime' => $order_cut_off_time,
            'shipmentOffsetDays' => $shipment_off_set_days,
            'storeDateTime' => $store_date_time,
            'shipmentWeekDays' => $shipment_week_days,
            'origin_markup' => (isset($packages['origin']['origin_markup'])) ? $packages['origin']['origin_markup'] : 0,
            'product_level_markup' => $product_markup_shipment
        );

        // Liftgate exclude limit based on the liftgate weight restrictions shipping rule
        $shipping_rules_obj = new EnXpoShippingRulesAjaxReq();
        $liftGateExcludeLimit = $shipping_rules_obj->get_liftgate_exclude_limit();
        if (!empty($liftGateExcludeLimit) && $liftGateExcludeLimit > 0) {
            $post_data['liftgateExcludeLimit'] = $liftGateExcludeLimit;
        }

        $post_data = $this->xpo_quotes_update_carrier_service($post_data);
        $post_data = apply_filters("en_woo_addons_carrier_service_quotes_request", $post_data, en_woo_plugin_xpo_quotes);
        $post_data = apply_filters('en_request_handler', $post_data, 'xpo');

//      add basic access token and xpoversion for new xpo api
        $basic_access_token = get_option("wc_settings_xpo_basic_access_token");

        if (isset($basic_access_token) && !empty($basic_access_token) && strlen($basic_access_token) > 0) {
            $post_data["basicAccessToken"] = $basic_access_token;
            $post_data["xpoApiVersion"] = '1.0';
        }
        if (get_option('xpo_quotes_store_type') == "1") {
//          Hazardous Material
            $hazardous_material = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hazardous_material');
            if (!is_array($hazardous_material)) {
                $post_data['Accessorial']['ZHM'] = ($packages['hazardousMaterial'] == 'yes') ? 'ZHM' : '';
                $hazardous[] = 'ZHM';

                // FDO
                $post_data['en_fdo_meta_data'] = array_merge($post_data['en_fdo_meta_data'], $EnXpoFdo->en_package_hazardous($packages, $en_fdo_meta_data));
            }
        } else {
            $post_data['Accessorial']['ZHM'] = ($packages['hazardousMaterial'] == 'yes') ? 'ZHM' : '';
            $hazardous[] = 'ZHM';
        }

        // remove hazardous from accessorial array if it is empty
        if (empty($post_data['Accessorial']['ZHM'])) {
            unset($post_data['Accessorial']['ZHM']);
            unset($hazardous[0]);
        }

//      In-store pickup and local delivery
        $instore_pickup_local_devlivery_action = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
        if (!is_array($instore_pickup_local_devlivery_action)) {
            $post_data = apply_filters('en_xpo_freight_wd_standard_plans', $post_data, $post_data['receiverZip'], $this->en_wd_origin_array, $package_plugin);
        }

//      Hold at terminal
        $hold_at_terminal = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hold_at_terminal');

        if (!is_array($hold_at_terminal)) {
            (isset($this->quote_settings['HAT_status']) && ($this->quote_settings['HAT_status'] == 'yes')) ? $post_data['holdAtTerminal'] = '1' : '';
        }

        ($packages['hazardousMaterial'] == 'yes') ? $post_data['hazardous'] = $hazardous : '';

        // Standard Packaging
        // Configure standard plugin with pallet packaging addon
        $post_data = apply_filters('en_pallet_identify', $post_data);

        do_action("eniture_debug_mood", "Quotes Request (XPO)", $post_data);
        return $post_data;
    }

    /**
     * destinationAddressXpo return receiver details
     * @return array type
     */
    function destinationAddressXpo()
    {
        $en_order_accessories = apply_filters('en_order_accessories', []);
        if (isset($en_order_accessories) && !empty($en_order_accessories)) {
            return $en_order_accessories;
        }
        $wc_change_class = new Woo_Update_Changes_Xpo();
        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $wc_change_class->xpo_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $wc_change_class->xpo_state();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $wc_change_class->xpo_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $wc_change_class->xpo_getCity();
        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country
        );
    }

    /**
     * XPO Line Items
     * @param $packages
     * @return array
     */
    function xpo_get_line_items($packages)
    {
        $lineItem = array();
        foreach ($packages['items'] as $item) {
            $lineItem[] = array(
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemHeight' => $item['productHeight'],
                'lineItemDescription' => $item['productName'],
                'lineItemQuantity' => $item['productQty'],
            );
        }
        return $lineItem;
    }

    /**
     * Check LTL Class For Product
     * @param $slug
     * @param $values
     * @return string
     * @global $woocommerce
     */
    function xpo_product_with_ltl_class($slug, $values)
    {
        global $woocommerce;
        $product_in_cart = false;
        $_product = $values['data'];
        $terms = get_the_terms($_product->get_id(), 'product_shipping_class');

        if ($terms) {
            foreach ($terms as $term) {
                $_shippingclass = "";
                $_shippingclass = $term->slug;
                if ($slug === $_shippingclass) {
                    $product_in_cart[] = $_shippingclass;
                }
            }
        }
        return $product_in_cart;
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return Warehouse Address
     */
    function xpo_multi_warehouse($warehous_list)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->xpo_origin_array($warehous_list);
        }

        $xpo_distance_request = new Get_xpo_freight_distance();
        $accessLevel = "MultiDistance";
        $response_json = $xpo_distance_request->xpo_freight_address($warehous_list, $accessLevel, $this->destinationAddressXpo());
        $response_obj = json_decode($response_json);
        return $this->xpo_origin_array($response_obj->origin_with_min_dist);
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return Warehouse Address Array
     */
    function xpo_origin_array($origin)
    {
//      In-store pickup and local delivery
        if (has_filter("en_xpo_freight_wd_origin_array_set")) {
            return apply_filters("en_xpo_freight_wd_origin_array_set", $origin);
        }
        $zip = $origin->zip;
        $city = $origin->city;
        $state = $origin->state;
        $country = $origin->country;
        $location = $origin->location;
        $locationId = $origin->id;

//      Minify Me
        return array('locationId' => $locationId, 'zip' => $zip, 'city' => $city, 'state' => $state, 'location' => $location, 'country' => $country, 'sender_origin' => $location . ": " . $city . ", " . $state . " " . $zip,);
    }

    /**
     * Refine URL
     * @param $domain
     * @return Domain URL
     */
    function xpo_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = parse_url($domain);
        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }
        $parse = parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);
        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            if(phpversion() < 8) {
                $refinded_domain_name = implode($domain_array, '.');
            }else {
                $refinded_domain_name = implode('.', $domain_array);
            }
        }
        return $refinded_domain_name;
    }

    /**
     *
     * @return type
     */
    function return_xpo_localdelivery_array()
    {
        return $this->localdeliver;
    }

    /**
     * Curl Request To Get Quotes
     * @param $request_data
     * @return array/Json
     */
    function xpo_get_web_quotes($request_data, $xpo_package, $loc_id)
    {
//      check response from session
        $srequest_data = $request_data;
        $srequest_data['requestKey'] = "";
        $currentData = md5(json_encode($srequest_data));
        $requestFromSession = WC()->session->get('previousRequestData');
        $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();

        if (isset($requestFromSession[$currentData]) && (!empty($requestFromSession[$currentData]))) {
            do_action("eniture_debug_mood", "Build Query (XPO)", http_build_query($request_data));
            do_action("eniture_debug_mood", "Quotes Response (XPO)", json_decode($requestFromSession[$currentData]));

            $this->localdeliver = (isset(json_decode($requestFromSession[$currentData])->InstorPickupLocalDelivery)) ? json_decode($requestFromSession[$currentData])->InstorPickupLocalDelivery : '';
            return $this->parse_xpo_output($requestFromSession[$currentData], $request_data, $xpo_package, $loc_id);
        }

        if (is_array($request_data) && count($request_data) > 0 && !empty($request_data['senderZip'])) {
            $xpo_curl_obj = new XPO_Curl_Request();
            $output = $xpo_curl_obj->xpo_get_curl_response($this->EndPointURL, $request_data);
//          set response in session
            $response = json_decode($output);
            // preferred origin
            if (is_plugin_active('preferred-origin/preferred-origin.php')) {
                 apply_filters('en_check_response', $response->severity);
            }
            do_action("eniture_debug_mood", "Build Query (XPO)", http_build_query($request_data));
            do_action("eniture_debug_mood", "Quotes Response (XPO)", $response);

            $this->localdeliver = (isset($response->InstorPickupLocalDelivery)) ? $response->InstorPickupLocalDelivery : '';
            if (isset($response->q) &&
                (empty($response->q->Error))) {
                if (isset($response->autoResidentialSubscriptionExpired) &&
                    ($response->autoResidentialSubscriptionExpired == 1)) {
                    $flag_api_response = "no";
                    $srequest_data['residential_detecion_flag'] = $flag_api_response;
                    $currentData = md5(json_encode($srequest_data));
                }

                $requestFromSession[$currentData] = $output;
                WC()->session->set('previousRequestData', $requestFromSession);
            }

            return $this->parse_xpo_output($output, $request_data, $xpo_package, $loc_id);
        }
    }

    /**
     * @return shipment days of a week  - Cuttoff time
     */
    public function xpo_shipment_week_days()
    {
        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_xpo') == 'yes') {
            return $shipment_days_of_week;
        }
        if (get_option('monday_shipment_day_xpo') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_xpo') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_xpo') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_xpo') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_xpo') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $output
     * @return Single Quote Array
     */
    function parse_xpo_output($output, $request_data, $xpo_package, $loc_id)
    {
        $result = json_decode($output);

        // Apply Override rates shipping rules
        $result = (new EnXpoShippingRulesAjaxReq())->apply_shipping_rules($xpo_package, true, $result, $loc_id);

        // FDO
        $en_fdo_meta_data = (isset($request_data['en_fdo_meta_data'])) ? $request_data['en_fdo_meta_data'] : '';
        if (isset($result->fdo_handling_unit)) {
            $en_fdo_meta_data['handling_unit_details'] = $result->fdo_handling_unit;
        }

        // Standard Packaging
        $standard_packaging = isset($result->standardPackagingData) ? $result->standardPackagingData : [];
        // Cuttoff Time
        $delivery_time_stamp = (isset($result->q->deliveryTimestamp)) ? $result->q->deliveryTimestamp : '';
        $delivery_estimates = (isset($result->q->totalTransitTimeInDays)) ? $result->q->totalTransitTimeInDays : '';
        if (!strlen($delivery_time_stamp) > 0) {
            $delivery_time_stamp = (isset($result->q->deliveryDate)) ? $result->q->deliveryDate : '';
        }

        $accessorials = array();
        $hat_accessorials = array();

        ($this->quote_settings['liftgate_delivery'] == "yes") ? $accessorials[] = "L" : "";
        ($this->quote_settings['residential_delivery'] == "yes") ? $accessorials[] = "R" : "";
        (isset($this->quote_settings['handling_fee']) && !empty($this->quote_settings['handling_fee']) || isset($this->quote_settings['handling_fee_2']) && !empty($this->quote_settings['handling_fee_2'])) ? $accessorials[] = "HF" : "";
        (isset($request_data['hazardous']) && is_array($request_data['hazardous']) && !empty($request_data['hazardous'])) ? $accessorials[] = "H" : "";
        
        // Excluded accessoarials
        $excluded = false;
        if (isset($result->liftgateExcluded) && $result->liftgateExcluded == 1) {
            $this->quote_settings['liftgate_delivery'] = 'no';
            $this->quote_settings['liftgate_resid_delivery'] = "no";
            $this->en_accessorial_excluded = ['liftgateResidentialExcluded'];
            add_filter('en_xpo_accessorial_excluded', [$this, 'en_xpo_accessorial_excluded'], 10, 1);
            $en_fdo_meta_data['accessorials']['residential'] = false;
            $en_fdo_meta_data['accessorials']['liftgate'] = false;
            $excluded = true;
        }

        $label_sufex_arr = $this->filter_label_sufex_array_xpo_quotes($result);
        $sandbox = "";
        
        if (isset($result->q) && empty($result->q->Error)) {

            $charges = isset($result->q->NetCharge) && !empty($result->q->NetCharge) ? $result->q->NetCharge : (isset($result->q, $result->q->totalNetCharge) ? $result->q->totalNetCharge : 0);
            $price = 0;
            if (!is_string($charges)) {
                $charges = json_decode(json_encode($charges), true);
                if (count($charges) > 1) {
                    if (isset($charges['currency'])) {
                        $charges = [$charges];
                    }

                    foreach ($charges as $key => $charge) {
                        if (!$price > 0 && isset($charge['currency'], $charge['0']) && $charge['currency'] == 'USD') {
                            $price = $charge['0'];
                        }
                    }
                } else {
                    $price = isset($result->q, $result->q->NetCharge, $result->q->NetCharge->{0}) ? $result->q->NetCharge->{0} : 0;
                }
            } else {
                $price = $charges;
            }

            if (isset($price['currency'], $price['0']) && $price['currency'] == 'USD') {
                $price = $price['0'];
            }

            $transit = isset($result->q->transitDays) && !empty($result->q->transitDays) ? $result->q->transitDays : $result->q->TransitTime[0];

            $surcharges = isset($result->q->AccessorialCharges->OtherAccessorialCharges) && !empty($result->q->AccessorialCharges->OtherAccessorialCharges) ? $result->q->AccessorialCharges->OtherAccessorialCharges['1'] : (array)$result->q->surcharges;

            $fee = isset($surcharges) && (is_array($surcharges)) ? $surcharges['liftgateFee'] : $surcharges;

            if (isset($result->t)) {
                $sandbox = ' (Sandbox) ';
            }

            if (isset($result->q->AccessorialCharges->OtherAccessorialChargesFormated)) {
                $other_accessorial_charges_formated = (!empty($result->q->AccessorialCharges->OtherAccessorialChargesFormated)) ? (array)$result->q->AccessorialCharges->OtherAccessorialChargesFormated : [];
                $selection_for_accessorial = [
                    'DLG' => 'liftgateFee',
                    'RSD' => 'residentialFee',
                ];

                // Get surcharges
                $array_intersected_key = array_intersect_key($other_accessorial_charges_formated, $selection_for_accessorial);
//                $surcharges = !empty($array_intersected_key) ? array_combine($selection_for_accessorial, $array_intersected_key) : [];
                (isset($array_intersected_key['DLG'])) ? $surcharges['liftgateFee'] = $array_intersected_key['DLG'] : '';
                (isset($array_intersected_key['RSD'])) ? $surcharges['residentialFee'] = $array_intersected_key['RSD'] : '';
            } else {
                $getOtherAccessorialCharges = isset($result->q->AccessorialCharges->OtherAccessorialCharges) ? $result->q->AccessorialCharges->OtherAccessorialCharges : [];
                $surcharges = !empty($getOtherAccessorialCharges) ? (is_array($getOtherAccessorialCharges) ? array('liftgateFee' => @end(array_values($getOtherAccessorialCharges))) : array('liftgateFee' => $getOtherAccessorialCharges)) : (isset($result->q->surcharges) ? $this->update_parse_xpo_quotes_output($result->q->surcharges) : array());
            }

            $cost = $price;
            $sender_zip = (isset($request_data['sender_zip'])) ? $request_data['sender_zip'] : '';
            $sender_location = (isset($request_data['sender_location'])) ? $request_data['sender_location'] : '';

            $meta_data['service_type'] = 'xpo';
            $meta_data['plugin_name'] = 'xpo';
            $meta_data['sender_zip'] = $sender_zip;
            $meta_data['sender_location'] = $sender_location;
            $meta_data['accessorials'] = json_encode($accessorials);
            $meta_data['sender_origin'] = $request_data['sender_origin'];
            $meta_data['product_name'] = json_encode($request_data['product_name']);
            // Standard packaging
            $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);

            $XPO_Freight_Shipping = new XPO_Logistics_Shipping();

            // Product level markup
            if (!empty($request_data['product_level_markup'])) {
                $price = $XPO_Freight_Shipping->add_handling_fee($price, $request_data['product_level_markup']);
            }

            // Origin level markup
            if (!empty($request_data['origin_markup'])) {
                $price = $XPO_Freight_Shipping->add_handling_fee($price, $request_data['origin_markup']);
            }

            $quotes = array(
                'id' => $meta_data['service_type'],
                'plugin_name' => $meta_data['service_type'],
                'cost' => $price,
                'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                'transit_time' => $transit,
                // Cuttoff Time
                'delivery_estimates' => $delivery_estimates,
                'delivery_time_stamp' => $delivery_time_stamp,
                'sandbox' => $sandbox,
                'label_sfx_arr' => $label_sufex_arr,
                'surcharges' => $fee,
                'surcharges' => isset($result->q->AccessorialCharges->OtherAccessorialCharges) && !empty($result->q->AccessorialCharges->OtherAccessorialCharges) ?
                    (is_array($result->q->AccessorialCharges->OtherAccessorialCharges) ? array('liftgateFee' => @end(array_values($result->q->AccessorialCharges->OtherAccessorialCharges))) : array('liftgateFee' => $result->q->AccessorialCharges->OtherAccessorialCharges)) :
                    (isset($result->q->surcharges) ? $this->update_parse_xpo_quotes_output($result->q->surcharges) : array()),
                'surcharges' => $surcharges,
                'meta_data' => $meta_data,
                'markup' => $this->quote_settings['handling_fee'],
                'markup2' => $this->quote_settings['handling_fee_2'],
                'plugin_name' => 'xpoLogistics',
                'plugin_type' => 'ltl',
                'owned_by' => 'eniture'
            );

            // FDO
            $en_fdo_meta_data['rate'] = $quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

            $quotes = array_merge($quotes, $meta_data);

            // warehouse appliance
            $quotes = apply_filters('add_warehouse_appliance_handling_fee', $quotes, $request_data);

            $quotes = apply_filters("en_woo_addons_web_quotes", $quotes, en_woo_plugin_xpo_quotes);
            $label_sufex = (isset($quotes['label_sufex'])) ? $quotes['label_sufex'] : array();
            $label_sufex = $this->label_R_freight_view($label_sufex);
            $quotes['label_sufex'] = $label_sufex;

            in_array('R', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

            $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
            if (!$en_check_action_warehouse_appliance && ($this->quote_settings['liftgate_delivery_option'] == "yes") && (!isset($result->liftgateExcluded)) &&
                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                $service = $quotes;
                $quotes['id'] .= "WL";

                (isset($quotes['label_sufex']) &&
                    (!empty($quotes['label_sufex']))) ?
                    array_push($quotes['label_sufex'], "L") : // IF
                    $quotes['label_sufex'] = array("L");       // ELSE

                // FDO
                $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                $quotes['append_label'] = " with lift gate delivery ";

                $liftgate_charge = (isset($service['surcharges']['liftgateFee'])) ? $service['surcharges']['liftgateFee'] : 0;
                $service['cost'] = (isset($service['cost'])) ? $service['cost'] - $liftgate_charge : 0;
                (!empty($service)) && (in_array("R", $service['label_sufex'])) ? $service['label_sufex'] = array("R") : $service['label_sufex'] = array();

                $simple_quotes = $service;

                // FDO
                if (isset($simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                    $simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'] = $service['cost'];
                }
            } else if ($excluded) {
                $simple_quotes = $quotes;
                
                // FDO
                if (isset($simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                    $simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'] = $quotes['cost'];
                }
            }

            $hold_at_terminal = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hold_at_terminal');

            if (!$en_check_action_warehouse_appliance && isset($result->q->holdAtTerminalResponse, $result->q->holdAtTerminalResponse->totalNetCharge) && !is_array($hold_at_terminal) && $this->quote_settings['HAT_status'] == 'yes' || (isset($result->q->holdAtTerminalResponse->severity) && $result->q->holdAtTerminalResponse->severity != 'ERROR')) {
                $hold_at_terminal_fee = (isset($result->q->holdAtTerminalResponse->totalNetCharge)) ? $result->q->holdAtTerminalResponse->totalNetCharge : 0;

                if (isset($this->quote_settings['HAT_fee']) && (strlen($this->quote_settings['HAT_fee']) > 0)) {
                    $XPO_Logistics_Shipping = new XPO_Logistics_Shipping();

                     // Product level markup
                    if (!empty($request_data['product_level_markup'])) {
                        $hold_at_terminal_fee = $XPO_Freight_Shipping->add_handling_fee($hold_at_terminal_fee, $request_data['product_level_markup']);
                    }

                    // Origin level markup
                    if (!empty($request_data['origin_markup'])) {
                        $hold_at_terminal_fee = $XPO_Freight_Shipping->add_handling_fee($hold_at_terminal_fee, $request_data['origin_markup']);
                    }

                    $hold_at_terminal_fee = $XPO_Logistics_Shipping->add_hold_at_terminal_fee($hold_at_terminal_fee, $this->quote_settings['HAT_fee']);
                }

                (is_array($request_data['hazardous']) && !empty($request_data['hazardous'])) ? $hat_accessorials[] = "H" : "";

                $hat_accessorials[] = 'HAT';

                $meta_data['service_type'] = 'FreightHAT';
                $meta_data['plugin_name'] = 'xpo';
                $meta_data['accessorials'] = json_encode($hat_accessorials);
                $meta_data['sender_origin'] = $request_data['sender_origin'];
                $meta_data['product_name'] = json_encode($request_data['product_name']);
                $meta_data['address'] = (isset($result->q->holdAtTerminalResponse->address)) ? json_encode($result->q->holdAtTerminalResponse->address) : array();
                $meta_data['_address'] = (isset($result->q->holdAtTerminalResponse->address, $result->q->holdAtTerminalResponse->custServicePhoneNbr, $result->q->holdAtTerminalResponse->distance)) ? $this->get_address_terminal($result->q->holdAtTerminalResponse->address, $result->q->holdAtTerminalResponse->custServicePhoneNbr, $result->q->holdAtTerminalResponse->distance) : '';
                // Standard packaging
                $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);

                $hold_at_terminal_resp = (isset($result->q->holdAtTerminalResponse)) ? $result->q->holdAtTerminalResponse : [];

                $hat_quotes = array(
                    'id' => $meta_data['service_type'],
                    'cost' => $hold_at_terminal_fee,
                    'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                    'address' => $meta_data['address'],
                    '_address' => $meta_data['_address'],
                    'transit_time' => $transit,
                    // Cuttoff Time
                    'delivery_estimates' => $delivery_estimates,
                    'delivery_time_stamp' => $delivery_time_stamp,
                    'sandbox' => $sandbox,
                    'label_sfx_arr' => $label_sufex_arr,
                    'hat_append_label' => ' with hold at terminal',
                    '_hat_append_label' => $meta_data['_address'],
                    'meta_data' => $meta_data,
                    'markup' => $this->quote_settings['handling_fee'],
                    'markup2' => $this->quote_settings['handling_fee_2'],
                    'plugin_name' => 'xpoLogistics',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                $hat_quotes = array_merge($hat_quotes, $meta_data);

                // warehouse appliance
                $hat_quotes = apply_filters('add_warehouse_appliance_handling_fee', $hat_quotes, $request_data);

                // FDO
                $en_fdo_meta_data['rate'] = $hat_quotes;
                if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                    unset($en_fdo_meta_data['rate']['meta_data']);
                }
                $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                $en_fdo_meta_data['holdatterminal'] = $hold_at_terminal_resp;
                $hat_quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                $accessorials_hat = [
                    'holdatterminal' => true,
                    'residential' => false,
                    'liftgate' => false,
                ];
                if (isset($hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'])) {
                    $hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'] = array_merge($hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'], $accessorials_hat);
                } else {
                    $hat_quotes['meta_data']['en_fdo_meta_data']['accessorials']['holdatterminal'] = true;
                }
            }
        } else {
            return [];
            $sender_zip = (isset($request_data['sender_zip'])) ? $request_data['sender_zip'] : '';
            $sender_location = (isset($request_data['sender_location'])) ? $request_data['sender_location'] : '';

            $meta_data['service_type'] = 'xpo';
            $meta_data['plugin_name'] = 'xpo';
            $meta_data['sender_zip'] = $sender_zip;
            $meta_data['sender_location'] = $sender_location;
            $meta_data['accessorials'] = json_encode($accessorials);
            $meta_data['sender_origin'] = $request_data['sender_origin'];
            $meta_data['product_name'] = json_encode($request_data['product_name']);
            // Standard packaging
            $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);

            $quotes = array(
                'id' => 'no_quotes',
                'plugin_name' => $meta_data['service_type'],
                'cost' => 0,
                'label' => '',
                'label_sfx_arr' => $label_sufex_arr,
                'surcharges' => [],
                'meta_data' => $meta_data,
                'markup' => $this->quote_settings['handling_fee'],
                'markup2' => $this->quote_settings['handling_fee_2'],
                'plugin_name' => 'xpoLogistics',
                'plugin_type' => 'ltl',
                'owned_by' => 'eniture'
            );

            // FDO
            $en_fdo_meta_data['rate'] = $quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

            $quotes = array_merge($quotes, $meta_data);

            // warehouse appliance
            $quotes = apply_filters('add_warehouse_appliance_handling_fee', $quotes, $request_data);

            $quotes = apply_filters("en_woo_addons_web_quotes", $quotes, en_woo_plugin_xpo_quotes);
            $label_sufex = (isset($quotes['label_sufex'])) ? $quotes['label_sufex'] : array();
            $label_sufex = $this->label_R_freight_view($label_sufex);
            $quotes['label_sufex'] = $label_sufex;

            in_array('R', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

            $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
            if (!$en_check_action_warehouse_appliance && ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                $service = $quotes;
                $quotes['id'] .= "WL";

                (isset($quotes['label_sufex']) &&
                    (!empty($quotes['label_sufex']))) ?
                    array_push($quotes['label_sufex'], "L") : // IF
                    $quotes['label_sufex'] = array("L");       // ELSE

                // FDO
                $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                $quotes['append_label'] = " with lift gate delivery ";

                $liftgate_charge = (isset($service['surcharges']['liftgateFee'])) ? $service['surcharges']['liftgateFee'] : 0;
                $service['cost'] = (isset($service['cost'])) ? $service['cost'] - $liftgate_charge : 0;
                (!empty($service)) && (in_array("R", $service['label_sufex'])) ? $service['label_sufex'] = array("R") : $service['label_sufex'] = array();

                $simple_quotes = $service;

                // FDO
                if (isset($simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                    $simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'] = $service['cost'];
                }
            }

            $hold_at_terminal = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hold_at_terminal');

            if (!$en_check_action_warehouse_appliance && !is_array($hold_at_terminal) && $this->quote_settings['HAT_status'] == 'yes') {
                (is_array($request_data['hazardous']) && !empty($request_data['hazardous'])) ? $hat_accessorials[] = "H" : "";
                $hat_accessorials[] = 'HAT';
                $meta_data['service_type'] = 'no_quotes_HAT';
                $meta_data['plugin_name'] = 'xpo';
                $meta_data['accessorials'] = json_encode($hat_accessorials);
                $meta_data['sender_origin'] = $request_data['sender_origin'];
                $meta_data['product_name'] = json_encode($request_data['product_name']);
                $meta_data['address'] = (isset($result->q->holdAtTerminalResponse->address)) ? json_encode($result->q->holdAtTerminalResponse->address) : array();
                $meta_data['_address'] = (isset($result->q->holdAtTerminalResponse->address, $result->q->holdAtTerminalResponse->custServicePhoneNbr, $result->q->holdAtTerminalResponse->distance)) ? $this->get_address_terminal($result->q->holdAtTerminalResponse->address, $result->q->holdAtTerminalResponse->custServicePhoneNbr, $result->q->holdAtTerminalResponse->distance) : '';
                // Standard packaging
                $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);

                $hat_quotes = array(
                    'id' => $meta_data['service_type'],
                    'cost' => 0,
                    'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                    'address' => $meta_data['address'],
                    '_address' => $meta_data['_address'],
                    'label_sfx_arr' => $label_sufex_arr,
                    'hat_append_label' => ' with hold at terminal',
                    '_hat_append_label' => $meta_data['_address'],
                    'meta_data' => $meta_data,
                    'markup' => $this->quote_settings['handling_fee'],
                    'markup2' => $this->quote_settings['handling_fee_2'],
                    'plugin_name' => 'xpoLogistics',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                $hat_quotes = array_merge($hat_quotes, $meta_data);

                // warehouse appliance
                $hat_quotes = apply_filters('add_warehouse_appliance_handling_fee', $hat_quotes, $request_data);
            }

            // FDO
            $en_fdo_meta_data['rate'] = $hat_quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $hat_quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
            $accessorials_hat = [
                'holdatterminal' => true,
                'residential' => false,
                'liftgate' => false,
            ];
            if (isset($hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'])) {
                $hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'] = array_merge($hat_quotes['meta_data']['en_fdo_meta_data']['accessorials'], $accessorials_hat);
            } else {
                $hat_quotes['meta_data']['en_fdo_meta_data']['accessorials']['holdatterminal'] = true;
            }
        }

        (!empty($simple_quotes)) ? $quotes['simple_quotes'] = $simple_quotes : "";
        (!empty($hat_quotes)) ? $quotes['hold_at_terminal_quotes'] = $hat_quotes : "";

        do_action("eniture_debug_mood", "Calculated Rates (XPO)", $quotes);

        return $quotes;
    }

    public function get_address_terminal($address, $phone_nbr, $distance)
    {
        $address_terminal = '';

        $address_terminal .= (isset($distance->text)) ? ' | ' . $distance->text : '';
        $address_terminal .= (isset($address->addressLine1)) ? ' | ' . $address->addressLine1 : '';
        $address_terminal .= (isset($address->addressLine2)) ? $address->addressLine2 : '';
        $address_terminal .= (isset($address->cityName)) ? ' ' . $address->cityName : '';
        $address_terminal .= (isset($address->stateCd)) ? ' ' . $address->stateCd : '';
        $address_terminal .= (isset($address->postalCd)) ? ' ' . $address->postalCd : '';
        $address_terminal .= (strlen($phone_nbr) > 0) ? ' | T: ' . $phone_nbr : '';
        return $address_terminal;
    }

    /**
     * check "R" in array
     * @param array type $label_sufex
     * @return array type
     */
    public function label_R_freight_view($label_sufex)
    {
        if ($this->quote_settings['residential_delivery'] == 'yes' && (in_array("R", $label_sufex))) {
            $label_sufex = array_flip($label_sufex);
            unset($label_sufex['R']);
            $label_sufex = array_keys($label_sufex);
        }

        return $label_sufex;
    }

    /**
     * Change Country Code
     * @param $country
     * @return Country Code
     */
    function getCountryCode($country)
    {
        $countryCode = $country;
        $country = strtolower($country);
        switch ($country) {
            case 'usa':
                $countryCode = 'US';
                break;
            case 'can':
                $countryCode = 'CN';
                break;
            case 'ca':
                $countryCode = 'CN';
                break;
            default:
                $countryCode = strtoupper($country);
                break;
        }
        return $countryCode;
    }

    /**
     * Return woo-commerce and XPO version
     * @return int
     */
    function xpo_wc_version_number()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $xpo_plugin_folder = get_plugins('/' . 'ltl-freight-quotes-xpo-edition');
        $xpo_plugin_file = 'ltl-freight-quotes-xpo-edition.php';
        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $xpo_plugin = (isset($xpo_plugin_folder[$xpo_plugin_file]['Version'])) ? $xpo_plugin_folder[$xpo_plugin_file]['Version'] : "";
        $pluginVersions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "xpo_freight_plugin_version" => $xpo_plugin
        );
        return $pluginVersions;
    }

    /**
     * Accessoarials excluded
     * @param $excluded
     * @return array
    */
    public function en_xpo_accessorial_excluded($excluded)
    {
        return array_merge($excluded, $this->en_accessorial_excluded);
    }
}
