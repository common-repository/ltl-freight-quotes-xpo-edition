<?php

/**
 * XPO WooComerce Update Changes | Customer Billing Details 
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XPO WooComerce Update Changes Class | Customer Billing Details
 */
class Woo_Update_Changes_Xpo {

    /**
     * WooCommerce Version Number
     * @var int
     */
    public $WooVersion;

    /**
     * XPO WooComerce Update Changes Constructor | Customer Billing Details
     */
    function __construct() {
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $this->WooVersion = $plugin_folder[$plugin_file]['Version'];
    }

    /**
     * Customer Billing Postcode
     * @return int/string
     */
    function xpo_postcode() {
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_postcode();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_postcode();
                break;

            default:

                break;
        }
        return $postcode;
    }

    /**
     * Customer Billing State
     * @return int/string
     */
    function xpo_state() {
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_state();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_state();
                break;

            default:

                break;
        }
        return $postcode;
    }

    /**
     * xpo WooCommerce Version For City
     * @return int/boolian
     */
    function xpo_getCity() {
        $sCity = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $sCity = WC()->customer->get_city();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCity = WC()->customer->get_billing_city();
                break;

            default:
                break;
        }
        return $sCity;
    }

    /**
     * xpo WooCommerce Version For Country
     * @return int/boolian
     */
    function xpo_getCountry() {
        $sCountry = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $sCountry = WC()->customer->get_country();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCountry = WC()->customer->get_billing_country();
                break;

            default:
                break;
        }
        return $sCountry;
    }

}
