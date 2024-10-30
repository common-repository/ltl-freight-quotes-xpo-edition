<?php

/**
 * Includes Shipping Rules Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnXpoShippingRulesAjaxReq")) {

    class EnXpoShippingRulesAjaxReq
    {
        private $is_dest_country_matched;
        private $is_country_filter_applied;
        private $is_restrict_service_rule_applied;

        /**
         * Get shipping rules ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_xpo_save_shipping_rule', array($this, 'en_xpo_save_shipping_rule_ajax'));
            add_action('wp_ajax_en_xpo_save_shipping_rule', array($this, 'en_xpo_save_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_xpo_edit_shipping_rule', array($this, 'en_xpo_edit_shipping_rule_ajax'));
            add_action('wp_ajax_en_xpo_edit_shipping_rule', array($this, 'en_xpo_edit_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_xpo_delete_shipping_rule', array($this, 'en_xpo_delete_shipping_rule_ajax'));
            add_action('wp_ajax_en_xpo_delete_shipping_rule', array($this, 'en_xpo_delete_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_xpo_update_shipping_rule_status', array($this, 'en_xpo_update_shipping_rule_status_ajax'));
            add_action('wp_ajax_en_xpo_update_shipping_rule_status', array($this, 'en_xpo_update_shipping_rule_status_ajax'));

            $this->is_dest_country_matched = false;
            $this->is_country_filter_applied = false;
            $this->is_restrict_service_rule_applied = false;
        }

        // MARK: Save Shipping Rule
        /**
         * Save Shipping Rule Function
         * @global $wpdb
         */
        function en_xpo_save_shipping_rule_ajax()
        {
            global $wpdb;

            $insert_qry = $update_qry = '';
            $error = false;
            $data = $_POST;
            $get_shipping_rule_id = (isset($data['rule_id']) && intval($data['rule_id'])) ? $data['rule_id'] : "";
            $last_id = $get_shipping_rule_id;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_xpo_shipping_rules WHERE name = '" . $data['name'] . "'"; 
            $get_shipping_rule = $wpdb->get_results($qry);
            unset($data['action']);
            unset($data['rule_id']);
            
            if (!empty($get_shipping_rule_id)) {
                $data['settings'] = json_encode($data['settings']);
                $update_qry = $wpdb->update(
                    $wpdb->prefix . 'eniture_xpo_shipping_rules', $data, array('id' => $get_shipping_rule_id)
                );

                $update_qry = (!empty($get_shipping_rule) && reset($get_shipping_rule)->id == $get_shipping_rule_id) ? 1 : $update_qry;
            } else {
                if (!empty($get_shipping_rule)) {
                    $error = true;
                } else {
                    $data['settings'] = json_encode($data['settings']);
                    $insert_qry = $wpdb->insert($wpdb->prefix . 'eniture_xpo_shipping_rules', $data);
                    $last_id = $wpdb->insert_id;
                }
            }

            $shipping_rules_list = array('name' => $data["name"], 'type' => $data["type"], 'is_active' => $data["is_active"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $last_id, 'error' => $error);

            echo json_encode($shipping_rules_list);
            exit;
        }

        // MARK: Edit Shipping Rule
        /**
         * Edit Shipping Rule Function
         * @global $wpdb
         */
        function en_xpo_edit_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? $_POST['edit_id'] : "";
            $shipping_rules_list = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "eniture_xpo_shipping_rules WHERE id=$get_shipping_rule_id"
            );
            $product_tags_markup = $this->en_xpo_get_product_tags_markup($shipping_rules_list);
            $states_markup = $this->en_xpo_get_country_states_markup($shipping_rules_list);
            $data = ['rule_data' => reset($shipping_rules_list), 'product_tags_markup' => $product_tags_markup, 'country_states_markup' => $states_markup];

            echo json_encode($data);
            exit;
        }

        // MARK: Delete Shipping Rule
        /**
         * Delete Shipping Rule Function
         * @global $wpdb
         */
        function en_xpo_delete_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? $_POST['delete_id'] : "";
            $qry = $wpdb->delete($wpdb->prefix . 'eniture_xpo_shipping_rules', array('id' => $get_shipping_rule_id));

            echo json_encode(['query' => $qry]);
            exit;
        }

        // MARK: Update Shipping Rule Status
        /**
         * Update Shipping Rule Status Function
         * @global $wpdb
         */
        function en_xpo_update_shipping_rule_status_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['rule_id']) && intval($_POST['rule_id'])) ? $_POST['rule_id'] : "";
            $is_active = isset($_POST['is_active']) ? $_POST['is_active'] : "";
            $data = ['is_active' => $is_active];
            
            $update_qry = $wpdb->update(
                $wpdb->prefix . 'eniture_xpo_shipping_rules', $data, array('id' => $get_shipping_rule_id)
            );

            echo json_encode(['id' => $get_shipping_rule_id, 'is_active' => $is_active, 'update_qry' => $update_qry]);
            exit;
        }

        // MARK: Get Product Tags
        /**
         * Get Product Tags Function
         * @global $wpdb
         */
        function en_xpo_get_product_tags_markup($shipping_rules_list)
        {
            $tags_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $tags_data = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_tags_detials = $this->en_xpo_get_selected_tags_details($tags_data['filter_by_product_tag_value']);

            if (!empty($selected_tags_detials) && is_array($selected_tags_detials)) {
                foreach ($selected_tags_detials as $key => $tag) {
                    $tags_options .= "<option selected='selected' value='" . esc_attr($tag['term_taxonomy_id']) . "'>" . esc_html($tag['name']) . "</option>";
                }
            }

            if (empty($tags_data['filter_by_product_tag_value']) || !is_array($tags_data['filter_by_product_tag_value'])) {
                $tags_data['filter_by_product_tag_value'] = [];
            }

            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            if (!empty($en_woo_product_tags) && is_array($tags_data['filter_by_product_tag_value'])) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (!in_array($tag->term_id, $tags_data['filter_by_product_tag_value'])) {
                        $tags_options .= "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                    }
                }
            }

            return $tags_options;
        }

        // MARK: Get Country States
        /**
         * Get Country States Function
         * @global $wpdb
         */
        function en_xpo_get_country_states_markup($shipping_rules_list)
        {
            $states_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $settings = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_states_detials = $this->en_xpo_get_selected_states_details($settings);

            if (!empty($selected_states_detials) && is_array($selected_states_detials)) {
                foreach ($selected_states_detials as $s_code => $s_label) {
                    $states_options .= "<option selected='selected' value='" . esc_attr($s_code) . "'>" . esc_html($s_label) . "</option>";
                }
            }

            if (empty($settings['filter_by_state_value']) || !is_array($settings['filter_by_state_value'])) {
                $settings['filter_by_state_value'] = [];
            }

            $countries_obj = new WC_Countries();
            $selected_country = isset($settings['filter_by_country_value']) ? $settings['filter_by_country_value'] : '';
            $en_woo_states = $countries_obj->get_states($selected_country);

            if (!empty($en_woo_states) && is_array($settings['filter_by_state_value'])) {
                foreach ($en_woo_states as $s_code => $s_label) {
                    if (!in_array($s_code, $settings['filter_by_state_value'])) {
                        $states_options .= "<option value='" . esc_attr($s_code) . "'>" . esc_html($s_label) . "</option>";
                    }
                }
            }

            return $states_options;
        }

        // MARK: Get Selected Tags Details
        /**
         * Get Selected Tags Details Function
         * @global $wpdb
         */
        function en_xpo_get_selected_tags_details($products_tags_arr)
        {
            if (empty($products_tags_arr) || !is_array($products_tags_arr)) {
                return [];
            }

            $tags_detail = [];
            $count = 0;
            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );

            if (isset($en_woo_product_tags) && !empty($en_woo_product_tags)) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (in_array($tag->term_taxonomy_id, $products_tags_arr)) {
                        $tags_detail[$count]['term_id'] = $tag->term_id;
                        $tags_detail[$count]['name'] = $tag->name;
                        $tags_detail[$count]['slug'] = $tag->slug;
                        $tags_detail[$count]['term_taxonomy_id'] = $tag->term_taxonomy_id;
                        $tags_detail[$count]['description'] = $tag->description;
                        $count++;
                    }
                }
            }

            return $tags_detail;
        }

        // MARK: Get Selected States Details
        /**
         * Get Selected States Details
         * @global $wpdb
         */
        function en_xpo_get_selected_states_details($settings)
        {
            $states_arr = isset($settings['filter_by_state_value']) ? $settings['filter_by_state_value'] : [];
            if (empty($states_arr) || !is_array($states_arr)) {
                return [];
            }

            $countries_obj = new WC_Countries();
            $selected_country = isset($settings['filter_by_country_value']) ? $settings['filter_by_country_value'] : '';
            $en_woo_states = $countries_obj->get_states($selected_country);
            $states_detail = [];

            if (isset($en_woo_states) && !empty($en_woo_states)) {
                foreach ($en_woo_states as $key => $state) {
                    if (in_array($key, $states_arr)) $states_detail[$key] = $state;
                }
            }

            return $states_detail;
        }

	    // MARK: Apply Shipping Rules
        /**
         * Apply shipping rules based on Odfl package and settings.
         *
         * @param array $xpo_package request to get quotes
         * @param boolean $apply_on_rates whether to apply rules on rates
         * @param object $rates quotes response
         * @return boolean | object returns if rule is applied or modified rates
         */
        function apply_shipping_rules($xpo_package, $apply_on_rates = false, $rates = [], $loc_id = '')
        {
            if (empty($xpo_package)) return $apply_on_rates ? $rates : false;

            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_xpo_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);

            if (empty($rules)) return $apply_on_rates ? $rates : false;
        
            $is_rule_applied = false;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;

                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';

                if ($rule_type == 'Hide Methods' && !$apply_on_rates) {
                    $is_rule_applied = $this->apply_hide_methods_rule($settings, $xpo_package);
                    if ($is_rule_applied) break;
                } else if ($rule_type == 'Override Rates' && $apply_on_rates) {
                    $rates = $this->apply_override_rates_rule($xpo_package, $settings, $rates, $loc_id);
                } else if ($rule_type == 'Restrict To State' && $apply_on_rates) {
                    $rates = $this->apply_restrict_to_state_rule($xpo_package, $settings, $rates);
                }
            }

            return $apply_on_rates ? $rates : $is_rule_applied;
        }

        // MARK: Apply Hide Methods Rule
        /**
         * Apply the rule to the given settings and package.
         *
         * @param array $settings The settings for the rule.
         * @param array $xpo_package The package to apply the rule to.
         * @return bool Whether the rule was applied or not.
        */
        function apply_hide_methods_rule($settings, $xpo_package)
        {
            $is_rule_applied = false;

            if ($settings['apply_to'] == 'cart') {
                $formatted_values = $this->get_formatted_values($xpo_package);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
            } else {
                foreach ($xpo_package as $key => $pkg) {
                    $is_rule_applied = false;
                    $shipments = [];
                    $shipments[$key] = $pkg;

                    $formatted_values = $this->get_formatted_values($shipments);
                    $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

                    if ($is_rule_applied) break;
                }
            }

            return $is_rule_applied;
        }

        // MARK: Apply Override Rates Rule
        /**
         * A function to apply override rates rule.
         *
         * @param array $xpo_package request array to get the quotes
         * @param array $settings rule settings
         * @param object $rates quotes object
         * @return $rates The updated rates.
         */
        function apply_override_rates_rule($xpo_package, $settings, $rates, $loc_id)
        {
            if (empty($rates)) return $rates;
            $updated_rates = $rates;

            foreach ($xpo_package as $key => $pkg) {
                if ($key != $loc_id) continue;

                $is_rule_applied = false;
                $shipments = [];
                $shipments[$key] = $pkg;

                $formatted_values = $this->get_formatted_values($shipments);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

                if ($is_rule_applied) {
                    $updated_rates = $this->get_updated_rates($updated_rates, $settings);
                };
            }

            return $updated_rates;
        }

        // MARK: Apply restrict to state rule
        /**
         * Apply restrict to state rule.
         *
         * @param array $xpo_package request array to get the quotes
         * @param array $settings rule settings
         * @param object $rates quotes object
         * @return object $rates The updated rates.
         */
        function apply_restrict_to_state_rule($xpo_package, $settings, $rates) 
        {
            $formatted_values = $this->get_formatted_values($xpo_package);
            $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values, true);
            $this->is_restrict_service_rule_applied = $is_rule_applied;

            return ($is_rule_applied || ($this->is_country_filter_applied && !$this->is_dest_country_matched)) ? $this->get_updated_rates($rates, $settings, true) : $rates;
        }

        // MARK: Get Updated Rates
        /**
         * A function that updates rates based on settings and rule type.
         *
         * @param object $rates The rates to be updated.
         * @param array $settings The settings used for updating rates.
         * @param bool $restict_service Flag to apply restrict service rule.
         * @return array The updated rates.
         */
        function get_updated_rates($rates, $settings, $restict_service = false)
        {
            if (empty($rates)) return $rates;

            if ($restict_service) {
                if ($this->is_country_filter_applied && !$this->is_dest_country_matched && isset($rates->q)) {
                    unset($rates->q);
                }
            } else {
                $service_type = $settings['service'];
                $service_rate = $settings['service_rate'];
                $residential_status = isset($rates->residentialStatus) && $rates->residentialStatus == 'r';
                $liftgate_status = isset($rates->liftGateStatus) && $rates->liftGateStatus == 'l';
                
                if ($service_type == 'transportation_service') {
                    $rates = $this->get_transportation_service_rates($rates, $service_rate);
                } elseif ($service_type == 'residential_delivery_service' && $residential_status) {
                    $rates = $this->get_resi_or_lfg_service_rates($rates, $service_rate, 'residentialFee');
                } elseif ($service_type == 'liftgate_delivery_service' && $liftgate_status) {
                    $rates = $this->get_resi_or_lfg_service_rates($rates, $service_rate, 'liftgateFee');
                }
            }

            return $rates;
        }

        // MARK: Get Fromatted Values
        /**
         * Calculate the total weight, price, quantity, and tags for a list of shipments.
         *
         * @param array $shipments An array of shipments to process.
         * @return array The formatted values including weight, price, quantity, tags, and country.
         */
        function get_formatted_values($shipments)
        {
            $formatted_values = ['weight' => 0, 'price' => 0, 'quantity' => 0, 'tags' => []];

            foreach ($shipments as $pkg) {
                $formatted_values['weight'] += floatval($pkg['shipment_weight']);
                $formatted_values['price'] += floatval($pkg['product_prices']);
                $formatted_values['quantity'] += floatval($pkg['product_quantities']);
                $formatted_values['tags'] = array_merge($formatted_values['tags'], $pkg['product_tags']);
            }

            return $formatted_values;
        }

        // MARK: Apply Rule Filters
        /**
         * Apply rule filters to determine if the rule is applied.
         *
         * @param array $settings The settings for the rule filters
         * @param array $formatted_values The formatted values for comparison
         * @param bool $restict_service Flag to also include restrict service filters
         * @return bool Whether the rule filters are applied
         */
        function apply_rule_filters($settings, $formatted_values, $restict_service = false)
        {
            $this->is_country_filter_applied = false;

            // If there is no filter check, then all rules will meet so rule will be treated as applied
            if (!$this->is_any_filter_checked($settings, $restict_service)) return true;

            $is_filter_applied = false;
            $filters = ['weight', 'price', 'quantity'];
            // Add restrict service filters in case of restrict to state rule
            $filters = $restict_service ? array_merge(['country'], $filters) : $filters;
            $destination_address = (new xpo_get_shipping_quotes())->destinationAddressXpo();

            foreach ($filters as $filter) {
                if (filter_var($settings['filter_by_' . $filter], FILTER_VALIDATE_BOOLEAN)) {
                    if ($filter == 'country') {
                        $destination_country = isset($destination_address['country']) ? $destination_address['country'] : '';
                        $this->is_dest_country_matched = $this->is_country_matched($settings['filter_by_country_value'],$destination_country);
                        $is_filter_applied = $this->is_dest_country_matched;
                        $this->is_country_filter_applied = true;

                        if ($is_filter_applied && filter_var($settings['filter_by_state'], FILTER_VALIDATE_BOOLEAN)) {
                            $destination_state = isset($destination_address['state']) ? $destination_address['state'] : '';
                            $states = isset($settings['filter_by_state_value']) ? $settings['filter_by_state_value'] : [];
                            $is_filter_applied = $this->is_dest_country_matched = in_array($destination_state, $states);
                        }
                    } else {
                        $is_filter_applied = $formatted_values[$filter] >= $settings['filter_by_' . $filter . '_from'];
                        if ($is_filter_applied && !empty($settings['filter_by_' . $filter . '_to'])) {
                            $is_filter_applied = $formatted_values[$filter] < $settings['filter_by_' . $filter . '_to'];
                        }
                    }
                }

                if ($is_filter_applied) break;
            }

            if (!$is_filter_applied && filter_var($settings['filter_by_product_tag'], FILTER_VALIDATE_BOOLEAN)) {
                $product_tags = $settings['filter_by_product_tag_value'];
                $tags_check = array_filter($product_tags, function ($tag) use ($formatted_values) {
                    return in_array($tag, $formatted_values['tags']);
                });
                $is_filter_applied = count($tags_check) > 0;
            }

            return $is_filter_applied;
        }

        // MARK: Any filter is enabled
        /**
         * A function that checks if any filter is checked based on the provided settings.
         *
         * @param array $settings The settings containing filter values.
         * @param bool $restrict_service A flag indicating if service restriction rule is applied.
         * @return bool Returns true if any filter is checked, false otherwise.
         */
        function is_any_filter_checked($settings, $restict_service)
        {
            $filters_checks = ['weight', 'price', 'quantity', 'product_tag'];
            // Add restrict service filters in case of restrict service rule
            if ($restict_service) $filters_checks = array_merge($filters_checks, ['country', 'state']);
            
            // Check if any of the filter is checked
            $any_filter_checked = false;
            foreach ($filters_checks as $check) {
                if (isset($settings['filter_by_' . $check]) && filter_var($settings['filter_by_' . $check], FILTER_VALIDATE_BOOLEAN)) {
                    $any_filter_checked = true;
                    break;
                }
            }

            return $any_filter_checked;
        }

        // MARK: Match country
        /**
         * Check if the selected country matches the destination country.
         *
         * @param string $selected_country The selected country code.
         * @param string $destination_country The destination country code.
         * @return bool Returns true if the countries match, false otherwise.
         */
        function is_country_matched($selected_country, $destination_country) 
        {
            $is_matched = false;
            $selected_country = strtolower($selected_country);
            $destination_country = strtolower($destination_country);

            if ($selected_country == $destination_country || ($destination_country == 'usa' && $selected_country == 'us') || ($destination_country == 'can' && $selected_country == 'ca')) {
                $is_matched = true;
            }

            return $is_matched;
        }
        
        // MARK: Get transportations service rates
        /**
         * A function that updates service rates based on certain conditions.
         *
         * @param object $rates The object containing normal and direct service rates.
         * @param float $service_rate The service rate to be applied.
         * @return object The updated rates object after applying the service rate.
         */
        function get_transportation_service_rates($rates, $service_rate)
        {
            $quote_error = isset($rates->q->Error) ? $rates->q->Error : '';
            $quote_results = (isset($rates->q)) ? $rates->q : [];

            if (!empty($quote_error) || empty($quote_results)) return $rates;

            // New response
            $surcharges = isset($rates->q->surcharges) ? $rates->q->surcharges : [];
            isset($rates->q->totalNetCharge) && $rates->q->totalNetCharge = (string)(floatval($service_rate) + $this->get_resi_and_lfg_fees($surcharges));

            // Old response
            if (empty($surcharges) || !isset($rates->q->totalNetCharge)) {
                $surcharges = (!empty($rates->q->AccessorialCharges->OtherAccessorialChargesFormated)) ? $rates->q->AccessorialCharges->OtherAccessorialChargesFormated : [];
                $lfg_fee = isset($surcharges->DLG) ? $surcharges->DLG : 0;
                $resi_fee = isset($surcharges->RSD) ? $surcharges->RSD : 0;
                $surcharges = !empty($surcharges) ? (object)['liftgateFee' => $lfg_fee, 'residentialFee' => $resi_fee] : [];
                isset($rates->q->NetCharge, $rates->q->NetCharge->{0}) && $rates->q->NetCharge->{0} = (string)(floatval($service_rate) + $this->get_resi_and_lfg_fees($surcharges));
            }

            return $rates;
        }

        // MARK: Get net charges
        /**
         * Get the total net charges from the rates.
         * @param object $rates The object containing the service rates
         * @return int|string Total net charges
         */
        function get_total_charges($rates)
        {
            $charges = isset($rates->q->NetCharge) && !empty($rates->q->NetCharge) ? $rates->q->NetCharge : (isset($rates->q, $rates->q->totalNetCharge) ? $rates->q->totalNetCharge : 0);
            $price = 0;

            if (!is_string($charges)) {
                $charges = json_decode(json_encode($charges), true);
                if (count($charges) > 1) {
                    if (isset($charges['currency'])) {
                        $charges = [$charges];
                    }

                    foreach ($charges as $charge) {
                        if (!$price > 0 && isset($charge['currency'], $charge['0']) && $charge['currency'] == 'USD') {
                            $price = $charge['0'];
                        }
                    }
                } else {
                    $price = isset($rates->q, $rates->q->NetCharge, $rates->q->NetCharge->{0}) ? $rates->q->NetCharge->{0} : 0;
                }
            } else {
                $price = $charges;
            }

            if (isset($price['currency'], $price['0']) && $price['currency'] == 'USD') {
                $price = $price['0'];
            }

            return $price;
        }

        // MARK: Get Additional Services Rates
        /**
         * Updates the additional services rates in the given $rates object based on the $type and $service_rate.
         *
         * @param object $rates The object containing the service rates
         * @param string $service_rate The new service rate to be applied
         * @param string $type The type of service rate to be updated
         * @return object The updated $rates object
         */
        function get_resi_or_lfg_service_rates($rates, $service_rate, $type)
        {
            $surcharges_types = ['residentialFee', 'liftgateFee'];
            
            $quote_error = isset($rates->q->Error) ? $rates->q->Error : '';
            $quote_results = (isset($rates->q)) ? $rates->q : [];
            if (!empty($quote_error) || empty($quote_results)) return $rates;

            $surcharges = isset($rates->q->surcharges) ? $rates->q->surcharges : [];
            $surcharges = empty($surcharges) && !empty($rates->q->AccessorialCharges->OtherAccessorialChargesFormated) ? $rates->q->AccessorialCharges->OtherAccessorialChargesFormated : $surcharges;
            
            if (empty($surcharges)) return $rates;

            if (isset($surcharges->DLG)) $surcharges->liftgateFee = $surcharges->DLG;
            if (isset($surcharges->RSD)) $surcharges->residentialFee = $surcharges->RSD;

            $surcharges_fee = 0;
            $is_surcharge_exist = false;
            foreach ($surcharges_types as $s_type) {
                if ($s_type != $type || !isset($surcharges->$s_type) || empty($surcharges->$s_type)) continue;

                $surcharges_fee = $surcharges->$s_type;
                isset($rates->q->surcharges, $rates->q->surcharges->$s_type) && $rates->q->surcharges->$s_type = $service_rate;
                if (isset($rates->q->AccessorialCharges, $rates->q->AccessorialCharges->OtherAccessorialChargesFormated)) {
                    $type == 'liftgateFee' && $rates->q->AccessorialCharges->OtherAccessorialChargesFormated->DLG = $service_rate;
                    $type == 'residentialFee' && $rates->q->AccessorialCharges->OtherAccessorialChargesFormated->RSD = $service_rate;
                }
                
                $is_surcharge_exist = true;
                break;
            }

            if (!$is_surcharge_exist) return $rates;

            $rate_charge = isset($rates->q->totalNetCharge) ? $rates->q->totalNetCharge : (isset($rates->q->NetCharge, $rates->q->NetCharge->{0}) ? $rates->q->NetCharge->{0} : 0);
            $rate_charge = floatval($rate_charge) - floatval($surcharges_fee);
            $rate_charge += floatval($service_rate);
            isset($rates->q->totalNetCharge) && $rates->q->totalNetCharge = (string)$rate_charge;
            isset($rates->q->NetCharge, $rates->q->NetCharge->{0}) && $rates->q->NetCharge->{0} = (string)$rate_charge;

            return $rates;
        }

        // MARK: Get Additional Services Fee
        /**
         * Get the total fees for residential and liftgate delivery surcharges.
         *
         * @param object $surcharges An array of surcharges objects.
         * @return float The total surcharges fee.
         */
        function get_resi_and_lfg_fees($surcharges)
        {
            if (empty($surcharges)) return 0;
            
            $surcharges_fee = 0;
            $surcharges_types = ['residentialFee', 'liftgateFee'];
            foreach ($surcharges_types as $s_charge) {
                if (isset($surcharges->$s_charge) && !empty($surcharges->$s_charge) && $surcharges->$s_charge > 0) {
                    $surcharges_fee += floatval($surcharges->$s_charge);
                }
            }

            return $surcharges_fee;
        }

        // MARK: Get Liftagte Excluded Limit
        /**
         * Get the liftgate exclude limit.
         * @return int Returns the liftgate exclude limit or 0 if no limit found.
         */
        function get_liftgate_exclude_limit()
        {
            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_xpo_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);

            if (empty($rules)) return 0;

            $liftgate_exclude_limit = 0;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;
                
                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';
                if ($rule_type == 'Liftgate Weight Restrictions' && !empty($settings['liftgate_weight_restrictions'])) {
                    $liftgate_exclude_limit = $settings['liftgate_weight_restrictions'];
                    break;
                }
            }

            return $liftgate_exclude_limit;
        }
    }
}

new EnXpoShippingRulesAjaxReq();
