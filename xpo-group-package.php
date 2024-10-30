<?php

/**
 * XPO WooCommerce Get Shipping Package Class
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooCommerce Get Shipping Package Class
 */
class Xpo_shipping_get_package
{

    public $ValidShipmentsXpo = 0;
    public $ValidShipmentsArr = [];

    // Micro Warehouse
    public $products = [];
    public $dropship_location_array = [];
    public $warehouse_products = [];
    public $destination_Address_xpo;
    public $origin;

    /**
     * hasLTLShipment
     * @var int
     */
    public $hasLTLShipment = 0;

    /**
     * Errors Array
     * @var varchar
     */
    public $errors = [];

    // Images for FDO
    public $en_fdo_image_urls = [];

    /**
     * Error
     * @var varchar
     */
    public $Error = [];
    /**
     * Grouping For Shipments
     * @param $package
     * @param $xpo_res_inst
     * @return Shipment Grouped Array
     * @global $wpdb
     */
    function group_xpo_shipment($package, $xpo_res_inst)
    {
        $xpo_package = [];
        $wc_change_class = new Woo_Update_Changes_Xpo();
        global $wpdb;
        $weight = 0;
        $xpo_freight_class = "";
        $xpo_enable = false;
        $counter_xpo = 0;

        $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
        $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
        $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];

        // Micro Warehouse
        $smallPluginExist = 0;
        $xpo_package = $items = $items_shipment = [];
        $xpo_get_shipping_quotes = new xpo_get_shipping_quotes();
        $this->destination_Address_xpo = $xpo_get_shipping_quotes->destinationAddressXpo();

        // Standard Packaging
        $en_ppp_pallet_product = apply_filters('en_ppp_existence', false);

