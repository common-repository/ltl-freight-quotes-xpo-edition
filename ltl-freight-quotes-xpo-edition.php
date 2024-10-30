<?php
/**
 * Plugin Name:    LTL Freight Quotes - XPO Edition
 * Plugin URI:     https://eniture.com/products/
 * Description:    Dynamically retrieves your negotiated shipping rates from XPO and displays the results in the WooCommerce shopping cart.
 * Version:        4.3.5
 * Author:         Eniture Technology
 * Author URI:     https://eniture.com/
 * Text Domain:    eniture-technology
 * License:        GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 6.4
 * WC tested up to: 9.3.1
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('XPO_FREIGHT_DOMAIN_HITTING_URL', 'https://ws028.eniture.com');
define('XPO_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Define reference
function en_xpo_freight_plugin($plugins)
{
    $plugins['lfq'] = (isset($plugins['lfq'])) ? array_merge($plugins['lfq'], ['xpo' => 'XPO_Logistics_Shipping']) : ['xpo' => 'XPO_Logistics_Shipping'];
    return $plugins;
}

add_filter('en_plugins', 'en_xpo_freight_plugin');
if (!function_exists('en_woo_plans_notification_PD')) {

    function en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($e = 1; $e <= 25; $e++) {
            $settings = get_option($eniture_plugins_id . $e);
            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }

        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'en_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('en_woo_plans_notification_message')) {

    function en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'en_woo_plans_notification_message', 10, 2);
}

//Product detail set plans notification message for nested checkbox
if (!function_exists('en_woo_plans_nested_notification_message')) {

    function en_woo_plans_nested_notification_message($enable_plugins, $disable_plugins, $feature)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0 && $feature == 'nested_material') ? " $disable_plugins: Upgrade to <b>Advance Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_nested_notification_message_action', 'en_woo_plans_nested_notification_message', 10, 3);
}

/**
 * Load scripts for XPO Freight json tree view
 */
if (!function_exists('en_xpo_jtv_script')) {
    function en_xpo_jtv_script()
    {
        wp_register_style('en_xpo_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('en_xpo_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');

        wp_enqueue_style('en_xpo_json_tree_view_style');
        wp_enqueue_script('en_xpo_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);

        // Shipping rules script and styles
        wp_enqueue_script('en_xpo_sr_script', plugin_dir_url(__FILE__) . '/shipping-rules/assets/js/shipping_rules.js', array(), '1.0.0');
        wp_localize_script('en_xpo_sr_script', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));
        wp_register_style('en_xpo_shipping_rules_section', plugin_dir_url(__FILE__) . '/shipping-rules/assets/css/shipping_rules.css', false, '1.0.0');
        wp_enqueue_style('en_xpo_shipping_rules_section');
    }

    add_action('admin_init', 'en_xpo_jtv_script');
}

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'xpo_wc_avaibility_error');
}

/**
 * Check woocommerce installlation
 */
function xpo_wc_avaibility_error()
{
    $class = "error";
    $message = "LTL Freight Quotes - XPO Edition is enabled but not effective. It requires WooCommerce in order to work , Please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin. Reactivate LTL Freight Quotes - XPO Edition plugin to create LTL shipping class.";
    echo "<div class=\"$class\"> <p>$message</p></div>";
}

add_action('admin_init', 'xpo_check_wc_version');

/**
 * Check woocommerce version compatibility
 */
function xpo_check_wc_version()
{
    $woo_version = xpo_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version, $version, ">=")) {
        add_action('admin_notices', 'wc_version_incompatibility_xpo');
    }
}

/**
 * Check woocommerce version incompatibility
 */
function wc_version_incompatibility_xpo()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            _e('LTL Freight Quotes - XPO Edition plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'wwe-woo-version-failure');
            ?>
        </p>
    </div>
    <?php
}

/**
 * Return woocomerce version
 * @return int
 */
