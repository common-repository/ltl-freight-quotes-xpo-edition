<?php

/**
 * WWE Small Get Distance
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Distance Request Class
 */
class Get_xpo_freight_distance
{

    function __construct()
    {
        add_filter("en_wd_get_address", array($this, "sm_address"), 10, 2);
    }

    /**
     * Get Address Upon Access Level
     * @param $map_address
     * @param $accessLevel
     */
    function xpo_freight_address($map_address, $accessLevel, $destinationZip = array())
    {

        $domain = xpo_freight_get_domain();
        $postData = array(
            'acessLevel' => $accessLevel,
            'address' => $map_address,
            'originAddresses' => (isset($map_address)) ? $map_address : "",
            'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
            'eniureLicenceKey' => get_option('wc_settings_xpo_plugin_licence_key'),
            'ServerName' => $domain,
        );

        $xpo_Curl_Request = new XPO_Curl_Request();
        $output = $xpo_Curl_Request->xpo_get_curl_response(XPO_FREIGHT_DOMAIN_HITTING_URL . '/addon/google-location.php', $postData);
        return $output;
    }

}