        $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);
        foreach ($package['contents'] as $item_id => $values) {
            $_product = $values['data'];
            $parent_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            if(isset($values['variation_id']) && $values['variation_id'] > 0){
                $variation = wc_get_product($values['variation_id']);
                $parent_id = $variation->get_parent_id();
            }
            // Images for FDO
            $this->en_fdo_image_urls($values, $_product);

            // Flat rate pricing
            $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
            if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                continue;
            }

            // Get product shipping class
            $en_ship_class = strtolower($values['data']->get_shipping_class());
            if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                continue;
            }

            // Shippable handling units
            $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
            $shippable = [];
            if (isset($values['shippable']) && !empty($values['shippable'])) {
                $shippable = $values['shippable'];
            }

            // Standard Packaging
            $ppp_product_pallet = [];
            $values = apply_filters('en_ppp_request', $values, $values, $_product);
            if (isset($values['ppp']) && !empty($values['ppp'])) {
                $ppp_product_pallet = $values['ppp'];
            }

            $ship_as_own_pallet = $vertical_rotation_for_pallet = 'no';
            if (!$en_ppp_pallet_product) {
                $ppp_product_pallet = [];
            }

            extract($ppp_product_pallet);

            //nesting start
            $nestedPercentage = 0;
            $nestedDimension = "";
            $nestedItems = "";
            $StakingProperty = "";

            $dimension_unit = get_option('woocommerce_dimension_unit');

            // Convert product dimensions in feet ,centimeter,miles,kilometer into Inches
            if ($dimension_unit == 'ft' || $dimension_unit == 'cm' || $dimension_unit == 'mi' || $dimension_unit == 'km') {
                $dimensions = $this->dimensions_conversion($_product);
                $height = $dimensions['height'];
                $width = $dimensions['width'];
                $length = $dimensions['length'];
            } else {
                $p_height = str_replace( array( "'",'"' ),'',$_product->get_height());
                $p_width = str_replace( array( "'",'"' ),'',$_product->get_width());
                $p_length = str_replace( array( "'",'"' ),'',$_product->get_length());
                $height = is_numeric($p_height) ? $p_height : 0;
                $width = is_numeric($p_width) ? $p_width : 0;
                $length = is_numeric($p_length) ? $p_length : 0;
                $height = ceil(wc_get_dimension($height, 'in'));
                $width = ceil(wc_get_dimension($width, 'in'));
                $length = ceil(wc_get_dimension($length, 'in'));
            }

            $height = (strlen($height) > 0) ? $height : "0";
            $width = (strlen($width) > 0) ? $width : "0";
            $length = (strlen($length) > 0) ? $length : "0";

            $product_weight = round(wc_get_weight($_product->get_weight(), 'lbs'), 2);
            $weight = $product_weight * $values['quantity'];
            $dimensions = (($length * $values['quantity']) * $width * $height);
            $freightClassXpo = $_product->get_shipping_class();

            if ($_product->get_shipping_class() == 'ltl_freight') {
                $xpo_freight_class = $_product->get_shipping_class();
            }

            $locationId = 0;

            // warehouse appliance
            (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
            $origin_address = $this->xpo_get_origin($_product, $values, $xpo_res_inst);

            // custom work for warehouse appliance
            $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance' , FALSE);
            if($en_check_action_warehouse_appliance) {
                $origin_address = apply_filters('en_warehouse_appliance_wharehouse_check', $origin_address, 'xpo');
            }

            // preferred origin
            if (is_plugin_active('preferred-origin/preferred-origin.php')) {
                $origin_address = apply_filters('en_selection_of_locations', $origin_address['zip'], 'xpo');
                if (empty($origin_address) || empty($origin_address['zip'])) {
                    continue;
                }
            }

            $locationId = (isset($origin_address['id'])) ? $origin_address['id'] : ((isset($origin_address['locationId'])) ? $origin_address['locationId'] : '');

            // Micro Warehouse
            (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
            $this->products[] = $post_id;

            $xpo_package[$locationId]['origin'] = $origin_address;
            $get_freight = $this->xpo_get_freight_class($values, $_product);
            $freightClass_ltl_gross = ($get_freight['freight_class'] == 0) ? $get_freight['freight_class'] = "" : $get_freight['freight_class'];

            $hm_plan = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'hazardous_material');
            $hm_status = (!is_array($hm_plan) && $get_freight['hazmat'] == 'yes') ? TRUE : FALSE;

            $nested_material = $this->en_nested_material($values, $_product);

            if ($nested_material == "yes") {
                $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                $nestedPercentage = get_post_meta($post_id, '_nestedPercentage', true);
                $nestedDimension = get_post_meta($post_id, '_nestedDimension', true);
                $nestedItems = get_post_meta($post_id, '_maxNestedItems', true);
                $StakingProperty = get_post_meta($post_id, '_nestedStakingProperty', true);
            }

            // Shippable handling units
            $lineItemPalletFlag = $lineItemPackageCode = $lineItemPackageType = '0';
            extract($shippable);

            $product_title = str_replace(array("'", '"'), '', $_product->get_title());
            $product_level_markup = $this->xpo_ltl_get_product_level_markup($_product, $values['variation_id'], $values['product_id'], $values['quantity']);

            $en_items = [
                'productId' => $parent_id,
                'productName' => str_replace(array("'", '"'), '', $_product->get_name()),
                'productQty' => $values['quantity'],
                'product_name' => $values['quantity'] . " x " . (str_replace(array("'", '"'), '', $_product->get_name())),
                'productPrice' => $_product->get_price(),
                'productWeight' => $product_weight,
                'productLength' => $length,
                'productWidth' => $width,
                'productHeight' => $height,
                'productClass' => $freightClass_ltl_gross,
                'freightClass' => $freightClassXpo,
                'hazardousMaterial' => $hm_status,
                'hazardous_material' => $hm_status,
                'hazmat' => $hm_status,
                'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                'productSku' => $_product->get_sku(),
                'actualProductPrice' => $_product->get_price(),
                'attributes' => $_product->get_attributes(),
                'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',
                'nestedMaterial' => $nested_material,
                'nestedPercentage' => $nestedPercentage,
                'nestedDimension' => $nestedDimension,
                'nestedItems' => $nestedItems,
                'stakingProperty' => $StakingProperty,

                // Shippable handling units
                'lineItemPalletFlag' => $lineItemPalletFlag,
                'lineItemPackageCode' => $lineItemPackageCode,
                'lineItemPackageType' => $lineItemPackageType,

                // Standard Packaging
                'ship_as_own_pallet' => $ship_as_own_pallet,
                'vertical_rotation_for_pallet' => $vertical_rotation_for_pallet,
                'markup' => $product_level_markup
            ];

            // Hook for flexibility adding to package
            $en_items = apply_filters('en_group_package', $en_items, $values, $_product);

            // Micro Warehouse
            $items[$post_id] = $en_items;

            if (!empty($origin_address)) {
                $locationId = (isset($origin_address['id'])) ? $origin_address['id'] : (isset($origin_address['locationId']) ? $origin_address['locationId'] : '');
                $xpo_package[$locationId]['origin'] = $origin_address;

                if (!$_product->is_virtual()) {

                    $xpo_package[$locationId]['items'][$counter_xpo] = $en_items;

                    // warehouse appliance
                    $xpo_package[$locationId]['items'][$counter_xpo] = apply_filters('get_warehouse_appliance_handling_fee', $xpo_package[$locationId]['items'][$counter_xpo], $post_id);

                    $validateProductParamsXpo = $this->validateProductParamsXpo($xpo_package[$locationId]['items'][$counter_xpo]);
                    (isset($validateProductParamsXpo) && ($validateProductParamsXpo === 1)) ? $validShipmentForLtlXpo = 1 : "";
                    $xpo_package[$locationId]['items'][$counter_xpo]['validForLtl'] = $validateProductParamsXpo;
                }

                // Product tags
                $product_tags = get_the_terms($product_id, 'product_tag');
                $product_tags = empty($product_tags) ? get_the_terms($parent_id, 'product_tag') : $product_tags;
                if (!empty($product_tags)) {
                    $product_tag_names = array_map(function($tag) { return $tag->term_id; }, $product_tags);

                    if (isset($xpo_package[$locationId]['product_tags'])) {
                        $xpo_package[$locationId]['product_tags'] = array_merge($xpo_package[$locationId]['product_tags'], $product_tag_names);
                    } else {
                        $xpo_package[$locationId]['product_tags'] = $product_tag_names;
                    }
                } else {
                    $xpo_package[$locationId]['product_tags'] = [];
                }

                // Product quantity
                if (isset($xpo_package[$locationId]['product_quantities'])) {
                    $xpo_package[$locationId]['product_quantities'] += floatval($values['quantity']);
                } else {
                    $xpo_package[$locationId]['product_quantities'] = floatval($values['quantity']);
                }

                // Product price
                if (isset($xpo_package[$locationId]['product_prices'])) {
                    $xpo_package[$locationId]['product_prices'] += (floatval($_product->get_price()) * floatval($values['quantity']));
                } else {
                    $xpo_package[$locationId]['product_prices'] = (floatval($_product->get_price()) * floatval($values['quantity']));
                }
            }

            $xpo_enable = $this->xpo_enable_shipping_class($_product);

            // Micro Warehouse
            $items_shipment[$post_id] = $xpo_enable;

            $exceedWeight = get_option('en_plugins_return_LTL_quotes');

            $xpo_package[$locationId]['shipment_weight'] = isset($xpo_package[$locationId]['shipment_weight']) ? $xpo_package[$locationId]['shipment_weight'] + $weight : $weight;
            $xpo_package[$locationId]['hazardousMaterial'] = isset($xpo_package[$locationId]['hazardousMaterial']) && $xpo_package[$locationId]['hazardousMaterial'] == 'yes' ? $xpo_package[$locationId]['hazardousMaterial'] : $get_freight['hazmat'];

            $xpo_package[$locationId]['validShipmentForLtl'] = $validShipmentForLtlXpo;
            (isset($validShipmentForLtlXpo) && ($validShipmentForLtlXpo === 1)) ? $this->ValidShipmentsXpo = 1 : "";

            $smallPluginExist = 0;
            $calledMethod = [];
            $eniturePluigns = json_decode(get_option('EN_Plugins'));
            foreach ($eniturePluigns as $enIndex => $enPlugin) {
                $freightSmallClassName = 'WC_' . $enPlugin;
                if (!in_array($freightSmallClassName, $calledMethod)) {
                    if (class_exists($freightSmallClassName)) {
                        $smallPluginExist = 1;
                    }
                    $calledMethod[] = $freightSmallClassName;
                }
            }

            // Cart weight threshold
            $cart_weight_threshold = get_option('en_weight_threshold_lfq');
            $cart_weight_threshold = (isset($cart_weight_threshold) && !empty($cart_weight_threshold)) ? $cart_weight_threshold : 150;

            if ($xpo_enable == true || ($xpo_package[$locationId]['shipment_weight'] > $cart_weight_threshold && $exceedWeight == 'yes')) {
                $xpo_package[$locationId]['xpo'] = 1;
                $this->hasLTLShipment = 1;
                $this->ValidShipmentsArr[] = "ltl_freight";
            } elseif (isset($xpo_package[$locationId]['xpo'])) {
                $xpo_package[$locationId]['xpo'] = 1;
                $this->hasLTLShipment = 1;
                $this->ValidShipmentsArr[] = "ltl_freight";
            } elseif ($smallPluginExist == 1) {
                $xpo_package[$locationId]['small'] = 1;
                $this->ValidShipmentsArr[] = "small_shipment";
            } else {
                $this->ValidShipmentsArr[] = "no_shipment";
            }

            $counter_xpo++;
        }

        // Micro Warehouse
        $eniureLicenceKey = get_option('wc_settings_xpo_plugin_licence_key');
        $xpo_package = apply_filters('en_micro_warehouse', $xpo_package, $this->products, $this->dropship_location_array, $this->destination_Address_xpo, $this->origin, 1, $items, $items_shipment, $this->warehouse_products, $eniureLicenceKey, 'xpo');
        do_action("eniture_debug_mood", "Product Detail (xpo)", $xpo_package);
        return $xpo_package;
    }

    /**
     * Set images urls | Images for FDO
     * @param array type $en_fdo_image_urls
     * @return array type
     */
    public function en_fdo_image_urls_merge($en_fdo_image_urls)
    {
        return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
    }

    /**
     * Get images urls | Images for FDO
     * @param array type $values
     * @param array type $_product
     * @return array type
     */
    public function en_fdo_image_urls($values, $_product)
    {
        $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        $gallery_image_ids = $_product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $key => $image_id) {
            $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
        }

        $image_id = $_product->get_image_id();
        $this->en_fdo_image_urls[$product_id] = [
            'product_id' => $product_id,
            'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
            'gallery_image_ids' => $gallery_image_ids
        ];

        add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
    }

    /**
     * Nested Material
     * @param array type $values
     * @param array type $_product
     * @return string type
     */
    function en_nested_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_nestedMaterials', true);
    }

    /**
     * validateProductParamsXpo return ltl shipment exist
     * @param array type $productData
     * @return boolean int
     */
    function validateProductParamsXpo($productData)
    {
        if ((!isset($productData['freightClass']) || $productData['freightClass'] != "ltl_freight")) {
            return 0;
        }
        return 1;
    }

    /**
     * Grouping For Shipment Quotes
     * @param $quotes
     * @param $smallQuotes
     * @param $handlng_fee
     * @return Total Cost
     */
    function xpo_grouped_quotes($quotes, $smallQuotes, $handlng_fee)
    {
        $totalPrice = 0;
        $smTotalPrice = 0;
        $sandbox = "";
        $grandTotal = 0;
        $grandTotalWdoutLiftGate = 0;
        $smpkgCost = 0;
        $liftgate_amnt = 0;
        $label_sfx_arr = "";

        foreach ($quotes as $multiValues) {
            if (isset($multiValues) && !empty($multiValues)) {

                $totalPriceLiftGate = (isset($multiValues['surcharges']['DLG'])) ? $multiValues['surcharges']['DLG'] : 0;

                if ($handlng_fee != '') {
                    $grandTotal += $this->parse_handeling_fee($handlng_fee, $multiValues['cost']);
                    $grandTotalWdoutLiftGate += $this->parse_handeling_fee($handlng_fee, $multiValues['cost'] - $totalPriceLiftGate);
                } else {
                    $grandTotal += $multiValues['cost'];
                    $grandTotalWdoutLiftGate += $multiValues['cost'] - $totalPriceLiftGate;
                }
            } else {
                $this->errors = 'no quotes return';
                continue;
            }

            (isset($multiValues['sandbox']) && !empty($multiValues['sandbox'])) ? $sandbox = $multiValues['sandbox'] : '';
            (isset($multiValues['surcharges']['DLG']) && !empty($multiValues['surcharges']['DLG'])) ? $liftgate_amnt = $liftgate_amnt + $multiValues['surcharges']['DLG'] : '';
            (isset($multiValues['label_sfx_arr'])) ? $label_sfx_arr = $multiValues['label_sfx_arr'] : '';
        }
        if (count($this->errors) < 1) {
            $grandTotal = $grandTotal;
            $freight = array(
                'sandbox' => $sandbox,
                'total' => $grandTotal,
                'label_sfx_arr' => $label_sfx_arr,
                'liftgate_amnt' => $liftgate_amnt,
                'grandTotalWdoutLiftGate' => $grandTotalWdoutLiftGate,
            );
            return $freight;
        }
    }

    /**
     * Calculate Handeling Fee
     * @param $handlng_fee
     * @param $cost
     * @return Handling Fee
     */
    function parse_handeling_fee($handlng_fee, $cost)
    {
        $pos = strpos($handlng_fee, '%');
        if ($pos > 0) {
            $rest = substr($handlng_fee, $pos);
            $exp = explode($rest, $handlng_fee);
            $get = $exp[0];
            $percnt = $get / 100 * $cost;
            $grandTotal = $cost + $percnt;
        } else {
            $grandTotal = $cost + $handlng_fee;
        }
        return $grandTotal;
    }

    /**
     * Get Shipment Origin
     * @param $_product
     * @param $values
     * @param $ltl_res_inst
     * @param $ltl_zipcode
     * @return Origin Address
     * @global $wpdb
     */
    function xpo_get_origin($_product, $values, $ltl_res_inst)
    {
        global $wpdb;

        $locations_list = [];

        (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                // Micro Warehouse
                $this->warehouse_products[] = $post_id;
                return array('error' => 'wwe small dp location not found!');
            }

            // Multi Dropship
            $multi_dropship = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'multi_dropship');

            if (is_array($multi_dropship)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            } else {
                $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            // Micro Warehouse
            $this->multiple_dropship_of_prod($locations_list, $post_id);
            $eniture_debug_name = "Dropships";
        }

        if (empty($locations_list)) {
            // Multi Warehouse
            $multi_warehouse = apply_filters('xpo_quotes_quotes_plans_suscription_and_features', 'multi_warehouse');
            if (is_array($multi_warehouse)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            } else {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            // Micro Warehouse
            $this->warehouse_products[] = $post_id;
            $eniture_debug_name = "Warehouses";
        }

        // preferred origin
        /*if (is_plugin_active('preferred-origin/preferred-origin.php')) {
            $locations_list = apply_filters('en_selection_of_locations', $locations_list, 'xpo');
        }*/
        $locations_list = $ltl_res_inst->xpo_multi_warehouse($locations_list);

        do_action("eniture_debug_mood", "Quotes $eniture_debug_name (XPO)", $locations_list);
        return $locations_list;
    }

    // Micro Warehouse
    public function multiple_dropship_of_prod($locations_list, $post_id)
    {
        $post_id = (string)$post_id;

        foreach ($locations_list as $key => $value) {
            $dropship_data = $this->address_array($value);

            $this->origin["D" . $dropship_data['zip']] = $dropship_data;

            if (!isset($this->dropship_location_array["D" . $dropship_data['zip']]) || !in_array($post_id, $this->dropship_location_array["D" . $dropship_data['zip']])) {
                $this->dropship_location_array["D" . $dropship_data['zip']][] = $post_id;
            }
        }

    }

    // Micro Warehouse
    public function address_array($value)
    {
        $dropship_data = [];

        $dropship_data['locationId'] = (isset($value->id)) ? $value->id : "";
        $dropship_data['zip'] = (isset($value->zip)) ? $value->zip : "";
        $dropship_data['city'] = (isset($value->city)) ? $value->city : "";
        $dropship_data['state'] = (isset($value->state)) ? $value->state : "";
        // Origin terminal address
        $dropship_data['address'] = (isset($value->address)) ? $value->address : "";
        // Terminal phone number
        $dropship_data['phone_instore'] = (isset($value->phone_instore)) ? $value->phone_instore : "";
        $dropship_data['location'] = (isset($value->location)) ? $value->location : "";
        $dropship_data['country'] = (isset($value->country)) ? $value->country : "";
        $dropship_data['enable_store_pickup'] = (isset($value->enable_store_pickup)) ? $value->enable_store_pickup : "";
        $dropship_data['fee_local_delivery'] = (isset($value->fee_local_delivery)) ? $value->fee_local_delivery : "";
        $dropship_data['suppress_local_delivery'] = (isset($value->suppress_local_delivery)) ? $value->suppress_local_delivery : "";
        $dropship_data['miles_store_pickup'] = (isset($value->miles_store_pickup)) ? $value->miles_store_pickup : "";
        $dropship_data['match_postal_store_pickup'] = (isset($value->match_postal_store_pickup)) ? $value->match_postal_store_pickup : "";
        $dropship_data['checkout_desc_store_pickup'] = (isset($value->checkout_desc_store_pickup)) ? $value->checkout_desc_store_pickup : "";
        $dropship_data['enable_local_delivery'] = (isset($value->enable_local_delivery)) ? $value->enable_local_delivery : "";
        $dropship_data['miles_local_delivery'] = (isset($value->miles_local_delivery)) ? $value->miles_local_delivery : "";
        $dropship_data['match_postal_local_delivery'] = (isset($value->match_postal_local_delivery)) ? $value->match_postal_local_delivery : "";
        $dropship_data['checkout_desc_local_delivery'] = (isset($value->checkout_desc_local_delivery)) ? $value->checkout_desc_local_delivery : "";

        $dropship_data['sender_origin'] = $dropship_data['location'] . ": " . $dropship_data['city'] . ", " . $dropship_data['state'] . " " . $dropship_data['zip'];

        return $dropship_data;
    }

    /**
     * Check Product Freight Class
     * @param $values
     * @param $_product
     * @return Freight Class
     */
    function xpo_get_freight_class($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $hazardous_material = get_post_meta($values['variation_id'], '_hazardousmaterials', true);
            $variation_class = get_post_meta($values['variation_id'], '_ltl_freight_variation', true);
            if (empty($variation_class) || $variation_class == 'get_parent') {
                $variation_class = get_post_meta($values['product_id'], '_ltl_freight', true);
                $freightClass_ltl_gross = $variation_class;
            } else {
                if ($variation_class > 0) {
                    $freightClass_ltl_gross = get_post_meta($values['variation_id'], '_ltl_freight_variation', true);
                } else {
                    $freightClass_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
                }

                if(empty($freightClass_ltl_gross)){
                    $freightClass_ltl_gross = get_post_meta($values['product_id'], '_ltl_freight', true);
                }
            }
        } else {
            $hazardous_material = get_post_meta($_product->get_id(), '_hazardousmaterials', true);
            $freightClass_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
        }
        return array('hazmat' => $hazardous_material, 'freight_class' => $freightClass_ltl_gross);
    }

    /**
     * Check Product Enable Against LTL Freight
     * @param $_product
     * @return Shipping Class
     */
    function xpo_enable_shipping_class($_product)
    {
        if ($_product->get_type() == 'variation') {
            $ship_class_id = $_product->get_shipping_class_id();
            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $get_shipping_result = (isset($get_parent_term->slug)) ? $get_parent_term->slug : '';
            } else {
                $get_shipping_result = $_product->get_shipping_class();
            }

            $ltl_enable = ($get_shipping_result && $get_shipping_result == 'ltl_freight') ? true : false;
        } else {
            $get_shipping_result = $_product->get_shipping_class();
            $ltl_enable = ($get_shipping_result == 'ltl_freight') ? true : false;
        }
        return $ltl_enable;
    }

    /**
     * parameters $_product
     * return $dimensions in inches
     */
    function dimensions_conversion($_product)
    {
        $height = is_numeric($_product->get_height()) ? $_product->get_height() : 0;
        $width = is_numeric($_product->get_width()) ? $_product->get_width() : 0;
        $length = is_numeric($_product->get_length()) ? $_product->get_length() : 0;

        $dimension_unit = get_option('woocommerce_dimension_unit');
        $dimensions = [];

        switch ($dimension_unit) {

            case 'ft':
                $dimensions['height'] = round($height * 12, 2);
                $dimensions['width'] = round($width * 12, 2);
                $dimensions['length'] = round($length * 12, 2);
                break;

            case 'cm':
                $dimensions['height'] = round($height * 0.3937007874, 2);
                $dimensions['width'] = round($width * 0.3937007874, 2);
                $dimensions['length'] = round($length * 0.3937007874, 2);
                break;

            case 'mi':
                $dimensions['height'] = round($height * 63360, 2);
                $dimensions['width'] = round($width * 63360, 2);
                $dimensions['length'] = round($length * 63360, 2);
                break;

            case 'km':
                $dimensions['height'] = round($height * 39370.1, 2);
                $dimensions['width'] = round($width * 39370.1, 2);
                $dimensions['length'] = round($length * 39370.1, 2);
                break;
        }

        return $dimensions;
    }

    
    /**
     * Returns flat rate price and quantity
     */
    function en_get_flat_rate_price($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
            if (strlen($flat_rate_price) < 1) {
                $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
            }
        } else {
            $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
        }

        return $flat_rate_price;
    }

    /**
    * Returns product level markup
    */
    function xpo_ltl_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
    {
        $product_level_markup = 0;
        if ($_product->get_type() == 'variation') {
            $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
            if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
        } else {
            $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
        }
        if(empty($product_level_markup)) {
            $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
        }
        if(!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
        && is_numeric($product_level_markup) && is_numeric($quantity))
        {
            $product_level_markup *= $quantity;
        } else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
            $position = strpos($product_level_markup, '%');
            $first_str = substr($product_level_markup, $position);
            $arr = explode($first_str, $product_level_markup);
            $percentage_value = $arr[0];
            $product_price = $_product->get_price();
 
            if (!empty($product_price)) {
                $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
            } else {
                $product_level_markup = 0;
            }
         }
 
        return $product_level_markup;
    }
}