function xpo_wc_version_number()
{
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    if (isset($plugin_folder[$plugin_file]['Version'])) {
        return $plugin_folder[$plugin_file]['Version'];
    } else {
        return NULL;
    }
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    add_action('admin_enqueue_scripts', 'xpo_admin_script');

    /**
     * Load scripts for XPO
     */
    function xpo_admin_script()
    {
        // Cuttoff Time

        wp_register_style('xpo_wickedpicker_style', plugin_dir_url(__FILE__) . 'css/wickedpicker.min.css', false, '1.0.0');
        wp_register_script('xpo_wickedpicker_script', plugin_dir_url(__FILE__) . 'js/wickedpicker.js', false, '1.0.0');
        wp_enqueue_style('xpo_wickedpicker_style');

        wp_enqueue_script('xpo_wickedpicker_script');
        wp_register_style('xpo_style', plugin_dir_url(__FILE__) . '/css/xpo-style.css', false, '1.1.3');
        wp_enqueue_style('xpo_style');
    }

    add_action('admin_enqueue_scripts', 'en_xpo_script');

    /**
     * Load Front-end scripts for xpo
     */
    function en_xpo_script()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('en_xpo_script', plugin_dir_url(__FILE__) . 'js/en-xpo.js', array(), '1.1.2');
        wp_localize_script('en_xpo_script', 'en_xpo_admin_script', array(
            'plugins_url' => plugins_url(),
            'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
            'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
            // Cuttoff Time
            'xpo_freight_order_cutoff_time' => get_option("xpo_freight_order_cut_off_time"),
        ));
    }

    /**
     * Inlude Plugin Files
     */

    require_once 'fdo/en-fdo.php';

    require_once 'xpo-curl-class.php';
    require_once 'xpo-quotes-liftgate-as-option.php';
    require_once 'xpo-test-connection.php';
    require_once 'xpo-shipping-class.php';
    require_once 'db/xpo-db.php';
    require_once 'xpo-admin-filter.php';

    /*
     * link files of plans and warehouse
     */
    require_once('warehouse-dropship/xpo-wild-delivery.php');
    require_once('warehouse-dropship/get-distance-request.php');
    require_once('standard-package-addon/standard-package-addon.php');
    require_once 'update-plan.php';

    require_once 'xpo-group-package.php';
    require_once 'xpo-carrier-service.php';
    require_once('template/connection-settings.php');
    require_once('template/quote-settings.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once 'wc-update-change.php';
    require_once 'shipping-rules/shipping-rules-save.php';

    require_once 'template/products-nested-options.php';
    require_once 'template/csv-export.php';

    // $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

    require_once 'order/en-order-export.php';

    $en_hide_widget = apply_filters('en_hide_widget_for_this_carrier', false);
    if (!$en_hide_widget) {
        require_once 'order/en-order-widget.php';
    }

    // Origin terminal address
    add_action('admin_init', 'xpo_freight_update_warehouse');
    add_action('admin_init', 'create_xpo_shipping_rules_db');

    require_once('product/en-product-detail.php');

    register_activation_hook(__FILE__, 'create_ltl_freight_class');
    register_activation_hook(__FILE__, 'create_xpo_wh_db');
    register_activation_hook(__FILE__, 'create_xpo_option');
    register_activation_hook(__FILE__, 'old_store_xpo_ltl_dropship_status');
    register_activation_hook(__FILE__, 'create_xpo_shipping_rules_db');
    register_activation_hook(__FILE__, 'en_xpo_freight_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_xpo_freight_deactivate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_xpo_deactivate_plugin');

    /**
     * xpo plugin update now
     */
    function en_xpo_update_now()
    {
        $index = 'ltl-freight-quotes-xpo-edition/ltl-freight-quotes-xpo-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = $plugin_info[$index]['Version'];
        $update_now = get_option('en_xpo_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('en_xpo_freight_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            create_ltl_freight_class();
            create_xpo_wh_db();
            create_xpo_option();
            old_store_xpo_ltl_dropship_status();
            en_xpo_freight_activate_hit_to_update_plan();

            update_option('en_xpo_update_now', $plugin_version);
        }
    }

    add_action('init', 'en_xpo_update_now');
    add_action( 'upgrader_process_complete', 'en_xpo_update_now', 10, 2);

    /**
     * XPO Action And Filters
     */
    add_action('woocommerce_shipping_init', 'xpo_logistics_init');
    add_filter('woocommerce_shipping_methods', 'add_xpo_logistics');
    add_filter('woocommerce_get_settings_pages', 'xpo_shipping_sections');
    add_filter('woocommerce_package_rates', 'xpo_hide_shipping', 99);
    add_filter('woocommerce_cart_no_shipping_available_html', 'xpo_default_error_message');
    add_action('init', 'xpo_no_method_available');
    add_action('init', 'xpo_default_error_message_selection');

    /**
     * Update Default custom error message selection
     */
    if (!function_exists("xpo_default_error_message_selection")) {

        function xpo_default_error_message_selection()
        {
            $custom_error_selection = get_option('wc_pervent_proceed_checkout_eniture');
            if (empty($custom_error_selection)) {
                update_option('wc_pervent_proceed_checkout_eniture', 'prevent', true);
                update_option('prevent_proceed_checkout_eniture', 'There are no shipping methods available for the address provided. Please check the address.', true);
            }
        }

    }

    /**
     * @param $message
     * @return string
     */
    if (!function_exists("xpo_default_error_message")) {

        function xpo_default_error_message($message)
        {

            if (get_option('wc_pervent_proceed_checkout_eniture') == 'prevent') {
                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                return __(get_option('prevent_proceed_checkout_eniture'));
            } else if (get_option('wc_pervent_proceed_checkout_eniture') == 'allow') {
                add_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                return __(get_option('allow_proceed_checkout_eniture'));
            }
        }

    }

    add_filter('plugin_action_links', 'xpo_logistics_add_action_plugin', 10, 5);

    /**
     * XPO action links
     * @staticvar $plugin
     * @param $actions
     * @param $plugin_file
     * @return arrray/string
     */
    function xpo_logistics_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=xpo_quotes">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

}

