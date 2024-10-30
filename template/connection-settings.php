<?php
/**
 * XPO WooComerce XPO Connection Settings Tab Class
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooComerce Connection Settings Tab Class
 */
class XPO_Connection_Settings
{
    /**
     * Connection Settings Fields
     * @return array
     */
    public function xpo_con_setting()
    {
        echo '<div class="connection_section_class_xpo">';
        $settings = array(
            'section_title_xpo' => array(
                'name' => __('', 'woocommerce-settings-xpo_quotes'),
                'type' => 'title',
                'desc' => '<br> ',
                'id' => 'wc_settings_xpo_title_section_connection',
            ),

            'customer_number_xpo' => array(
                'name' => __('Pickup/Delivery Account Number ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_customer_number',
            ),

            'username_xpo' => array(
                'name' => __('Username ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_username'
            ),

            'password_xpo' => array(
                'name' => __('Password ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_password'
            ),

            'address_zipcode_xpo' => array(
                'name' => __('Pickup/Delivery Postal Code', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_zipcode'
            ),

            'third_party_acc_xpo' => array(
                'name' => __('Bill To Account Number ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_third_party_acc'
            ),
            'basic_access_token' => array(
                'name' => __('Access Token', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_basic_access_token'
            ),

            'plugin_licence_key_xpo' => array(
                'name' => __('Eniture API Key ', 'woocommerce-settings-xpo_quotes'),
                'type' => 'text',
                'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/woocommerce-xpo-ltl-freight/" target="_blank" >eniture.com </a>', 'woocommerce-settings-xpo_quotes'),
                'id' => 'wc_settings_xpo_plugin_licence_key'
            ),

            'xpo_account_select' => array(
                'name' => __('', 'xpo_wc_settings'),
                'id' => 'xpo_account_select_setting',
                'class' => 'xpo_account_select_setting',
                'type' => 'radio',
                'options' => array(
                    'shipper' => __('Test Account Number', 'woocommerce'),
                    'thirdParty' => __('Test Bill To Account Number', 'woocommerce')
                )
            ),

            'section_end_xpo' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_xpo_plugin_licence_key'
            ),
        );
        return $settings;
    }
}