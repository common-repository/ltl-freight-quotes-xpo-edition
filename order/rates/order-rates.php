<?php
/**
 * Order page rates when click on "Save" OR "Recalculate".
 */
if (!class_exists('EnXpoOrderRates')) {

    class EnXpoOrderRates
    {
        public $shipping_address = [];

        public function __construct()
        {
            add_action('woocommerce_order_before_calculate_totals', [$this, 'en_order_before_calculate_totals'], 10, 2);
            add_filter('en_order_accessories', [$this, 'en_order_accessories']);
        }

        // Receiver address along order page.
        public function en_order_accessories($shipping_address)
        {
            return array_merge($this->shipping_address, $shipping_address);
        }

        // Calculate shipping
        public function en_order_before_calculate_totals($and_taxes, $order)
        {
            global $woocommerce;
            if (isset($woocommerce->cart)) {
                $order_recreated = false;
                $errors = [];
                $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;

                // Shipping address
                if (method_exists($order, 'get_address')) {
                    $shipping_address = $order->get_address('shipping');
                    (strlen($shipping_address['postcode']) > 0) ? $shipping_address['zip'] = $shipping_address['postcode'] : $errors[] = "Please enter billing or shipping address.";
                    $this->shipping_address = $shipping_address;
                    $shipping = $order->get_items('shipping');
                    foreach ($shipping as $item_id => $per_shipment) {
                        $method_id = $per_shipment->get_method_id();
                        switch ($method_id) {
                            case 'xpo':
                                $woocommerce->cart->empty_cart();
                                $items = $order->get_items();
                                foreach ($items as $item) {
                                    $product_id = (isset($item['variation_id']) && !empty($item['variation_id'])) ?
                                        $item['variation_id'] : $item['product_id'];
                                    $woocommerce->cart->add_to_cart($product_id, $item['qty']);
                                    $cart = array('contents' => $woocommerce->cart->get_cart($product_id));
                                }

                                ((isset($cart['contents'])) && empty($cart['contents']) || (empty($items))) ? $errors[] = "Empty shipping cart content." : '';

                                if (empty($errors)) {
                                    $shipping_class = new XPO_Logistics_Shipping();
                                    $response = $shipping_class->calculate_shipping($cart, true);
                                    if (isset($response) && is_array($response) && !empty($response)) {
                                        $response = current($response);
                                    }

                                    if (isset($response['cost']) && $response['cost'] > 0) {
                                        // Delete old meta data
                                        foreach ($per_shipment->get_meta_data() as $meta_key => $meta_value) {
                                            unset($meta_value);
                                        }

                                        $per_shipment->update_meta_data('en_flat_rate_details', json_encode([]));
                                        $per_shipment->update_meta_data('en_account_details', json_encode([]));
                                        $per_shipment->update_meta_data('en_fdo_meta_data', json_encode([]));
                                        $per_shipment->set_method_title($response['label']);
                                        $per_shipment->set_method_id($method_id);
                                        $per_shipment->set_total($response['cost']);
                                        foreach ($response['meta_data'] as $meta_key => $meta_data) {
                                            $per_shipment->update_meta_data($meta_key, $meta_data);
                                        }
                                        $order_recreated = true;
                                        $per_shipment->save();
                                    } else {
                                        $errors[] = "No Quotes return.";
                                    }

                                    break;
                                }
                        }
                    }
                }

                if ($order_recreated) {
                    $request_data = [
                        'domain' => xpo_freight_get_domain(),
                        'order_id' => $order_id
                    ];
                    $xpo_freight_curl_obj = new XPO_Curl_Request();
                    $xpo_freight_curl_obj->xpo_get_curl_response(XPO_FDO_HITTING_URL, $request_data);
                }
            }
        }
    }

    new EnXpoOrderRates();
}