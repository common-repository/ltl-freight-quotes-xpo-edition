<?php
/**
 * XPO WooComerce Settings Tabs
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooCommerce Setting Tab Class
 */
class WC_Settings_Xpo_Logistics extends WC_Settings_Page {

    /**
     * Woocommerce Setting Tab Constructor
     */
    public function __construct() {
        $this->id = 'xpo_quotes';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * XPO Setting Tab For Woo-commerce
     * @param $settings_tabs
     * @return array
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = __('XPO Logistics', 'woocommerce-settings-xpo_quotes');
        return $settings_tabs;
    }

    /**
     * XPO Setting Sections
     * @return array
     */
    public function get_sections() {
        $sections = array(
            '' => __('Connection Settings', 'woocommerce-settings-xpo_quotes'),
            'section-1' => __('Quote Settings', 'woocommerce-settings-xpo_quotes'),
            'section-2' => __('Warehouses', 'woocommerce-settings-xpo_quotes'),
            'shipping-rules' => __('Shipping Rules', 'woocommerce-settings-xpo_quotes'),
            // fdo va
            'section-4' => __('FreightDesk Online', 'woocommerce-settings-xpo_quotes'),
            'section-5' => __('Validate Addresses', 'woocommerce-settings-xpo_quotes'),
            'section-3' => __('User Guide', 'woocommerce-settings-xpo_quotes')
        );

        // Logs data
        $enable_logs = get_option('en_xpo_enable_logs');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        } 

        $sections = apply_filters('en_woo_addons_sections', $sections, en_woo_plugin_xpo_quotes);
        // Standard Packaging
        $sections = apply_filters('en_woo_pallet_addons_sections', $sections, en_woo_plugin_xpo_quotes);
        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * XPO Warehouse Tab
     */
    public function xpo_warehouse() {
        require_once 'warehouse-dropship/wild/warehouse/xpo_warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/xpo_dropship_template.php';
    }

    public function xpo_shipping_rules() {
        include_once plugin_dir_path(__FILE__) . 'shipping-rules/shipping-rules-template.php';
    }

    /**
     * XPO User Guide Tab
     */
    public function xpo_user_guide() {
        include_once( 'template/guide.php' );
    }

    /**
     * Get All Pages
     * @param $section
     * @return array
     */
    public function get_settings($section = null) {
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = XPO_Connection_Settings::xpo_con_setting();
                break;
            case 'section-1' :
                $xpo_quote_Settings = new XPO_Quote_Settings();
                $settings = $xpo_quote_Settings->xpo_quote_settings_tab();
                break;
            case 'section-2':
                $this->xpo_warehouse();
                $settings = array();
                break;
            case 'shipping-rules':
                $this->xpo_shipping_rules();
                $settings = array();
                break;
            case 'section-3' :
                $this->xpo_user_guide();
                $settings = array();
                break;
            // fdo va
            case 'section-4' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-5' :
                $this->validate_addresses_section();
                $settings = [];
                break;
            case 'en-logs' :
                require_once 'logs/en-logs.php';
                $settings = [];
                break;
            default:
                $xpo_con_settings = new XPO_Connection_Settings();
                $settings = $xpo_con_settings->xpo_con_setting();
                break;
        }

        $settings = apply_filters('en_woo_addons_settings', $settings, $section, en_woo_plugin_xpo_quotes);
        // Standard Packaging
        $settings = apply_filters('en_woo_pallet_addons_settings', $settings, $section, en_woo_plugin_xpo_quotes);
        $settings = $this->avaibility_addon($settings);
        return apply_filters('woocommerce-settings-xpo_quotes', $settings, $section);
    }

    /**
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings) {
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            unset($settings['avaibility_lift_gate']);
            unset($settings['avaibility_auto_residential']);
        }

        return $settings;
    }

    /**
     * Settings Output
     * @global $current_section
     */
    public function output() {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * XPO Save Settings
     * @global $current_section
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings($current_section);
        // Cuttoff Time
        if (isset($_POST['xpo_freight_order_cut_off_time']) && $_POST['xpo_freight_order_cut_off_time'] != '') {
            $time_24_format = $this->xpo_get_time_in_24_hours($_POST['xpo_freight_order_cut_off_time']);
            $_POST['xpo_freight_order_cut_off_time'] = $time_24_format;
        }
        WC_Admin_Settings::save_fields($settings);
    }
    /**
     * Cuttoff Time
     * @param $timeStr
     * @return false|string
     */
    public function xpo_get_time_in_24_hours($timeStr)
    {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return date("H:i", strtotime($cutOffTime));
    }
    // fdo va
    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/freightdesk-online-section.php';
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/validate-addresses-section.php';
    }

}

return new WC_Settings_Xpo_Logistics();