define("en_woo_plugin_xpo_quotes", "xpo_quotes");

add_action('wp_enqueue_scripts', 'en_ltl_xpo_frontend_checkout_script');

/**
 * Load Frontend scripts for XPO
 */
function en_ltl_xpo_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_ltl_xpo_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-xpo-checkout.js', array(), '1.0.0');
    wp_localize_script('en_ltl_xpo_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
}

/**
 * Get Domain Name
 */
if (!function_exists('xpo_freight_get_domain')) {

    function xpo_freight_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return getHost($url);
    }

}
if (!function_exists('getHost')) {

    function getHost($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}
/**
 * Plans Common Hooks
 */
if (!function_exists('xpo_quotes_quotes_plans_suscription_and_features')) {

    function xpo_quotes_quotes_plans_suscription_and_features($feature)
    {
        $package = get_option('xpo_freight_package');

        $features = array
        (
            'instore_pickup_local_devlivery' => array('3'),
            'hold_at_terminal' => array('3'),
            'nested_material' => array('3'),
            // Cuttoff Time
            'xpo_cutt_off_time' => array('2', '3'),
            'hazardous_material' => array('2', '3')
        );

        if (get_option('xpo_quotes_store_type') == "1") {
            $features['multi_warehouse'] = array('2', '3');
            $features['multi_dropship'] = array('', '0', '1', '2', '3');
            $features['hazardous_material'] = array('2', '3');
        } else {
            $dropship_status = get_option('en_old_user_dropship_status');
            $warehouse_status = get_option('en_old_user_warehouse_status');

            isset($dropship_status) && ($dropship_status == "0") ? $features['multi_dropship'] = array('', '0', '1', '2', '3') : '';
            isset($warehouse_status) && ($warehouse_status == "0") ? $features['multi_warehouse'] = array('2', '3') : '';
        }

        return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
    }

    add_filter('xpo_quotes_quotes_plans_suscription_and_features', 'xpo_quotes_quotes_plans_suscription_and_features', 1);
}

if (!function_exists('xpo_quotes_plans_notification_link')) {

    function xpo_quotes_plans_notification_link($plans)
    {
        $plan = current($plans);
        $plan_to_upgrade = "";
        switch ($plan) {
            case 2:
                $plan_to_upgrade = "<a target='_blank' class='plan_color' href='https://eniture.com/woocommerce-xpo-ltl-freight/'>Standard Plan required.</a>";
                break;
            case 3:
                $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-xpo-ltl-freight/'>Advanced Plan required.</a>";
                break;
        }

        return $plan_to_upgrade;
    }

    add_filter('xpo_quotes_plans_notification_link', 'xpo_quotes_plans_notification_link', 1);
}

/**
 *
 * old customer check dropship / warehouse status on plugin update
 */
if (!function_exists('old_store_xpo_ltl_dropship_status')) {

    function old_store_xpo_ltl_dropship_status()
    {
        global $wpdb;

//      Check total no. of dropships on plugin updation
        $table_name = $wpdb->prefix . 'warehouse';
        $count_query = "select count(*) from $table_name where location = 'dropship' ";
        $num = $wpdb->get_var($count_query);

        if (get_option('en_old_user_dropship_status') == "0" && get_option('xpo_quotes_store_type') == "0") {
            $dropship_status = ($num > 1) ? 1 : 0;
            update_option('en_old_user_dropship_status', "$dropship_status");
        } elseif (get_option('en_old_user_dropship_status') == "" && get_option('xpo_quotes_store_type') == "0") {
            $dropship_status = ($num == 1) ? 0 : 1;
            update_option('en_old_user_dropship_status', "$dropship_status");
        }

//      Check total no. of warehouses on plugin updation
        $table_name = $wpdb->prefix . 'warehouse';
        $warehouse_count_query = "select count(*) from $table_name where location = 'warehouse' ";
        $warehouse_num = $wpdb->get_var($warehouse_count_query);

        if (get_option('en_old_user_warehouse_status') == "0" && get_option('xpo_quotes_store_type') == "0") {
            $warehouse_status = ($warehouse_num > 1) ? 1 : 0;
            update_option('en_old_user_warehouse_status', "$warehouse_status");
        } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('xpo_quotes_store_type') == "0") {
            $warehouse_status = ($warehouse_num == 1) ? 0 : 1;
            update_option('en_old_user_warehouse_status', "$warehouse_status");
        }
    }

}

