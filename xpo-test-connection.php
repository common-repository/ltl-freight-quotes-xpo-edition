<?php

/**
 * XPO WooComerce XPO Test connection AJAX Request
 * @package     Woocommerce XPO Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_xpo_action', 'xpo_test_submit');
add_action('wp_ajax_xpo_action', 'xpo_test_submit');

/**
 * XPO Test connection AJAX Request
 */
function xpo_test_submit()
{
    if (isset($_POST)) {
        foreach ($_POST as $key => $post) {
            $data[$key] = sanitize_text_field($post);
        }
        
        $postData = array(
            'licence_key' => (!empty($data['xpo_plugin_license'])) ? $data['xpo_plugin_license'] : '',
            'sever_name' => xpo_freight_get_domain(),
            'carrierName' => 'xpoLogistics',
            'plateform' => 'WordPress',
            'carrier_mode' => 'test',
            'UserName' => (!empty($data['xpo_username'])) ? $data['xpo_username'] : '',
            'Password' => (!empty($data['xpo_password'])) ? $data['xpo_password'] : '',
            'CUSTNMBR' => (!empty($data['xpo_customer_number'])) ? $data['xpo_customer_number'] : '',
            'physicalZipCode' => (!empty($data['billing_postal_code'])) ? $data['billing_postal_code'] : '',
            'thirdPartyAccountNumber' => (!empty($data['third_party_acc_number'])) ? $data['third_party_acc_number'] : '',
            'basicAccessToken' => (!empty($data['basic_access_token'])) ? $data['basic_access_token'] : '',
            'requestType' => (!empty($data['account_check'])) ? $data['account_check'] : '',
            'xpoApiVersion' => (!empty($data['basic_access_token'])) ? '1.0' : '',
        );
    }

    if (is_array($postData) && count($postData) > 0) {

        $url = XPO_FREIGHT_DOMAIN_HITTING_URL . '/index.php';
        $field_string = http_build_query($postData);

        $response = wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $field_string,
            )
        );

        $output = wp_remote_retrieve_body($response);
    }

    $result = json_decode($output);

    if (isset($result->error)) {
        $test_error = $result->error;
        $test_error = (is_object($test_error)) ? reset($test_error) : $test_error;
        $response = array('Error' => $test_error);
    } elseif (isset($result->q->Error) && $result->q->Error != "") {
        $response = array('Error' => $result->q->Error);
    } elseif (isset($result->q) && $result->q != "") {
        $response = array('Success' => 'The test resulted in a successful connection.');
    } elseif (isset($result->Error)) {
        $response = array('Error' => $result->Error);
    }
    echo json_encode($response);
    exit();
}