function xpo_en_append_account_number_multiple_plugins($template)
{
    $template .= ' <div class="en_wd_add_warehouse_custom_input en_wd_add_warehouse_input en_wd_xpo_account_label">
                        <label for="en_wd__dropship_xpo_account">XPO Account Number</label>
                        <input type="text" data-connection_input="xpo_test_connection_zipcode" data-post_input="xpo_account" title="XPO Account Nmuber" name="en_wd_xpo_account" value="" placeholder="XPO Account Number" class="en_wd_xpo_account" data-optional="1">
                        <span class="en_wd_err"></span>
                    </div>';
    return $template;
}

add_filter('en_append_account_number_multiple_plugins', 'xpo_en_append_account_number_multiple_plugins', 1, 1);


/*
 * Add account number hidden field on add/edit warehouse/dropship
 */

function xpo_en_append_account_number_hidden_multiple_plugins($template)
{
    $template .= '<div class="en_wd_account_number">
        <input type="hidden" data-account_num_on_warehouse="en_wd_xpo_account_label" value="' . get_option('wc_settings_xpo_zipcode') . '" id="xpo_test_connection_zipcode">
    </div>';
    return $template;
}

add_filter('en_append_account_number_hidden_multiple_plugins', 'xpo_en_append_account_number_hidden_multiple_plugins', 1, 1);
// fdo va
add_action('wp_ajax_nopriv_xpo_fd', 'xpo_fd_api');
add_action('wp_ajax_xpo_fd', 'xpo_fd_api');
/**
 * UPS AJAX Request
 */
function xpo_fd_api()
{
    $store_name = xpo_freight_get_domain();
    $company_id = $_POST['company_id'];
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=xpo_quotes&section=section-4',
    ];
    if (is_array($data) && count($data) > 0) {
        if($_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo $response;
    exit();
}
add_action('rest_api_init', 'en_rest_api_init_status_xpo');
function en_rest_api_init_status_xpo()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'en_xpo_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array $request
 * @return array|void
 */
function en_xpo_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

add_filter('en_suppress_parcel_rates_hook', 'supress_parcel_rates');
if (!function_exists('supress_parcel_rates')) {
    function supress_parcel_rates() {
        $exceedWeight = get_option('en_plugins_return_LTL_quotes') == 'yes';
        $supress_parcel_rates = get_option('en_suppress_parcel_rates') == 'suppress_parcel_rates';
        return ($exceedWeight && $supress_parcel_rates);
    }
}
